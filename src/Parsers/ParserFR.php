<?php
class ParserFR extends Parser
{
  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("website");
  }

  protected function getStatusRegExp()
  {
    return $this->getBaseRegExp("eppstatus");
  }

  protected function getStatus()
  {
    $originalData = $this->data;

    $status = [];

    if (preg_match("/^(.+?)source:/is", $this->data, $matches)) {
      $this->data = $matches[1];
      $status = parent::getStatus();
      $this->data = $originalData;
    }

    return $status;
  }
}
