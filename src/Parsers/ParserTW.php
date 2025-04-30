<?php
class ParserTW extends Parser
{
  protected $timezone = "Asia/Taipei";

  protected function getCreationDateRegExp()
  {
    return "/record created on (.+)/i";
  }

  protected function getCreationDateISO8601()
  {
    return $this->getISO8601(substr($this->creationDate, 0, 19));
  }

  protected function getExpirationDateRegExp()
  {
    return "/record expires on (.+)/i";
  }

  protected function getExpirationDateISO8601()
  {
    return $this->getISO8601(substr($this->expirationDate, 0, 19));
  }

  protected function getStatus()
  {
    return $this->getStatusFromExplode(",");
  }

  protected function getNameServersRegExp()
  {
    return "/domain servers in listed order:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromExplode("\n");
  }
}
