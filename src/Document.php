<?php

namespace Textualization\SemanticSearch;

class Document {

    public string $text;

    public ?string $title;

    public string $url;

    public int $chunk_num;

    public int $offset_start;

    public int $offset_end;

    public ?string $section;

    public ?string $license;

    public function __construct(array $arr)
    {
        $this->title = $arr["title"] ?? "";
        $this->url = $arr["url"] ?? "file:///dev/null";
        $this->text = $arr["text"];
        $this->offset_start = $arr["offset_start"];
        $this->offset_end = $arr["offset_end"];
        $this->section = $arr["section"] ?? "unknown";
        $this->license = $arr["license"] ?? "unknown";
        $this->chunk_num = $arr["chunk_num"] ?? 0;
    }

}
