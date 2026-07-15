<?php

declare(strict_types=1);

class ParserAX extends Parser
{
  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("www");
  }
}
