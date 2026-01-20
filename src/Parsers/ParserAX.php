<?php
class ParserAX extends Parser
{
  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("www");
  }
}
