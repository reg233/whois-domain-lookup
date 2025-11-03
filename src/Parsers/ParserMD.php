<?php
class ParserMD extends Parser
{
  protected function getDomainRegExp()
  {
    return $this->getBaseRegExp("domain  name");
  }

  protected function getExpirationDateRegExp()
  {
    return $this->getBaseRegExp("expire[sd] on");
  }

  protected function getStatusRegExp()
  {
    return $this->getBaseRegExp("domain state");
  }

  protected function getStatus()
  {
    return $this->getStatusFromExplode(" ");
  }
}
