<?php
class ParserGA extends Parser
{
  protected function getDomainRegExp()
  {
    return $this->getBaseRegExp("nom de domaine");
  }

  protected function getCreationDateRegExp()
  {
    return $this->getBaseRegExp("date de création");
  }

  protected function getUpdatedDateRegExp()
  {
    return $this->getBaseRegExp("dernière modification");
  }

  protected function getExpirationDateRegExp()
  {
    return $this->getBaseRegExp("date d'expiration");
  }

  protected function getStatusRegExp()
  {
    return $this->getBaseRegExp("statut");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("serveur de noms");
  }
}
