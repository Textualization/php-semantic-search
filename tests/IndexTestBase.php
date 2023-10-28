<?php

namespace Textualization\SemanticSearch\Tests;

use Textualization\SemanticSearch\Ingester;
use Textualization\SemanticSearch\Index;
use Textualization\SemanticSearch\KeywordIndex;
use Textualization\SemanticSearch\Document;
use Textualization\SemanticSearch\Tokenizer;

use PHPUnit\Framework\TestCase;

abstract class IndexTestBase extends TestCase {

    public function __construct()
    {
        Ingester::$verbose = false;
        parent::__construct();
    }

    protected function _test_mrr(Index $index, array $title_url, float $mrr_min, float $mrr_max) : void
    {
        $this->assertEquals(1000, count($title_url));       
        $mrr = 0;
        $index->set_max_docs(1000);
        foreach($title_url as $title => $url) {
            $docs = $index->search($title);
            $len = count($docs);
            $rank = 0;
            while ($rank < $len and $docs[$rank]->url != $url) {
                $rank++;
            }
            $mrr += 1.0/($rank+1);
        }
        $mrr /= 1000.0;
        $this->assertGreaterThan($mrr_min, $mrr);
        $this->assertLessThan($mrr_max,    $mrr);
    }

}

