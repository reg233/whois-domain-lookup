<?php
class ParserGF extends Parser
{
  protected function getUnregisteredRegExp()
  {
    return "/le nom de domaine .+ est disponible/i";
  }

  protected function getCreationDateRegExp()
  {
    return "/record created on (.+)\./i";
  }

  protected function getExpirationDateRegExp()
  {
    return "/record expires on (.+)\./i";
  }

  protected function getUpdatedDateRegExp()
  {
    return "/record last updated on (.+)\./i";
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("name server s?");
  }
}
