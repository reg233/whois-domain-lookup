<?php
class ParserLT extends Parser
{
  protected function getUnregisteredRegExp()
  {
    return "/status:\t{3}available/i";
  }
}
