<?php

declare(strict_types=1);

class ParserGR extends Parser
{
  protected function getReservedRegExp(): string
  {
    // gr.gr, gr.ελ
    return "/not acceptable/i";
  }

  protected function getUnregisteredRegExp(): string
  {
    return "/can be provisioned/i";
  }

  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp("name");
  }

  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("website");
  }
}
