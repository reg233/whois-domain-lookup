<?php
class ParserGM extends Parser
{
  protected function getUnregisteredRegExp()
  {
    return "/is still available/i";
  }

  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("registrar \(company\)");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("name server #\d");
  }
}
