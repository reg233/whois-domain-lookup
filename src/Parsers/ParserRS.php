<?php
class ParserRS extends Parser
{
  protected $timezone = "Europe/Belgrade";

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("dns");
  }
}
