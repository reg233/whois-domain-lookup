<?php

declare(strict_types=1);

class ParserGF extends Parser
{
  protected function getUnregisteredRegExp(): string
  {
    return "/le nom de domaine .+ est disponible/i";
  }

  protected function getCreationDateRegExp(): string
  {
    return "/record created on (.+)\./i";
  }

  protected function getExpirationDateRegExp(): string
  {
    return "/record expires on (.+)\./i";
  }

  protected function getUpdatedDateRegExp(): string
  {
    return "/record last updated on (.+)\./i";
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("name server s?");
  }
}
