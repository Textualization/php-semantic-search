<?php

namespace Textualization\SemanticSearch;

include_once 'util.php';

class EmbedderFactory {
    
    public static function make(array|string|null $desc = null) : Embedder
    {
        $desc = get_json($desc);
        if(is_null($desc)){
            $desc=[];
        }elseif(!is_array($desc)) {
            $desc = json_decode($desc);
        }
        #$embedder_class = $desc['class'] ?? "\\Textualization\\SemanticSearch\\SentenceTransphormerXLMEmbedder";
        $embedder_class = $desc['class'] ?? "\\Textualization\\SemanticSearch\\SentenceTransphormerEmbedder";

        return new $embedder_class($desc);
    }

}
