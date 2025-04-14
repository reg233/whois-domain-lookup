<?php
class ParserMO extends Parser
{
  protected $timezone = "Asia/Macau";

  protected function getCreationDateRegExp()
  {
    return "/record created on (.+)/i";
  }

  protected function getExpirationDateRegExp()
  {
    return "/record expires on (.+)/i";
  }

  protected function getNameServersRegExp()
  {
    return "/domain name servers:\n -+\n(.+)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromMultiLine();
  }
}
