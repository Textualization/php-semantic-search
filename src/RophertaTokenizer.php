<?php

namespace Textualization\SemanticSearch;

class RophertaTokenizer extends \Textualization\Ropherta\Tokenizer implements Tokenizer {

    public static function wrap(\Textualization\Ropherta\Tokenizer $wrapped) : Tokenizer
    {
        return new class($wrapped) implements Tokenizer {
            private \Textualization\Ropherta\Tokenizer $wrapped;

            public function __construct($wrapped)
            {
                $this->wrapped = $wrapped;
            }
            public function encode(string $text): array
            {
                return $this->wrapped->encode($text);
            }
    
            public function count(string $text): int
            {
                return $this->wrapped->count($text);
            }
        };
    }
    
    public static function wrapXLM(\Textualization\Ropherta\XLMTokenizer $wrapped) : Tokenizer
    {
        return new class($wrapped) implements Tokenizer {
            private \Textualization\Ropherta\XLMTokenizer $wrapped;

            public function __construct($wrapped)
            {
                $this->wrapped = $wrapped;
            }
            public function encode(string $text): array
            {
                return $this->wrapped->encode($text);
            }
    
            public function count(string $text): int
            {
                return $this->wrapped->count($text);
            }
        };
    }
}
