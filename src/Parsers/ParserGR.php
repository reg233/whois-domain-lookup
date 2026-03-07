<?php
class ParserGR extends Parser
{
  protected function getReservedRegExp()
  {
    // gr.gr, gr.ελ
    return "/not acceptable/i";
  }

  protected function getUnregisteredRegExp()
  {
    return "/can be provisioned/i";
  }

  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("name");
  }

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("website");
  }
}
