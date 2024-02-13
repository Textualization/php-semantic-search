<?php

namespace Textualization\SemanticSearch;

interface Embedder {

    public function encode(array|string $text) : array; // returns floating point vectors
    
    public function encode_query(array|string $text) : array; // returns floating point vectors

    public function is_asymmetric() : bool; // true is encode_query is different
    
    public function size() : int; // embedding size

    public function input_size() : int; // input size, in tokens, 0 or less for infinite

    public function tokenizer() : ?Tokenizer; // some embedders have no tokenizer
    
}
