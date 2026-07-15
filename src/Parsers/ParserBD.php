<?php

declare(strict_types=1);

class ParserBD extends Parser
{
  protected ?string $dateFormat = "d/m/Y";

  protected function getUpdatedDate(?string $subject = null): string
  {
    return "";
  }

  protected function getStatusRegExp(): string
  {
    return $this->getBaseRegExp("domain status");
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("(?:primary|secondary) dns");
  }
}
