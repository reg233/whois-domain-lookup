<?php
class ParserSV extends Parser
{
  protected function getReservedRegExp()
  {
    // sv.sv
    return "/no se puede registrar/i";
  }

  protected function getUnregisteredRegExp()
  {
    return "/no registrado/i";
  }

  protected function getDomainRegExp()
  {
    return $this->getBaseRegExp("nombre de dominio");
  }

  protected function getCreationDateRegExp()
  {
    return $this->getBaseRegExp("fecha registro");
  }

  protected function getExpirationDateRegExp()
  {
    return $this->getBaseRegExp("fecha de vencimiento");
  }

  protected function getAvailableDateRegExp()
  {
    return $this->getBaseRegExp("fecha de baja");
  }

  protected function getStatusRegExp()
  {
    return $this->getBaseRegExp("estado");
  }
}
