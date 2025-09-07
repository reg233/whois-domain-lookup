<?php
class ParserCU extends Parser
{
  protected function getUnregistered()
  {
    return str_contains($this->data, "Existe(n) 0 dominio(s) con el criterio");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("dns (?:primario|secundario)\nnombre");
  }
}
