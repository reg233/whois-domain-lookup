<?php
class ParserBD extends Parser
{
  protected $dateFormat = "d/m/Y";

  protected function getUpdatedDate($subject = null)
  {
    return "";
  }

  protected function getStatusRegExp()
  {
    return $this->getBaseRegExp("domain status");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("(?:primary|secondary) dns");
  }
}
