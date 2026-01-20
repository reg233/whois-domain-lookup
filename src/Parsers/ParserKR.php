<?php
class ParserKR extends Parser
{
  protected $dateFormat = "Y. m. d.";

  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("authorized agency");
  }
}
