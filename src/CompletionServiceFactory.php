<?php

namespace Textualization\SemanticSearch;

include_once 'util.php';

class CompletionServiceFactory {
    
    public static function make(array|string|null $desc = null) : CompletionService
    {
        $desc = get_json($desc);
        $index_class = $desc['class'] ?? "\\Textualization\\SemanticSearch\\OpenAIService";

        return new $index_class($desc);
    }
    
}
