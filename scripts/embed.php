<?php

require "vendor/autoload.php";

global $argc,$argv;

$params = [];
$text = $argv[1];
if($argc == 3){
    $params["model"] = $argv[1];
    $text = $argv[2];
}


$model = new \Textualization\SemanticSearch\SentenceTransphormerEmbedder( $params );

echo json_encode($model->encode($text))."\n";
# $embedding = $model->encode($text); echo array_sum($embedding)."\n";
