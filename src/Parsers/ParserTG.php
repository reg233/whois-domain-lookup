<?php
class ParserTG extends Parser
{
  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern):\.+(.+)/i";
  }
}
