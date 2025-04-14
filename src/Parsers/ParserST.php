<?php
class ParserST extends Parser
{
  protected $timezone = "Africa/Sao_Tome";

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp("url");
  }

  protected function getCreationDateRegExp()
  {
    return $this->getBaseRegExp("created-date");
  }

  protected function getExpirationDateRegExp()
  {
    return $this->getBaseRegExp("expiration-date");
  }

  protected function getUpdatedDateRegExp()
  {
    return $this->getBaseRegExp("updated-date");
  }
}
