<?php
class ParserQA extends Parser
{
  protected function getStatus()
  {
    if (preg_match_all($this->getStatusRegExp(), $this->data, $matches)) {
      return array_map(
        fn($item) => ["text" => explode(" ", $item)[0], "url" => ""],
        array_unique(array_filter(array_map("trim", $matches[1]))),
      );
    }

    return [];
  }
}
