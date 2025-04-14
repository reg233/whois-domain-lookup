<?php
class ParserBO extends Parser
{
  protected function getUnregisteredRegExp()
  {
    return "/no registrado/i";
  }

  protected function getDomainRegExp()
  {
    return $this->getBaseRegExp("nombre de dominio");
  }

  protected function getCreationDateRegExp()
  {
    return $this->getBaseRegExp("fecha de activación");
  }

  protected function getExpirationDateRegExp()
  {
    return $this->getBaseRegExp("fecha de corte");
  }
}
