<?php
class ParserLT extends Parser
{
  protected function getReservedRegExp()
  {
    return "/status:\t{3}blocked/i";
  }

  protected function getUnregisteredRegExp()
  {
    return "/status:\t{3}available/i";
  }
}
