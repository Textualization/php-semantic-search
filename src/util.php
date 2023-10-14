<?php

namespace Textualization\SemanticSearch;

function get_json(string|array|null $param) : array
{
    if(is_null($param))
        return [];
    if(is_array($param))
       return $param;
    if(str_ends_with($param, ".json"))
        $param = file_get_contents($param);
    return json_decode($param, true);
}
