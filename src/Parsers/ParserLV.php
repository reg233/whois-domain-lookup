<?php
class ParserLV extends Parser
{
  protected function getRegistrarRegExp()
  {
    return "/\[registrar\]\n.+\nname:(.+)/i";
  }

  protected function getUpdatedDate($subject = null)
  {
    return "";
  }
}
