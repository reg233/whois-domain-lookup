<?php
class ParserUA extends Parser
{
  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("organization");
  }

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("url");
  }

  protected function getStatus()
  {
    $originalData = $this->data;

    $status = [];

    if (preg_match("/^(.+)% registrar:/is", $this->data, $matches)) {
      $this->data = $matches[1];
      $status = parent::getStatus();
      $this->data = $originalData;
    }

    return $status;
  }
}
