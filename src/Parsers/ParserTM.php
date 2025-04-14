<?php
class ParserTM extends Parser
{
  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern) :(.+)/i";
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("ns \d +");
  }
}
