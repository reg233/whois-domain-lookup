<?php

declare(strict_types=1);

class ParserIL extends Parser
{
  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp("registrar info");
  }

  protected function getUpdatedDateRegExp(): string
  {
    return "/^changed:.+(\d{8}) \(changed\)$/im";
  }

  protected function getUpdatedDate(?string $subject = null): string
  {
    if (preg_match_all($this->getUpdatedDateRegExp(), $this->data, $matches)) {
      return $matches[1][array_key_last($matches[1])] ?? "";
    }

    return "";
  }
}
