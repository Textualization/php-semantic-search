<?php

namespace Textualization\SemanticSearch;

include_once 'util.php';

class KeywordIndex implements Index {

    protected \SQLite3 $db;

    protected string $text_table;

    protected array $stop_words;  // <string>

    protected int $max_docs;
    
    public function __construct(array|string|null $desc = null)
    {
        $desc = get_json($desc);
        $this->db = new \SQLite3($desc["location"] ?? "keyword.db");

        $this->text_table = $desc["table"] ?? "keywords";
        $this->stop_words = $desc["stopwords"] ?? [];
        $this->max_docs = $desc["max_docs"] ?? 50;

        // check if DB has the index has the table, otherwise create it
        $stmt = $this->db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=:name");
        if($stmt === false) throw new \Exception($this->db->lastErrorMsg());
        $stmt->bindValue(':name', $this->text_table);
        $result = $stmt->execute();
        if(! is_array($result->fetchArray())) {
            // create table
            $stmt->close();
            
            $table = $this->text_table;
            $fts = "fts_" . $this->text_table;
            $tok = "tok_" . $this->text_table;
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
create virtual table $fts using fts4(
  `content` TEXT,
  `document_id` INTEGER DEFAULT -1, FOREIGN KEY(`document_id`) REFERENCES $table ("rowid"), 
  tokenize=porter
);
create virtual table $tok using fts3tokenize("porter");
SQL
            ))
            throw new \Exception($this->db->lastErrorMsg());
        }

        // tokenize stop words and transform it into a set
        $toksw = [];
        foreach($this->stop_words as $word) {
            $tokens = $this->tokenize($str);
            foreach($tokens as $token)
                $toksw[] = $token;
        }
        $this->stop_words = \array_flip($toksw);
    }

    protected function tokenize(string $str) : array
    {
        $tok = "tok_".$this->text_table;
        $stmt = $this->db->prepare("SELECT token FROM $tok WHERE input=:input");
        $stmt->bindValue(':input', $str);
        $result=[];
        $tokens = $stmt->execute();
        while($row = $tokens->fetchArray()){
            $result[] = $row['token'];
        }
        $stmt->close();
        return $result;
    }

    public function generate_query(string $query) : string
    {
        // remove stop words
        $tokens = $this->tokenize($query);
        $term_seen = [];
        $query = "";
        foreach($tokens as $token) {
            if(isset($this->stop_words[$token]))
                continue;
            if(isset($term_seen[$token]))
                continue;
            if($query) {
                $query = "$query OR $token"; // more complex queries are possible
            }else{
                $query = $token;
            }
            $term_seen[$token] = 1;
        }
        return $query;
    }

    public function search_with_query(string $search_query) : array // <SearchResult>
    {
        echo "Search query: $search_query\n";
        $doc = $this->text_table;
        $fts = "fts_$doc";
        $stmt = $this->db->prepare(<<<SQL
select MATCHINFO($fts, 'pclanx') info, $doc.`title`, $doc.`url`, $doc.`chunk_num`, $doc.`offset_start`, $doc.`offset_end`
from $fts
inner join $doc on
$fts.document_id = $doc.rowid
where $fts MATCH :query
SQL
);
        if($stmt === false) throw new \Exception($this->db->lastErrorMsg());
        $stmt->bindValue(':query', $search_query);
        $infos = $stmt->execute();
        $result=[];

        $k1 = 1.2;
        $b = 0.75;
        while ($row = $infos->fetchArray()) {
            $info = unpack('L*', $row[0]);

            // https://en.wikipedia.org/wiki/Okapi_BM25
            $p = 1;
            $n = $info[$p]; $p++; // number of query terms
            $C = $info[$p]; $p++; // number of columns
            $l = 0; for($c=0; $c<$C; $c++) {$l+=$info[$p];$p++;} // doc. size in terms
            $avgdl = 0; for($c=0; $c<$C; $c++) {$avgdl+=$info[$p];$p++;} // average document length
            $N = $info[$p]; $p++;// number of documents
            $score = 0.0;
            //echo "$n $C $l $avgdl $N\n";
            for($i=0; $i<$n; $i++){
                for($c=0; $c<$C; $c++) {
                    $tf = $info[$p]; // 6 + 3*(2 + $i*$c)]; // number of times term appears in document
                    $df = $info[$p + 2]; //6 + 3*(2 + $i*$c) + 2]; // number of documents containing term
                    $p += 3;
                    $idf = log(($N - $df + 0.5)/($df+0.5) + 1);
                    
                    //echo "$n $l $avgdl $N - $i $c - \t$tf\t$df\t$idf\n";
                    $score += $idf * $tf * ($k1+1) / ($tf + $k1 * (1-$b +$b*$l/$avgdl));
                }
            }
            $result[] = new SearchResult($score, $row[2], $row[3], $row[4], $row[5], $row[1]);
        }
        $stmt->close();
        usort($result, function($a,$b) { return $a->score < $b->score; });
        $result=array_slice($result, 0, $this->max_docs);
        
        return $result;
    }

    public function search(string $user_query) : array //<SearchResult>
    {
        return $this->search_with_query($this->generate_query($user_query));
    }

    public function add(Document $doc) : void
    {
        // insert document
        $table = $this->text_table;
        
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

        $fts = "fts_$table";
        $stmt = $this->db->prepare(<<<SQL
insert into $fts (`content`, `document_id`) 
values (:text, :document_id);
SQL
        );
        if($stmt === false) throw new \Exception($this->db->lastErrorMsg());
        $stmt->bindValue(':text', $doc->text);
        $stmt->bindValue(':document_id', $rowid, SQLITE3_INTEGER);
        $stmt->execute();
        $stmt->close();
    }

    public function tokenizer() : ?Tokenizer
    {
        return null;
    }

    public function max_docs() : int
    {
        return $this->max_docs;
    }
    
    public function set_max_docs(int $max_docs) : void
    {
        $this->max_docs = $max_docs;
    }

    public function fetch_document(string $url, int $chunk_num) : ?Document
    {
        $doc = $this->text_table;
        $stmt = $this->db->prepare(<<<SQL
select `title`, `text`, `offset_start`, `offset_end`, `section`, `license`, `url`, `chunk_num`
from $doc
where `url`=:url and `chunk_num`=:chunk
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
        return -1;
    }
}
