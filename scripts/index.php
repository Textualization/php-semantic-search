<?php

require "vendor/autoload.php";

global $argc,$argv;

$index_class = "\\Textualization\\SemanticSearch\\KeywordIndex";
$jsonl = $argv[1];
$desc = ["class"=>$index_class];
if($argc == 3) {
    if(str_starts_with($argv[1], "{")) {
        $desc = json_decode($argv[1], true);
    }else{
        $index_class = $argv[1];
        if($index_class == "keyword") {
            $index_class = "\\Textualization\\SemanticSearch\\KeywordIndex";
        }elseif($index_class == "vector") {
            $index_class = "\\Textualization\\SemanticSearch\\VectorIndex";
        }
        $desc = ["class"=>$index_class];
    }
    $jsonl = $argv[2];
}
        
\Textualization\SemanticSearch\Ingester::ingest($desc, [], $jsonl);
