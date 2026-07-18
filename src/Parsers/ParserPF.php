<?php

declare(strict_types=1);

class ParserPF extends Parser
{
  protected ?string $dateFormat = "d/m/Y";

  protected function getDomainRegExp(): string
  {
    return "/informations about '(.+)'/i";
  }

  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp("registrar compagnie name");
  }

  protected function getCreationDateRegExp(): string
  {
    return $this->getBaseRegExp("created \(jj\/mm\/aaaa\)");
  }

  protected function getExpirationDateRegExp(): string
  {
    return $this->getBaseRegExp("expire \(jj\/mm\/aaaa\)");
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("name server \d");
  }
}
