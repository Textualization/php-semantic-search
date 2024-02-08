<?php

namespace Textualization\SemanticSearch;

include_once 'util.php';

class VectorIndex implements Index {

    protected \SQLite3 $db;

    protected string $vector_table;

    protected string $vector_size;

    protected int $max_docs;

    protected Embedder $embedder;

    protected bool $verbose;
    
    public function __construct(array|string|null $desc = null)
    {
        $desc = get_json($desc);        
        $this->db = new \SQLite3($desc["location"] ?? "vector.db");
        $this->db->loadExtension("vector0.so");
        $this->db->loadExtension("vss0.so");

        // create the embedder
        $this->embedder = EmbedderFactory::make($desc["embedder"] ?? null);
        
        $this->vector_table = $desc["table"] ?? "vector_documents";
        $this->vector_size = $this->embedder->size();
        $this->max_docs = $desc["max_docs"] ?? 50;
        $this->verbose = $desc["verbose"] ?? false;

        // check if DB has the index has the table, otherwise create it
        $stmt = $this->db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=:name");
        if($stmt === false) throw new \Exception($this->db->lastErrorMsg());
        $stmt->bindValue(':name', $this->vector_table);
        $result = $stmt->execute();
        $table = $this->vector_table;
        if(! \is_array($result->fetchArray())) {
            $stmt->close();

            $size = $this->vector_size;
            if(! $this->db->exec(<<<SQL
pragma journal_mode=wal;
pragma synchronous=normal;
create table $table(
  `url` TEXT, 
  `chunk_num` INTEGER, 
  `text` TEXT NOT NULL, 
  `title` TEXT, 
  `offset_start` INTEGER,
  `offset_end` INTEGER,
  `section` TEXT, 
  `license` TEXT
);
create virtual table vss_$table using vss0( 
  embeddings( $size )
);
SQL
            ))
                throw new \Exception($this->db->lastErrorMsg());            
        }
    }


    public function search(string $query) : array //<SearchResult>
    {
        $query_vector = $this->embedder->encode($query);
        
        $table = $this->vector_table;
        /*
        $stmt = $this->db->prepare(<<<SQL
select distance, $table.`title`, $table.`url`, $table.`chunk_num`, $table.`offset_start`, $table.`offset_end`
from vss_$table
inner join $table on
vss_$table.rowid = $table.rowid
where vss_search(embeddings, json(:json))
limit :limit
SQL
        );
        */
        $stmt = $this->db->prepare(<<<SQL
select r.distance, $table.`title`, $table.`url`, $table.`chunk_num`, $table.`offset_start`, $table.`offset_end`
from (select distance, rowid
from vss_$table
where vss_search(embeddings, json(:json))
limit :limit) r
inner join $table on
r.rowid = $table.rowid
SQL
        );

        if($stmt === false) throw new \Exception($this->db->lastErrorMsg());
        $stmt->bindValue(':limit', $this->max_docs, SQLITE3_INTEGER);
        $stmt->bindValue(':json', json_encode($query_vector));
        $distances = $stmt->execute();
        if($distances === false) throw new \Exception($this->db->lastErrorMsg());
        $result=[];
        while ($row = $distances->fetchArray()) {
            $result[] = new SearchResult(-$row[0], $row[2], $row[3], $row[4], $row[5], $row[1]);
        }
        $stmt->close();

        //usort($result, function($a,$b) { return $a->score < $b->score; });
        return $result;
    }

    public function add(Document $doc) : void
    {
        $text_vector = $this->embedder->encode($doc->text);

        #echo "\n\n---------------\n";
        #print_r($document->text);

        $table = $this->vector_table;
        $stmt = $this->db->prepare(<<<SQL
insert into $table (`url`,`chunk_num`,`text`, `title`, `offset_start`, `offset_end`, `section`, `license`) 
values (:url, :chunk_num, :text, :title, :offset_start, :offset_end, :section, :license);
SQL
        );
        if($stmt === false) throw new \Exception($this->db->lastErrorMsg());
        $stmt->bindValue(':url', $doc->url);
        $stmt->bindValue(':chunk_num', $doc->chunk_num, SQLITE3_INTEGER);
        $stmt->bindValue(':text', $doc->text);
        $stmt->bindValue(':title', $doc->title);
        $stmt->bindValue(':offset_start', $doc->offset_start, SQLITE3_INTEGER);
        $stmt->bindValue(':offset_end', $doc->offset_end, SQLITE3_INTEGER);
        $stmt->bindValue(':section', $doc->section);
        $stmt->bindValue(':license', $doc->license);
        $stmt->execute();
        $rowid = $this->db->lastInsertRowID();
        $stmt->close();

        $vss = "vss_$table";
        $stmt = $this->db->prepare(<<<SQL
insert into $vss (`rowid`, `embeddings`) 
values (:document_id, json(:embeddings));
SQL
        );
        if($stmt === false) throw new \Exception($this->db->lastErrorMsg());
        $stmt->bindValue(':document_id', $rowid, SQLITE3_INTEGER);
        $stmt->bindValue(':embeddings', json_encode($text_vector));
        $stmt->execute();
        $stmt->close();
    }

    public function tokenizer() : ?Tokenizer
    {
        return $this->embedder->tokenizer();
    }

    public function max_docs() : int
    {
        return $max_docs;
    }
    
    public function set_max_docs(int $max_docs) : void
    {
        $this->max_docs = $max_docs;
    }

    public function fetch_document(string $url, int $chunk_num) : ?Document
    {
        $doc = $this->vector_table;
        $stmt = $this->db->prepare(<<<SQL
select `title`, `text`, `offset_start`, `offset_end`, `section`, `license`, `url`, `chunk_num`
from $doc
where `url` = :url and `chunk_num` = :chunk
SQL
);
        if($stmt === false) throw new \Exception($this->db->lastErrorMsg());
        $stmt->bindValue(':url', $url);
        $stmt->bindValue(':chunk', $chunk_num, SQLITE3_INTEGER);        
        $docs = $stmt->execute();
        $doc = null;
        while($row = $docs->fetchArray()) {
            $doc = new Document($row);
        }
        $stmt->close();
        
        return $doc;
    }

    public function document_size() : int
    {
        return $this->embedder->input_size();
    }

    public function close() : void
    {
        $this->db->close();
    }
}
