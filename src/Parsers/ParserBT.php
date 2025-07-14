<?php
class ParserBT extends Parser
{
  protected $timezone = "Asia/Thimphu";

  protected function getExpirationDateRegExp()
  {
    return $this->getBaseRegExp("expiration date");
  }
}
