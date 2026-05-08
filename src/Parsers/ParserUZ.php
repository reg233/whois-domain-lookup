<?php
class ParserUZ extends Parser
{
  protected $timezone = "Asia/Tashkent";

  protected function getNameServersRegExp()
  {
    return "/name server:(?! (?:not[\. ]defined\.|<no value>))(.+)/i";
  }
}
