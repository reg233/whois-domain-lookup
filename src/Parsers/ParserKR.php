<?php
class ParserKR extends Parser
{
  protected $dateFormat = "Y. m. d.";

  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern) +:(.+)/i";
  }

  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("authorized agency");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("host name");
  }
}
