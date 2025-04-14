<?php
class ParserCR extends Parser
{
  protected $timezone = "America/Costa_Rica";

  protected function getUpdatedDate()
  {
    $originalData = $this->data;

    $updatedDate = "";

    if (preg_match("/^(.+?)contact:/is", $this->data, $matches)) {
      $this->data = $matches[1];
      $updatedDate = parent::getUpdatedDate();
      $this->data = $originalData;
    }

    return $updatedDate;
  }
}
