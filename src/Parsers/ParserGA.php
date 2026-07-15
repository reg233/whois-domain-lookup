<?php

declare(strict_types=1);

class ParserGA extends Parser
{
  protected function getDomainRegExp(): string
  {
    return $this->getBaseRegExp("nom de domaine");
  }

  protected function getCreationDateRegExp(): string
  {
    return $this->getBaseRegExp("date de création");
  }

  protected function getExpirationDateRegExp(): string
  {
    return $this->getBaseRegExp("date d'expiration");
  }

  protected function getUpdatedDateRegExp(): string
  {
    return $this->getBaseRegExp("dernière modification");
  }

  protected function getStatusRegExp(): string
  {
    return $this->getBaseRegExp("statut");
  }

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp("serveur de noms");
  }
}
