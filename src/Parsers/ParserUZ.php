<?php

declare(strict_types=1);

class ParserUZ extends Parser
{
  protected string $timezone = "Asia/Tashkent";

  protected function getNameServersRegExp(): string
  {
    return "/name server:(?! (?:not[\. ]defined\.|<no value>))(.+)/i";
  }
}
