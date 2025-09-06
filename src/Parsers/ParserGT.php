<?php
class ParserGT extends Parser
{
  protected $timezone = "America/Guatemala";

  protected function getNameServersRegExp()
  {
    return "/servers:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromExplode("\n");
  }
}
