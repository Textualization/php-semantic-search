<?php

namespace Textualization\SemanticSearch;

include_once 'util.php';

class Ingester {

    protected static $END_CHARS = [ '.', ':', '?', '!', ';' ];

    public static function ingest(array|string|Index|null $indexdesc, array|string|null $ingesterdesc, string $jsonl) : void
    {
        $index    = IndexFactory::make($indexdesc);
        $ingester = new Ingester($index, $ingesterdesc);

        $handle = fopen($jsonl, "r");
        if (! $handle) {
            throw new \Exception("Cannot open JSONL file: $jsonl\n");
        }
        
        $tokenizer = $index->tokenizer();
        $max_size = $index->document_size();
        $count = 0;
        while (($line = fgets($handle)) !== false) {
            $row = json_decode($line, true);
            if ($row === null) {
                echo "Cannot parse JSON at line $count: '$line'\n";
            }else{
                $ingester->_add( $row, $tokenizer, $max_size );
            }
            $count++;
            if($count % 1000 == 0){
                echo "$count documents indexed...\n";
            }
        }
        fclose($handle);
    }

    protected Index $index;

    protected ?Tokenizer $tokenizer;

    protected bool $add_title;

    protected array $end_chars;

    public function __construct(Index $index, array|string|null $desc = null)
    {
        $desc = get_json($desc);
        $this->index = $index;
        $this->tokenizer = $index->tokenizer();
        $this->add_title = $desc['add_title'] ?? true;
        $this->end_chars = array_flip(Ingester::$END_CHARS);
    }

    // split on a separator
    // if multiple, contiguous splits can be joined and still be less than $max_size, they are joined
    // returns the splits plus their length and beginning offset, end offset
    protected function _split(
        string $separator, string $text, ?Tokenizer $tokenizer, int $max_size
    ) : array
    {
        //print_r($separator);
        $matches = [];
        preg_match_all($separator, $text, $matches, PREG_OFFSET_CAPTURE);
        $result = [];
        $current = "";
        $current_len = 0;
        $current_start = 0;
        $current_end = 0;
        $prev_offset = 0;
        foreach($matches[0] as $match) {
            $split = substr($text, $prev_offset, $match[1] - $prev_offset);
            $len = $tokenizer ? $tokenizer->count($split) : strlen($split);
            if($len >= $max_size) {
                if($current) {
                    $result[] = [ $current, $current_len, $current_start, $current_end ];
                    $current = ""; $curent_len = 0;
                }
                $result[] = [ $split, $len, $prev_offset, $match[1] ];
            }else{
                if($current) {
                    $extended = substr($text, $current_start, $match[1] - $current_start);
                    $elen = $tokenizer ? $tokenizer->count($extended) : strlen($extended);
                    if($elen >= $max_size){
                        $result[] = [ $current, $current_len, $current_start, $current_end ];
                        $current = $split;
                        $current_len = $len;
                        $current_start = $prev_offset;
                        $current_end = $match[1];
                    }else{
                        $current = $extended;
                        $current_len = $elen;
                        $current_end = $match[1];
                    }
                }else{
                    $current = $split;
                    $current_len = $len;
                    $current_start = $prev_offset;
                    $current_end = $match[1];
                }
            }
            $prev_offset = $match[1] + strlen($match[0]);
        }
        $end = strlen($text);
        if($prev_offset != $end) {
            $split = substr($text, $prev_offset);
            $len = strlen($split);
            if($current) {
                $extended = substr($text, $current_start);
                $elen = $tokenizer ? $tokenizer->count($extended) : strlen($extended);
                if($elen >= $max_size){
                    $result[] = [ $current, $current_len, $current_start, $current_end ];
                    $current = $split;
                    $current_len = $len;
                    $current_start = $prev_offset;
                    $current_end = $end;
                }else{
                    $current = $extended;
                    $current_len = $elen;
                    $current_end = $end;
                }
            }else{
                $current = $split;
                $current_len = $len;
                $current_start = $prev_offset;
                $current_end = $end;
            }
        }
        if($current) {
            $result[] = [ $current, $current_len, $current_start, $current_end ];
        }
        //echo count($result)." ";
        return $result;
    }

    protected function split(string $text, ?Tokenizer $tokenizer, int $max_size) : array
    {
        if($max_size < 0)
            return [ $text ];
        
        // split in \n\n+
        $large_splits = $this->_split('/\n\n+/', $text, $tokenizer, $max_size);
        $result = [];
        foreach($large_splits as $large_pair) {
            [ $large_split, $large_len, $large_start, $large_end ] = $large_pair;
            //echo "($large_len)";
            if($large_len < $max_size) {
                $result[] = [ $large_split, $large_start, $large_end ];
                continue;
            }
            // split on lines
            $small_splits = $this->_split('/\n/', $large_split, $tokenizer, $max_size);
            foreach($small_splits as $small_pair) {
                [ $small_split, $small_len, $small_start, $small_end ] = $small_pair;
                if($small_len < $max_size) {
                    $result[] = [ $small_split, $large_start + $small_start, $large_start + $small_end ];
                    continue;
                }
                // split on spaces
                $tiny_splits = $this->_split('/\s+/', $small_split, $tokenizer, $max_size);
                foreach($tiny_splits as $tiny_pair) {
                    [ $tiny_split, $tiny_len, $tiny_start, $tiny_end ] = $tiny_pair;
                    if($tiny_len < $max_size) {
                        $result[] = [ $tiny_split, $large_start + $small_start + $tiny_start, $large_start + $small_start + $tiny_end ];
                        continue;
                    }
                    // split on chars
                    $char_splits = $this->_split('//', $tiny_split, $tokenizer, $max_size);
                    foreach($char_splits as $char_pair) {
                        $result[] = [ $char_pair[0],
                                      $large_start + $small_start + $tiny_start + $char_pair[2],
                                      $large_start + $small_start + $tiny_start + $char_pair[3] ] ;
                    }
                }
            }
        }
        return $result;
    }
    
    protected function _add(array $row, ?Tokenizer $tokenizer, int $max_size) : void
    {
        $text = $row['text'] ?? "";
        if($this->add_title) {
            $title = trim($row['title'] ?? "");
            $len = strlen($title);
            if($len){
                if(! array_key_exists($title[$len-1], $this->end_chars)){
                    $title = "$title.";
                }
                $text = "$title\n$text";
            }
        }
        if(empty($text))
            return;
        if($max_size > 0) {
            //echo "\n=>".$row["url"]." text_len=".strlen($text)."\n";
            $chunks = $this->split($text, $tokenizer, $max_size);
            foreach($chunks as $idx => $chunk_offsets) {
                [ $chunk, $offset_start, $offset_end ] = $chunk_offsets;
                //echo "->".$row["url"]."#".$idx.": ".$offset_start."-".$offset_end."\n";
                $this->index->add(new Document([
                    "title" => $row["title"] ?? "",
                    "text" => $chunk,
                    "url" => $row["url"] ?? "file:///dev/null",
                    "chunk_num" => $idx,
                    "offset_start" => $offset_start,
                    "offset_end" => $offset_end,
                    "section" => $row["section"] ?? "unknown",
                    "license" => $row["license"] ?? "unknown"
                ]));
            }
        }else{
            $this->index->add(new Document([
                "title" => $row["title"] ?? "",
                "text" => $text,
                "url" => $row["url"] ?? "file:///dev/null",
                "chunk_num" => 0,
                "offset_start" => 0,
                "offset_end" => strlen($row["text"]),
                "section" => $row["section"] ?? "unknown",
                "license" => $row["license"] ?? "unknown"
            ]));
        }
    }
    
    public function add(array $row) : void
    {
        $tokenizer = $this->index->tokenizer();
        $max_size = $this->index->document_size();
        $this->_add($row, $tokenizer, $max_size);
    }
}
