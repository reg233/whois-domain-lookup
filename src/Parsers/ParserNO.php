<?php
class ParserNO extends Parser
{
  protected function getDomainRegExp()
  {
    return $this->getBaseRegExp("domain name\.+");
  }

  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("registrar handle\.+");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("name server handle\.+");
  }
}
