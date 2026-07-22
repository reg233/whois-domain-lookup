<?php

declare(strict_types=1);

class ParserJP extends Parser
{
  protected function getReservedRegExp(): string
  {
    // com.jp
    return "/\[Status\] {24}reserved/i";
  }

  protected function getBaseRegExp(string $pattern): string
  {
    return "/\[(?:$pattern)\](.+)/i";
  }

  protected function getStatusRegExp(): string
  {
    // Some SLD statuses use "State" or "Lock Status".
    return $this->getBaseRegExp("status|state|lock status");
  }

  protected function getStatus(?string $subject = null): array
  {
    if (preg_match_all($this->getStatusRegExp(), $this->data, $matches)) {
      // Some SLD statuses include a date suffix in the format "(yyyy-mm-dd)".
      return array_map(
        fn($item) => ["text" => trim(explode("(", $item)[0]), "url" => ""],
        array_values(array_unique(array_filter(array_map("trim", $matches[1])))),
      );
    }

    return [];
  }
}
