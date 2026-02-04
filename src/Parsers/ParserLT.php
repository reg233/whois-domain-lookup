<?php
class ParserLT extends Parser
{
  protected function getReservedRegExp()
  {
    // fuck.lt
    return "/status:\t{3}blocked/i";
  }

  protected function getUnregisteredRegExp()
  {
    return "/status:\t{3}available/i";
  }
}
