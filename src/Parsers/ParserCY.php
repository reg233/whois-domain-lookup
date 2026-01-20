<?php
class ParserCY extends Parser
{
  protected function getReserved()
  {
    return str_contains($this->data, "Status: Απαγορευμένο");
  }

  protected function getUnregistered()
  {
    return str_contains($this->data, "Status: Διαθέσιμο");
  }
}
