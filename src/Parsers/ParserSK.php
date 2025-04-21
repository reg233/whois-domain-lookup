<?php
class ParserSK extends Parser
{
  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("registrar:.+\nname:.+\norganization");
  }
}
