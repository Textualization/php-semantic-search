<?php

namespace Textualization\SemanticSearch;

interface Tokenizer {

    public function encode(string $text): array;
    
    public function count(string $text): int;
    
}
