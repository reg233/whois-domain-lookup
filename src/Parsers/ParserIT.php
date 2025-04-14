<?php
class ParserIT extends Parser
{
  protected $timezone = "Europe/Rome";

  protected function getUnregisteredRegExp()
  {
    return "/status: {13}available/i";
  }

  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("registrar\n.+\n {2}name");
  }

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("registrar\n.+\n.+\n {2}web");
  }

  protected function getStatus()
  {
    return $this->getStatusFromExplode("/");
  }

  protected function getNameServersRegExp()
  {
    return "/nameservers(.+)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromMultiLine();
  }
}
