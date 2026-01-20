<?php
class ParserTT extends Parser
{
  protected function getExpirationDateRegExp()
  {
    return "/expiration date: (.+) {6}/i";
  }

  protected function getStatusRegExp()
  {
    return "/expiration date: .+ {6}(.+)/i";
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("dns hostnames");
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode(",");
  }
}
