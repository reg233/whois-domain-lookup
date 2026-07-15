<?php

declare(strict_types=1);

class ParserTW extends Parser
{
  protected string $timezone = "Asia/Taipei";

  protected function getReservedRegExp(): string
  {
    // tw.tw
    // xxx.tw, xxx.台湾, xxx.台灣
    return "/網域名稱不合規定|reserved name/i";
  }

  protected function getDomainRegExp(): string
  {
    return $this->getBaseRegExp("domain name|註冊原型域名");
  }

  protected function getCreationDateRegExp(): string
  {
    return "/record created on (.+) /i";
  }

  protected function getExpirationDateRegExp(): string
  {
    return "/record expires on (.+) /i";
  }

  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode(",");
  }

  protected function getNameServersRegExp(): string
  {
    return "/domain servers in listed order:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers(?string $subject = null): array
  {
    return $this->getNameServersFromExplode("\n");
  }
}
