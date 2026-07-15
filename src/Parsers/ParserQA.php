<?php

declare(strict_types=1);

class ParserQA extends Parser
{
  protected function getReservedRegExp(): string
  {
    // 0.qa
    // qa.qa
    return "/reserved by qdr|is not available/i";
  }

  protected function getStatus(?string $subject = null): array
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
