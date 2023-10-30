<?php

namespace Textualization\SemanticSearch;

interface Embedder {

    public function encode(array|string $text) : array; // returns floating point vectors
    
    public function size() : int; // embedding size

    public function input_size() : int; // input size, in tokens, 0 or less for infinite

    public function tokenizer() : ?Tokenizer; // some embedders have no tokenizer
    
}
