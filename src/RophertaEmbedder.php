<?php

namespace Textualization\SemanticSearch;

class RophertaEmbedder extends \Textualization\Ropherta\RophertaModel implements Embedder {

    public function __construct(array $params)
    {
        $model = $params["model"] ?? null;
        $input_size = $params["input_size"] ?? 512;
        parent::__construct($model, $input_size);
    }

    public function encode(array|string $text) : array
    {
        $full_output = $this->_encode($text);

        $layer = $full_output["output_1"];
        $pool = $layer[0][0];
        $len = count($pool);
        $wlen = count($layer[0]);
        for($i=1; $i<$wlen; $i++)
            for($j=0; $j<$len; $j++)
                $pool[$j] += $layer[0][$i][$j];
        for($j=0; $j<$len; $j++)
            $pool[$j] /= $wlen;
        return $pool;
        //return $full_output["output_1"][0][0];
        //return $full_output["output_2"][0];
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
