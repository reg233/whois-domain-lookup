<?php
class ParserIT extends Parser
{
  protected $timezone = "Europe/Rome";

  protected function getReservedRegExp()
  {
    // it.it
    return "/status: {13}unassignable/i";
  }

  protected function getUnregisteredRegExp()
  {
    return "/status: {13}available/i";
  }

  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("registrar\n.+\n  name");
  }

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("web");
  }

  protected function getStatus($subject = null)
  {
    return $this->getStatusFromExplode("/");
  }

  protected function getNameServersRegExp()
  {
    return "/nameservers(.+)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\n");
  }
}
