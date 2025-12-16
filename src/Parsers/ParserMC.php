<?php
class ParserMC extends Parser
{
  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern) +:(.+)/i";
  }
}
