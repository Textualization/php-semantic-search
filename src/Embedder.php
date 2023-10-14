<?php

namespace Textualization\SemanticSearch;

interface Embedder {

    public function encode(array|string $text) : array; // returns floating point vectors
    
    public function size() : int; // embedding size

    public function input_size() : int; // input size, in tokens

    public function tokenizer() : Tokenizer;
    
}
