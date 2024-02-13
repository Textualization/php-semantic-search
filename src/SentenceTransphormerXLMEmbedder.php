<?php

namespace Textualization\SemanticSearch;

class SentenceTransphormerXLMEmbedder extends \Textualization\SentenceTransphormers\SentenceXLMRopherta implements Embedder {

    public function __construct(array $params)
    {
        $model = $params["model"] ?? null;
        $input_size = $params["input_size"] ?? 512;
        parent::__construct($model, $input_size);
    }
    
    public function encode(array|string $text) : array
    {
        $result = $this->_encode($text);
        if(! $result) {
            echo "Error in encode\n";
            $result = array_fill(0, $this->size(), 0);
        }
        return $this->_encode($text);
    }

    public function encode_query(array|string $text) : array
    {
        $result = $this->_encode_query($text);
        if(! $result) {
            echo "Error in encode_query\n";
            $result = array_fill(0, $this->size(), 0);
        }
        return $result;
    }

    public function is_asymmetric() : bool
    {
        return true;
    }
    
    public function size() : int
    {
        return 384;
    }

    public function tokenizer() : Tokenizer
    {
        return RophertaTokenizer::wrapXLM($this->xlmTokenizer);
    }

    public function input_size() : int
    {
        return $this->input_size;
    }
}
