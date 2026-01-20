<?php
class ParserUZ extends ParserRO
{
  protected $timezone = "Asia/Tashkent";

  protected function getNameServersRegExp()
  {
    return "/name server:(?! (?:not[\. ]defined\.|<no value>))(.+)/i";
  }
}
