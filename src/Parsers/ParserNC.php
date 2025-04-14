<?php
class ParserNC extends Parser
{
  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern) +:(.+)/i";
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("domain server \d");
  }
}
