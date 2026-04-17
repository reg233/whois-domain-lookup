<?php
class ParserSI extends Parser
{
  protected function getReservedRegExp()
  {
    // Conflict with .nu and .se extension
    // si.si
    return "/is forbidden/i";
  }

  protected function getStatus($subject = null)
  {
    return $this->getStatusFromExplode(",");
  }
}
