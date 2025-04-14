<?php
class ParserAR extends Parser
{
  protected $timezone = "America/Argentina/Buenos_Aires";

  protected function getUnregisteredRegExp()
  {
    return "/no se encuentra registrado/i";
  }
}
