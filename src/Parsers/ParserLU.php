<?php

declare(strict_types=1);

class ParserLU extends Parser
{
  protected function getReservedRegExp(): string
  {
    // lu.lu
    return "/domaintype: {5}reserved/i";
  }

  protected function getStatusRegExp(): string
  {
    return $this->getBaseRegExp("domaintype");
  }

  protected function getStatus(?string $subject = null): array
  {
    $result = [];

    if (preg_match($this->getStatusRegExp(), $this->data, $matches)) {
      if (preg_match("/^(.+?)(?: \((.+)\))?$/", trim($matches[1]), $matches)) {
        $result[] = ["text" => trim($matches[1]), "url" => ""];

        if (isset($matches[2])) {
          $result[] = ["text" => trim($matches[2]), "url" => ""];
        }
      }
    }

    return $result;
  }
}
