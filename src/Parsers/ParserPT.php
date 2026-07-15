<?php

declare(strict_types=1);

class ParserPT extends Parser
{
  protected ?string $dateFormat = "d/m/Y H:i:s";

  protected string $timezone = "Europe/Lisbon";
}
