<?php

require "vendor/autoload.php";

global $argc,$argv;

$model = new \Textualization\SemanticSearch\RophertaEmbedder();

print_r($model->encode($argv[1]));
