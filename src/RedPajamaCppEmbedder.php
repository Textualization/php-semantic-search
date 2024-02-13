<?php

namespace Textualization\SemanticSearch;

class RedPajamaCppEmbedder implements Embedder {

    protected string $host;
    protected int $port;    

    public function __construct(array $params)
    {
        $this->host = $params["host"] ?? "localhost";
        $this->port = $params["port"] ?? 8080;
    }
        
    public function encode(array|string $text) : array
    {
        if(is_array($text)) throw new \Exception("This embedder only works over text.");
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->host . ":" . $this->port . "/embedding");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode( [ "content" => $text ] ));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));        
        $output = curl_exec($curl);
        curl_close($curl);

        if(!$output) throw new \Exception("Error contacting RedPajama server");        
        
        $output = json_decode($output, true);
        return $output["embedding"];
    }
    
    public function size() : int
    {
        return 2560;
    }

    public function tokenizer() : ?Tokenizer
    {
        return null;
    }

    public function input_size() : int
    {
        return -1;
    }

    public function encode_query(array|string $text) : array
    {
        return $this->encode($text);
    }

    public function is_asymmetric() : bool
    {
        return false;
    }
}
