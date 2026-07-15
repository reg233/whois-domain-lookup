<?php

declare(strict_types=1);

class ParserRO extends Parser
{
  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("referral url");
  }
}
