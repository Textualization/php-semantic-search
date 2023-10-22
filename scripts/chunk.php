<?php

require "vendor/autoload.php";

use Textualization\SemanticSearch\Ingester;
use Textualization\SemanticSearch\Index;
use Textualization\SemanticSearch\Document;
use Textualization\SemanticSearch\Tokenizer;


global $argc,$argv;

$tokenizer_class = "\\Textualization\\SemanticSearch\\RophertaTokenizer";

if($argv[1] === "null") {
    $tokenizer_class = null;
}else if($argv[1] === "ropherta") {
     $tokenizer_class = "\\Textualization\\SemanticSearch\\RophertaTokenizer";
}else{
     $tokenizer_class = $argv[1];
}

$tokenizer = $tokenizer_class ? new $tokenizer_class() : null;
$ingester_desc = $argv[2];
if($ingester_desc === 'null') {
    $ingester_desc = null;
}

$size = intval($argv[3]);
$input = $argv[4];
$output = $argv[5];

$handle = fopen($output, "w");

$index = new class($size, $tokenizer, $handle) implements Index {
    private $handle;
    private int $size;
    private ?Tokenizer $tokenizer;
    public int $count;
    
    public function __construct(int $size, ?Tokenizer $tokenizer, $handle)
    {
        $this->size = $size;
        $this->tokenizer = $tokenizer;
        $this->handle = $handle;
        $this->count = 0;
    }
    public function add(Document $doc) : void
    {
        fputs($this->handle, json_encode($doc->__to_json()) . "\n");
        $this->count++;
    }
    public function document_size() : int {
        return $this->size;
    }
    public function tokenizer() : ?Tokenizer {
        return $this->tokenizer;
    }
    public function search(string $query) : array { return []; }
    public function fetch_document(string $url, int $chunk_num) : ?Document { return null; }
    public function max_docs() : int { return 10; }
    public function set_max_docs(int $max_docs) : void {}
};

Ingester::ingest($index, $ingester_desc, $input);
fclose($handle);
echo $index->count." chunks generated\n";
