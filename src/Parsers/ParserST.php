<?php

declare(strict_types=1);

class ParserST extends Parser
{
  protected string $timezone = "Africa/Sao_Tome";

  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("url");
  }

  protected function getCreationDateRegExp(): string
  {
    return $this->getBaseRegExp("created-date");
  }

  protected function getExpirationDateRegExp(): string
  {
    return $this->getBaseRegExp("expiration-date");
  }

  protected function getUpdatedDateRegExp(): string
  {
    return $this->getBaseRegExp("updated-date");
  }

  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode(",");
  }
}
