<?php
class ParserJO extends Parser
{
  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("(?:primary|secondary) server\d{,2}");
  }
}
