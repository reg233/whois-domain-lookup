<?php

declare(strict_types=1);

class ParserEE extends Parser
{
  protected function getDomainRegExp(): string
  {
    return $this->getBaseRegExp("domain:\nname");
  }

  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp("registrar:\nname");
  }

  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("url");
  }
}
