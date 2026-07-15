<?php

declare(strict_types=1);

class ParserSV extends Parser
{
  protected function getReservedRegExp(): string
  {
    // sv.sv
    return "/no se puede registrar/i";
  }

  protected function getUnregisteredRegExp(): string
  {
    return "/no registrado/i";
  }

  protected function getDomainRegExp(): string
  {
    return $this->getBaseRegExp("nombre de dominio");
  }

  protected function getCreationDateRegExp(): string
  {
    return $this->getBaseRegExp("fecha registro");
  }

  protected function getExpirationDateRegExp(): string
  {
    return $this->getBaseRegExp("fecha de vencimiento");
  }

  protected function getAvailableDateRegExp(): string
  {
    return $this->getBaseRegExp("fecha de baja");
  }

  protected function getStatusRegExp(): string
  {
    return $this->getBaseRegExp("estado");
  }
}
