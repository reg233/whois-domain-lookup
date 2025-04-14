<?php
class ParserIL extends Parser
{
  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("registrar info");
  }

  protected function getUpdatedDateRegExp()
  {
    return "/^changed:.+(\d{8}) \(changed\)$/im";
  }

  protected function getUpdatedDate()
  {
    if (preg_match_all($this->getUpdatedDateRegExp(), $this->data, $matches)) {
      return end($matches[1]);
    }

    return "";
  }
}
