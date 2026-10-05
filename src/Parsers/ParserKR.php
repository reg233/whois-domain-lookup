<?php

declare(strict_types=1);

class ParserKR extends Parser
{
  protected ?string $dateFormat = "Y. m. d.";

  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp("authorized agency");
  }

  protected function getDNSSECSigned(?string $subject = null): ?bool
  {
    // Due to the redundancy of the DNSSEC, it needs to be extracted from the specified string.
    if (preg_match("/# english(.+)/is", $this->data, $matches)) {
      return parent::getDNSSECSigned($matches[1]);
    }

    return null;
  }
}
