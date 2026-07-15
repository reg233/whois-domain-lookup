<?php

declare(strict_types=1);

class ParserAR extends Parser
{
  protected string $timezone = "America/Argentina/Buenos_Aires";

  protected function getUnregisteredRegExp(): string
  {
    return "/no se encuentra registrado/i";
  }
}
