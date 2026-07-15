<?php

declare(strict_types=1);

class ParserKR extends Parser
{
  protected ?string $dateFormat = "Y. m. d.";

  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp("authorized agency");
  }
}
