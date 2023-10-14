<?php

namespace Textualization\SemanticSearch;

include_once 'util.php';

use Orhanerday\OpenAi\OpenAi;

class OpenAIService implements CompletionService {

    protected OpenAi $open_ai;
    protected float $temperature;
    protected string $model;

    public function __construct(array|string $desc = null)
    {
        $desc = get_json($desc);
        if(!isset($desc["open_ai_key"])){
            throw new \Exception("Missing OpenAI Key");
        }
        $key = $desc["open_ai_key"];
        if(! str_starts_with($key, "sk-")){
            if(file_exists($key)){
                $key = trim(file($key)[0]);
            }else{
                $key = getenv($key);
            }
        }
        $this->open_ai = new OpenAi($key);
        $this->model = $desc["model"] ?? "gpt-3.5-turbo";
        $this->temperature = $desc["temperature"] ?? 0.0;
    }

    public function complete(string $prompt, int $tokens) : string
    {
        $messages=[];
        $messages[] = [
            "role" => "user",
            "content" => $prompt
        ];        
        $complete = $this->open_ai->chat([
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $this->temperature,
            'max_tokens' => $tokens,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ]);

        //echo "\n\n$complete\n\n";
        
        $complete = json_decode($complete, true);
        return $complete['choices'][0]['message']['content'];
    }

}
