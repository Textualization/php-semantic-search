<?php

namespace Textualization\SemanticSearch\Tests;

use Textualization\SemanticSearch\Ingester;
use Textualization\SemanticSearch\Index;
use Textualization\SemanticSearch\KeywordIndex;
use Textualization\SemanticSearch\Document;
use Textualization\SemanticSearch\Tokenizer;

use PHPUnit\Framework\TestCase;

class KeywordIndexTest extends IndexTestBase {

    public function test_mrr() : void
    {
        $tmpfname = tempnam(sys_get_temp_dir(), "phptest_keyword");
        $index = new KeywordIndex([ "location" => $tmpfname ]);
        Ingester::ingest($index, [ "add_title" => false ], __DIR__ . "/sornd1000.jsonl" );

        $db = new \SQLite3($tmpfname);
        $result = $db->query("select url, title from keywords");
        $this->assertNotFalse($result, $db->lastErrorMsg());
        $title_url = [];
        while($row = $result->fetchArray()){
            $title_url[$row['title']] = $row['url'];
        }
        $db->close();
        $this->_test_mrr($index, $title_url, 0.83, 0.85); # 0.8366089909836908

        $index->close();
        unlink($tmpfname);
    }

}

