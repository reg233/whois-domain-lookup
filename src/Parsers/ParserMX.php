<?php
class ParserMX extends Parser
{
  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("url");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("dns");
  }
}
