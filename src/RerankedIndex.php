<?php

namespace Textualization\SemanticSearch;

class RerankedIndex implements Index {
    protected Index $main;
    protected Index $reranker;

    protected bool $use_half_key;
    
    public function __construct(array|string $desc = null)
    {
        if(is_null($desc)){
            $desc=[];
        }elseif(!is_array($desc)) {
            $desc = json_decode($desc);
        }

        $this->main     = IndexFactory::make($desc["main"]);
        $this->reranker = IndexFactory::make($desc["reranker"]);

        $this->main->set_max_docs($desc["max_docs_main"] ?? 50);
        $this->reranker->set_max_docs($desc["max_docs_reranker"] ?? 50);

        $this->use_half_key = $desc["use_half_key"] ?? false;
    }

    public function search(string $query) : array //<SearchResult>
    {
        $main     = $this->main->search($query);
        $reranked = $this->reranker->search($query);

        $main_set = array();
        $count = 0;
        foreach($main as $result_doc) {
            if($count > $this->main->max_docs()){
                break;
            }
            $main_set[$result_doc->key($this->use_half_key)] = true;
        }
        $result = array();

        foreach($reranked as $result_doc) {
            if(isset($main_set[$result_doc->key($this->use_half_key)])){
                $result[] = $result_doc;
            }
        }
        return $result;
    }

    public function add(Document $document) : void
    {
        $this->main->add($document);
        $this->reranker->add($document);
    }

    public function tokenizer() : ?Tokenizer
    {
        return $this->main->tokenizer() ?? $this->reranker->tokenizer();
    }

    public function max_docs() : int
    {
        return $this->main->max_docs();
    }
    
    public function set_max_docs(int $max_docs) : void
    {
        $this->main->set_max_docs($max_docs);
    }

    public function fetch_document(string $url, int $chunk_num) : ?Document
    {
        return $this->reranker->fetch_document($url, $chunk_num);
    }    
    public function document_size() : int
    {
        $main_ds = $this->main->document_size();
        $reranker_ds = $this->reranker->document_size();
        return $main_ds < 0 ? ($reranker_ds < 0 ? -1 : $reranker_ds) :
            ( $reranker_ds < 0 ? $main_ds : min($main_ds, $reranker_ds));
    }
    
    public function close() : void
    {
        $this->main->close();
        $this->reranker->close();
    }
}

