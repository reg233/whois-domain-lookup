<?php
class ParserMD extends Parser
{
  protected function getExpirationDateRegExp()
  {
    return $this->getBaseRegExp("expire[sd] on");
  }

  protected function getStatus($subject = null)
  {
    return $this->getStatusFromExplode(" ");
  }
}
