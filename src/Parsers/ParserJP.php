<?php
class ParserJP extends Parser
{
  protected function getReservedRegExp()
  {
    // com.jp
    return "/\[Status\] {24}reserved/i";
  }

  protected function getBaseRegExp($pattern)
  {
    return "/\[(?:$pattern)\](.+)/i";
  }
}
