<?php

namespace Textualization\SemanticSearch;

include_once 'util.php';

class IndexFactory {
    
    public static function make(array|string|Index|null $desc = null) : Index
    {
        if($desc instanceof Index)
            return $desc;
        
        $desc = get_json($desc);
        if(is_null($desc)){
            $desc=[];
        }elseif(!is_array($desc)) {
            $desc = json_decode($desc);
        }
        $index_class = $desc['class'] ?? "\\Textualization\\SemanticSearch\\KeywordIndex";

        return new $index_class($desc);
    }

}
