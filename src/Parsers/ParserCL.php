<?php
class ParserCL extends Parser
{
  protected $timezone = "America/Santiago";

  protected function getCreationDateISO8601()
  {
    return $this->getISO8601(substr($this->creationDate, 0, 19));
  }

  protected function getExpirationDateISO8601()
  {
    return $this->getISO8601(substr($this->expirationDate, 0, 19));
  }
}
