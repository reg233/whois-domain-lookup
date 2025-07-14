<?php
class ParserSV extends ParserBO
{
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
