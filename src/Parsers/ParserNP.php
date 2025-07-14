<?php
class ParserNP extends Parser
{
  protected $timezone = "Asia/Kathmandu";

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("primary name server|secondary name server");
  }
}
