<?php
class ParserTO extends Parser
{
  protected $timezone = "Pacific/Tongatapu";

  protected function getNameServersRegExp()
  {
    return "/host name:(.+)/i";
  }
}
