<?php

namespace Textualization\SemanticSearch\Tests;

use Textualization\SemanticSearch\Ingester;
use Textualization\SemanticSearch\Index;
use Textualization\SemanticSearch\Document;
use Textualization\SemanticSearch\Tokenizer;

use PHPUnit\Framework\TestCase;

class IngesterTest extends TestCase {

    public function test_splitter() : void
    {
        $tmpfname = tempnam(sys_get_temp_dir(), "phptest");
        file_put_contents($tmpfname, <<<JSONL
{ "url":"https://textualization.com/ex1.txt","title":"example title","text":"1st chunk, dbl line\\n\\n2n chunk\\n\\njoin here\\n\\nThis one\\nneeds to\\nbe subchunked.\\n\\nHere is\\nA very long subchunk that will need to be split on spaces\\n\\nThisonehasnospacesocharacterswillhavetodo\\n\\nBack to\\nnormal\\n\\n", "section":"test", "license":"MIT" }
JSONL
        );
        $index = new class implements Index {
            public array $docs; //<Document>

            public function __construct()
            {
                $this->docs = [];
            }
            public function add(Document $doc) : void
            {
                $this->docs[] = $doc;
            }
            public function document_size() : int {
                return 20;
            }
            public function search(string $query) : array { return []; }
            public function fetch_document(string $url, int $chunk_num) : ?Document { return null; }
            public function tokenizer() : ?Tokenizer { return null; }
            public function max_docs() : int { return 10; }
            public function set_max_docs(int $max_docs) : void {}
        };
        Ingester::ingest($index, null, $tmpfname);
        //print_r($index->docs);
        $this->assertEquals(14, count($index->docs));
        $this->assertEquals("example title.", $index->docs[0]->text);
        $this->assertEquals("1st chunk, dbl line", $index->docs[1]->text);
        $this->assertEquals("characterswillhavet", $index->docs[11]->text);
        unlink($tmpfname);
    }

}

