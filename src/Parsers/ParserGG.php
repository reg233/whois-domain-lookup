<?php
class ParserGG extends Parser
{
  protected $dateFormat = 'jS F Y \a\t H:i:s.u';

  protected $timezone = "Europe/Guernsey";

  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern):(.+?)(?=\n\n)/is";
  }

  protected function getCreationDateRegExp()
  {
    return "/registered on (.+)/i";
  }

  protected function getStatus($subject = null)
  {
    return $this->getStatusFromExplode("\n");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("name servers");
  }

  protected function getNameServers()
  {
    return $this->getNameServersFromExplode("\n");
  }
}
