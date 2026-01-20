<?php
class ParserTG extends Parser
{
  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern):\.+(.+)/i";
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("name server \(db\)");
  }
}
