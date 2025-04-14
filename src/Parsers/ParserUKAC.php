<?php

/**
 * Class ParserUKAC
 * 
 * Parses data for the "ac.uk" domain extension.
 */
class ParserUKAC extends Parser
{
  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern):\n(.+)/i";
  }

  protected function getNameServersRegExp()
  {
    return "/servers:(.+?)(?=\n\n)/is";
  }

  protected function getNameServers()
  {
    if (preg_match($this->getNameServersRegExp(), $this->data, $matches)) {
      return array_map(
        fn($item) => strtolower(explode("\t", $item)[0]),
        array_unique(array_filter(array_map("trim", explode("\n", $matches[1])))),
      );
    }

    return [];
  }
}
