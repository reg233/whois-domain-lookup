<?php
class ParserKZ extends Parser
{
  protected function getBaseRegExp($pattern)
  {
    return "/(?:$pattern) ?\.*:(.+)/i";
  }

  protected function getCreationDateISO8601()
  {
    return $this->getISO8601(preg_replace("/[()]/", "", $this->creationDate));
  }

  protected function getUpdatedDateISO8601()
  {
    return $this->getISO8601(preg_replace("/[()]/", "", $this->updatedDate));
  }

  protected function getStatusRegExp()
  {
    return "/domain status :(.+?)(?=\n\S)/is";
  }

  protected function getStatus()
  {
    if (preg_match($this->getStatusRegExp(), $this->data, $matches)) {
      return array_map(
        fn($item) => ["text" => explode(" ", $item)[0], "url" => ""],
        array_filter(array_map("trim", explode("\n", $matches[1]))),
      );
    }

    return [];
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("server");
  }
}
