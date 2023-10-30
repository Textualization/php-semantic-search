<?php

namespace Textualization\SemanticSearch\Tests;

use Textualization\SemanticSearch\Ingester;
use Textualization\SemanticSearch\Index;
use Textualization\SemanticSearch\VectorIndex;
use Textualization\SemanticSearch\Document;
use Textualization\SemanticSearch\Tokenizer;

use PHPUnit\Framework\TestCase;

class VectorIndexRedPajamaTest extends IndexTestBase {

    protected function setUp() : void
    {
        if (getenv("REDPAJAMA_PORT") === false)
            $this->markTestSkipped('No redpajama port specified.');
    }

    public function test_mrr() : void
    {
        $tmpfname = tempnam(sys_get_temp_dir(), "phptest_vector");
        $index = new VectorIndex([
            "location" => $tmpfname,
            "embedder" => [
                "class" => "\\Textualization\\SemanticSearch\\RedPajamaCppEmbedder",
                "host" => "localhost",
                "port" => intval(getenv("REDPAJAMA_PORT"))
            ]
        ]);
        Ingester::ingest($index, [ "add_title" => false ], __DIR__ . "/sornd1000.jsonl" );

        $db = new \SQLite3($tmpfname);
        $result = $db->query("select url, title from vector_documents");
        $this->assertNotFalse($result, $db->lastErrorMsg());
        $title_url = [];
        while($row = $result->fetchArray()){
            $title_url[$row['title']] = $row['url'];
        }
        $db->close();
        # 0.013626664201567478
        $this->_test_mrr($index, $title_url, 0.01, 0.02);

        $index->close();
        unlink($tmpfname);
    }

}

