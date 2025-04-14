<?php
class ParserAX extends Parser
{
  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern)\.+:(.+)/i";
  }

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("www");
  }
}
