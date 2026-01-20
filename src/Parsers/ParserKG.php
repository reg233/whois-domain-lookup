<?php
class ParserKG extends Parser
{
  protected $timezone = "Asia/Bishkek";

  protected function getDomainRegExp()
  {
    return "/^domain (.+) \(.+\)$/im";
  }

  protected function getStatusRegExp()
  {
    return "/^domain .+ \((.+)\)$/im";
  }

  protected function getNameServersRegExp()
  {
    return "/name servers in the listed order:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\n");
  }
}
