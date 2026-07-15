<?php

declare(strict_types=1);

class ParserCU extends Parser
{
  protected function getUnregistered(): bool
  {
    return str_starts_with($this->data, "Existe(n) 0 dominio(s)");
  }

  protected function getDomainRegExp(): string
  {
    return $this->getBaseRegExp("dominio");
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("dns (?:primario|secundario)\nnombre");
  }
}
