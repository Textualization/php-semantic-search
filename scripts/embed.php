<?php

require "vendor/autoload.php";

global $argc,$argv;

$model = new \Textualization\Ropherta\RophertaModel();

print_r($model->embeddings($argv[1]));
