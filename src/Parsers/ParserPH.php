<?php
class ParserPH extends Parser
{
  protected function getStatus()
  {
    return $this->getStatusFromExplode(",");
  }
}
