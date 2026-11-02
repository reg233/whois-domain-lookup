<?php

declare(strict_types=1);

class ParserAR extends Parser
{
  protected string $timezone = "America/Argentina/Buenos_Aires";

  protected function getRateLimitedRegExp(): string
  {
    return "/excediste la cantidad permitida de consultas/i";
  }

  protected function getUnregisteredRegExp(): string
  {
    return "/no se encuentra registrado/i";
  }
}
