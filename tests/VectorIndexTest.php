<?php

namespace Textualization\SemanticSearch\Tests;

use Textualization\SemanticSearch\Ingester;
use Textualization\SemanticSearch\Index;
use Textualization\SemanticSearch\VectorIndex;
use Textualization\SemanticSearch\Document;
use Textualization\SemanticSearch\Tokenizer;

use PHPUnit\Framework\TestCase;

class VectorIndexTest extends IndexTestBase {

    public function test_mrr() : void
    {
        $tmpfname = tempnam(sys_get_temp_dir(), "phptest_vector");
        $index = new VectorIndex([ "location" => $tmpfname ]);
        Ingester::ingest($index, [ "add_title" => false ], __DIR__ . "/sornd1000.jsonl" );

        $db = new \SQLite3($tmpfname);
        $result = $db->query("select url, title from vector_documents");
        $this->assertNotFalse($result, $db->lastErrorMsg());
        $title_url = [];
        while($row = $result->fetchArray()){
            $title_url[$row['title']] = $row['url'];
        }
        $db->close();
        # 0.039330267174838146  (output_2 0)
        # 0.03822366623088808   (output_1 0 0)
        # 0.04232106750024146   (mean pooling)
        # 0.00733371290099728   (mean pooling with padding)
        # 0.8699910374529284    (xlm - multilingual-e5-small)
        # 0.9245769311666672    (distilroberta)
        $this->_test_mrr($index, $title_url, 0.92, 0.93);

        $index->close();
        unlink($tmpfname);
    }

}

