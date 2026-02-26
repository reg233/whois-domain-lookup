<?php
class ParserPY extends Parser
{
  protected function getUnregisteredRegExp()
  {
    return "/no encontrados/i";
  }

  protected function getDomainRegExp()
  {
    return "/^(.*)/";
  }

  protected function getStatusRegExp()
  {
    return $this->getBaseRegExp("estado");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("dns (?:primario|secundario|alternativo #\d)");
  }
}
