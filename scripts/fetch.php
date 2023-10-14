<?php

require "vendor/autoload.php";

global $argc,$argv;

$index_class = "\\Textualization\\SemanticSearch\\KeywordIndex";
$url = $argv[1];
$chunk = intval($argv[2]);

$desc = ["class"=>$index_class];

if($argc == 4) {
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
        
    $url = $argv[2];
    $chunk = intval($argv[3]);
}

$index = \Textualization\SemanticSearch\IndexFactory::make($desc);
$doc = $index->fetch_document($url, $chunk);

echo "URL: $url\n";
echo "Chunk: $chunk\n";
if($doc) {
    echo "Title: ".$doc->title."\n";
    echo "Offset-start: ".$doc->offset_start."\n";
    echo "Offset-end: ".$doc->offset_end."\n";
    echo "Section: ".$doc->section."\n";
    echo "License: ".$doc->license."\n";
    echo "Text:\n ".$doc->text."\n\n";
}else{
    echo "\nNOT FOUND\n";
}
