<?php

namespace Textualization\SemanticSearch\Tests;

use Textualization\SemanticSearch\Ingester;
use Textualization\SemanticSearch\Index;
use Textualization\SemanticSearch\VectorIndex;
use Textualization\SemanticSearch\KeywordIndex;
use Textualization\SemanticSearch\RerankedIndex;
use Textualization\SemanticSearch\Document;
use Textualization\SemanticSearch\Tokenizer;

use PHPUnit\Framework\TestCase;

class RerankedIndexTest extends IndexTestBase {

    public function test_mrr() : void
    {
        $tmpfname = tempnam(sys_get_temp_dir(), "phptest_reranked");
        $vector_index  = new VectorIndex( [ "location" => $tmpfname ."_vector"  ]);
        $keyword_index = new KeywordIndex([ "location" => $tmpfname ."_keyword" ]);
        
        Ingester::ingest($keyword_index, [ "add_title" => false ], __DIR__ . "/sornd1000.jsonl" );
        Ingester::ingest($vector_index,  [ "add_title" => false ], __DIR__ . "/sornd1000.jsonl" );
        
        $db = new \SQLite3($tmpfname ."_keyword");
        $result = $db->query("select url, title from keywords");
        $this->assertNotFalse($result, $db->lastErrorMsg());
        $title_url = [];
        while($row = $result->fetchArray()){
            $title_url[$row['title']] = $row['url'];
        }
        $db->close();
        $index = new RerankedIndex( [
            "main" => $keyword_index,
            "reranker" => $vector_index
        ]);
        # 0.08632250175091587
        $this->_test_mrr($index, $title_url, 0.08, 0.09);

        $index->close();
        unlink($tmpfname . "_vector");
        unlink($tmpfname . "_keyword");
    }

}

