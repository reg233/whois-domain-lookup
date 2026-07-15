<?php

declare(strict_types=1);

class ParserLV extends Parser
{
  protected function getRegistrarRegExp(): string
  {
    return "/\[registrar\]\n.+\nname:(.+)/i";
  }

  protected function getUpdatedDate(?string $subject = null): string
  {
    return "";
  }
}
