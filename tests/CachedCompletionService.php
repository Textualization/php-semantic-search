<?php

namespace Textualization\SemanticSearch\Tests;

use Textualization\SemanticSearch\CompletionService;

class CachedCompletionService implements CompletionService {

    private array $table;

    public function __construct(array|string $desc = null) {
        $this->table = $desc["cache"];
    }

    public function complete(string $prompt, int $tokens) : string
    {
        return $this->table[$prompt];
    }
}
