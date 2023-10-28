<?php

namespace Textualization\SemanticSearch;

interface Index {

    public function search(string $query) : array; //<SearchResult>

    public function add(Document $doc) : void;
    public function fetch_document(string $url, int $chunk_num) : ?Document;

    public function tokenizer() : ?Tokenizer; // returns the tokenizer, if any

    // document size in tokens, if a tokenizer is defined. In characters, otherwise
    // -1 for any size
    public function document_size() : int;

    public function max_docs() : int;
    public function set_max_docs(int $max_docs) : void;

    public function close() : void;
}

