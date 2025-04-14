<?php
class ParserRO extends Parser
{
  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("referral url");
  }
}
