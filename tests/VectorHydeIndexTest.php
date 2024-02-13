<?php

namespace Textualization\SemanticSearch\Tests;

use Textualization\SemanticSearch\Ingester;
use Textualization\SemanticSearch\Index;
use Textualization\SemanticSearch\VectorHydeIndex;
use Textualization\SemanticSearch\Document;
use Textualization\SemanticSearch\Tokenizer;

use PHPUnit\Framework\TestCase;

class VectorHydeIndexTest extends IndexTestBase {

    public function test_mrr() : void
    {
        $tmpfname = tempnam(sys_get_temp_dir(), "phptest_vector");
        $handle = fopen(__DIR__ . "/sornd1000.jsonl", "r");
        $cache =  []; // prompt -> completion
        while (($line = fgets($handle)) !== false) {
            $row = json_decode($line, true);
            $cache[$row['title']] = $row['completion'];
        }
        fclose($handle);
        
        $index = new VectorHydeIndex([ "location" => $tmpfname, "completion" => [
            "class" => "\\Textualization\\SemanticSearch\\Tests\\CachedCompletionService",
            "cache" => $cache
        ]]);
        Ingester::ingest($index, [ "add_title" => false ], __DIR__ . "/sornd1000.jsonl" );

        $db = new \SQLite3($tmpfname);
        $result = $db->query("select url, title from vector_documents");
        $this->assertNotFalse($result, $db->lastErrorMsg());
        $title_url = [];
        while($row = $result->fetchArray()){
            $title_url[$row['title']] = $row['url'];
        }
        $db->close();
        # xlm 0.6894799684118035
        $this->_test_mrr($index, $title_url, 0.84, 0.85); # 0.8480127736086553

        $index->close();
        unlink($tmpfname);
    }

}

