<?php
class ParserPF extends Parser
{
  protected $dateFormat = "d/m/Y";

  protected function getDomainRegExp()
  {
    return "/informations about '(.+)'/i";
  }

  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("registrar compagnie name");
  }

  protected function getCreationDateRegExp()
  {
    return $this->getBaseRegExp("created \(jj\/mm\/aaaa\)");
  }

  protected function getExpirationDateRegExp()
  {
    return $this->getBaseRegExp("expire \(jj\/mm\/aaaa\)");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("name server \d");
  }
}
