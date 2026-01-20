<?php
class ParserCU extends Parser
{
  protected function getUnregistered()
  {
    return str_starts_with($this->data, "Existe(n) 0 dominio(s)");
  }

  protected function getDomainRegExp()
  {
    return $this->getBaseRegExp("dominio");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("dns (?:primario|secundario)\nnombre");
  }
}
