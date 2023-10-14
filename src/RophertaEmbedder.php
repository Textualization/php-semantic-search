<?php

namespace Textualization\SemanticSearch;

class RophertaEmbedder extends \Textualization\Ropherta\RophertaModel implements Embedder {

    public function encode(array|string $text) : array
    {
        return $this->embeddings($text);
    }
    
    public function size() : int
    {
        return 768;
    }

    public function tokenizer() : Tokenizer
    {
        return RophertaTokenizer::wrap($this->tokenizer);
    }

    public function input_size() : int
    {
        return $this->input_size;
    }
}
