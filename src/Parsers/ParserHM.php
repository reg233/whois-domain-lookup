<?php
class ParserHM extends Parser
{
  protected $dateFormat = "d/m/Y";

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("referral url");
  }
}
