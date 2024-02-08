<?php

namespace Textualization\SemanticSearch;

class SentenceTransphormerEmbedder extends \Textualization\SentenceTransphormers\SentenceRopherta implements Embedder {

    public function __construct(array $params)
    {
        $model = $params["model"] ?? null;
        $input_size = $params["input_size"] ?? 512;
        parent::__construct($model, $input_size);
    }
    
    public function encode(array|string $text) : array
    {
        return $this->_encode($text);
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
