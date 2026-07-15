<?php

declare(strict_types=1);

class ParserMX extends Parser
{
  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("url");
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("dns");
  }
}
