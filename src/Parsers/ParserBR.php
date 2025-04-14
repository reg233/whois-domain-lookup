<?php
class ParserBR extends Parser
{
  protected function getCreationDateISO8601()
  {
    return $this->getISO8601(explode(" ", $this->creationDate)[0]);
  }
}
