<?php

require "vendor/autoload.php";

global $argc,$argv;

$index_class = "\\Textualization\\SemanticSearch\\KeywordIndex";
$query = $argv[1];

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
        }elseif($index_class == "reranked") {
            $index_class = "\\Textualization\\SemanticSearch\\RerankedIndex";
            $desc["main"] = [ "class" => "\\Textualization\\SemanticSearch\\KeywordIndex" ];
            $desc["reranker"] = [ "class" => "\\Textualization\\SemanticSearch\\VectorIndex" ];
            $desc["use_half_key"] = true;
        }
        $desc["class"] = $index_class;
    }
        
    $query = $argv[2];
}

$index = \Textualization\SemanticSearch\IndexFactory::make($desc);
$results = $index->search($query);

$idx = 0;
foreach($results as $result) {
    echo "$idx. $result\n";
    $idx++;
}
