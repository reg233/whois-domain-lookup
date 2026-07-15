<?php

declare(strict_types=1);

class ParserHM extends Parser
{
  protected ?string $dateFormat = "d/m/Y";

  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("referral url");
  }
}
