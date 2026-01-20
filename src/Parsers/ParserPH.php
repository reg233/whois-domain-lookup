<?php
class ParserPH extends Parser
{
  protected function getStatus($subject = null)
  {
    return $this->getStatusFromExplode(",");
  }
}
