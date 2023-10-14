<?php

namespace Textualization\SemanticSearch;

class VectorHydeIndex extends VectorIndex {

    protected CompletionService $completion;

    protected int $target_size;
    
    public function __construct(array|string $desc = null)
    {
        if(is_null($desc)){
            $desc=[];
        }elseif(!is_array($desc)) {
            $desc = json_decode($desc);
        }

        parent::__construct($desc);

        if(!isset($desc["completion"])){
            throw new \Exception("Missing completion service");
        }
        $this->completion = CompletionServiceFactory::make($desc["completion"]);
        $this->target_size = $desc["target_size"] ?? 512;
    }

    public function search(string $query) : array //<SearchResult>
    {
        $hyde_query = $this->completion->complete($query, $this->target_size);
        echo "Hdydrated query: $hyde_query\n";
        return parent::search($hyde_query);
    }
}
