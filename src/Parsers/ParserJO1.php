<?php

declare(strict_types=1);

/**
 * Parser for the الاردن extension.
 */
class ParserJO1 extends ParserJO
{
  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("(?:primary|secondary) server\d{0,2}");
  }
}
