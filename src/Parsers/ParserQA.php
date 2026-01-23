<?php
class ParserQA extends Parser
{
  protected function getReservedRegExp()
  {
    // 0.qa
    // qa.qa
    return "/reserved by qdr|is not available/i";
  }

  protected function getStatus($subject = null)
  {
    if (preg_match_all($this->getStatusRegExp(), $this->data, $matches)) {
      return array_map(
        fn($item) => ["text" => explode(" ", $item)[0], "url" => ""],
        array_values(array_unique(array_filter(array_map("trim", $matches[1])))),
      );
    }

    return [];
  }
}
