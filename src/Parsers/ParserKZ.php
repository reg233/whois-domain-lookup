<?php

declare(strict_types=1);

class ParserKZ extends Parser
{
  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp("current registar"); // Typo
  }

  protected function getCreationDateISO8601(): ?string
  {
    return $this->getISO8601(str_replace(["(", ")"], "", $this->creationDate));
  }

  protected function getUpdatedDateISO8601(): ?string
  {
    return $this->getISO8601(str_replace(["(", ")"], "", $this->updatedDate));
  }

  protected function getStatusRegExp(): string
  {
    return "/domain status :(.+?)(?=\n\S)/is";
  }

  protected function getStatus(?string $subject = null): array
  {
    return $this->getStatusFromExplode("\n", " ");
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("(?:primary|secondary) server");
  }
}
