<?php

namespace Textualization\SemanticSearch;

interface CompletionService {

    public function complete(string $prompt, int $tokens) : string;
    
}
