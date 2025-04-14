<?php
class ParserJP extends Parser
{
  protected function getBaseRegExp($pattern)
  {
    return "/\[(?:$pattern)\](.+)/i";
  }
}
