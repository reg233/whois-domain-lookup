<?php
class ParserRU extends Parser
{
  protected function getStatusRegExp()
  {
    return $this->getBaseRegExp("state");
  }

  protected function getStatus($subject = null)
  {
    return $this->getStatusFromExplode(",");
  }
}
