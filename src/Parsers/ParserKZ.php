<?php
class ParserKZ extends Parser
{
  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp("current registar"); // Typo
  }

  protected function getCreationDateISO8601()
  {
    return $this->getISO8601(str_replace(["(", ")"], "", $this->creationDate));
  }

  protected function getUpdatedDateISO8601()
  {
    return $this->getISO8601(str_replace(["(", ")"], "", $this->updatedDate));
  }

  protected function getStatusRegExp()
  {
    return "/domain status :(.+?)(?=\n\S)/is";
  }

  protected function getStatus($subject = null)
  {
    return $this->getStatusFromExplode("\n", " ");
  }

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp("(?:primary|secondary) server");
  }
}
