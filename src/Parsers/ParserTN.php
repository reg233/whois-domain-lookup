<?php
class ParserTN extends Parser
{
  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern)\.+:(.+)/i";
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("name");
  }

  protected function getNameServers()
  {
    $originalData = $this->data;

    $nameServers = [];

    if (preg_match("/dns servers(.+?)(?=\n\n)/is", $this->data, $matches)) {
      $this->data = $matches[1];
      $nameServers = parent::getNameServers();
      $this->data = $originalData;
    }

    return $nameServers;
  }
}
