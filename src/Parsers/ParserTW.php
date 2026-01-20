<?php
class ParserTW extends Parser
{
  protected $timezone = "Asia/Taipei";

  protected function getReservedRegExp()
  {
    // tw.tw, xxx.tw
    return "/網域名稱不合規定|reserved name/i";
  }

  protected function getCreationDateRegExp()
  {
    return "/record created on (.+) /i";
  }

  protected function getExpirationDateRegExp()
  {
    return "/record expires on (.+) /i";
  }

  protected function getStatus($subject = null)
  {
    return $this->getStatusFromExplode(",");
  }

  protected function getNameServersRegExp()
  {
    return "/domain servers in listed order:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers($subject = null)
  {
    return $this->getNameServersFromExplode("\n");
  }
}
