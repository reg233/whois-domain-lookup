<?php
class ParserBB extends Parser
{
  protected $timezone = "America/Barbados";

  protected function getUnregistered()
  {
    return str_contains($this->data, 'ERROR: Can\'t open file "/home/whois/static/update.txt"');
  }
}
