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
}
