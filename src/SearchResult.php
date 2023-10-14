<?php

namespace Textualization\SemanticSearch;

class SearchResult implements \Stringable {

    public ?string $title;

    public string $url;

    public int $chunk_num;

    public int $offset_start;

    public int $offset_end;

    public float $score;

    public function __construct(float $score, string $url, int $chunk_num = 0, int $offset_start=-1, int $offset_end=-1, ?string $title=null)
    {
        $this->title = $title;
        $this->url = $url;
        $this->score = $score;
        $this->chunk_num = $chunk_num;
        $this->offset_start = $offset_start;
        $this->offset_end = $offset_end;
    }

    public function key(?bool $half=false) : string
    {
        return $this->url.($half ? "" : " ".$this->chunk_num);
    }

    public function __toString() : string
    {
        return "SearchResult[".$this->score.",".$this->key().", '".$this->title."']";
    }
}
