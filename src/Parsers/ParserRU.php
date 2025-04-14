<?php
class ParserRU extends Parser
{
  protected function getStatusRegExp()
  {
    return $this->getBaseRegExp("state");
  }

  protected function getStatus()
  {
    return $this->getStatusFromExplode(",");
  }
}
