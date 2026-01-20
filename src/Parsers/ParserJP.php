<?php
class ParserJP extends Parser
{
  protected function getReservedRegExp()
  {
    return "/\[Status\] {24}reserved/i";
  }

  protected function getBaseRegExp($pattern)
  {
    return "/\[(?:$pattern)\](.+)/i";
  }
}
