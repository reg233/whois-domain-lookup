<?php
class ParserEE extends Parser
{
  protected function getDomainRegExp()
  {
    return $this->getBaseRegExp("domain:\nname");
  }

  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("registrar:\nname");
  }

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("url");
  }
}
