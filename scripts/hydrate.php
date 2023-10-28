<?php

require "vendor/autoload.php";

global $argc,$argv;

use Textualization\SemanticSearch\CompletionServiceFactory;

$completion_class = "\\Textualization\\SemanticSearch\\OpenAIService";

$desc = ["class"=>$completion_class];

if(str_starts_with($argv[1], "{")) {
    $desc = json_decode($argv[1], true);
}else{
    $key = $argv[1];
    $desc["open_ai_key"] = $key;
}

$completion = CompletionServiceFactory::make($desc);
$size = intval($argv[2]);
$jsonl = $argv[3];
$out = $argv[4];

$count = 0;
$handle = fopen($jsonl, "r");
$out_handle = fopen($out, "w");

while (($line = fgets($handle)) !== false) {
    $row = json_decode($line, true);
    if ($row === null) {
        echo "Cannot parse JSON at line $count: '$line'\n";
    }else{
        $row['completion'] = $completion->complete($row['title'], $size);
        sleep(1); # one second, should be enough to be out of Open AI time-outs
        fputs($out_handle, (json_encode($row)."\n"));
    }
    $count++;
    echo "$count ";
}
fclose($handle);
fclose($out_handle);

