<?php
class ParserLU extends Parser
{
  protected function getStatusRegExp()
  {
    return $this->getBaseRegExp("domaintype");
  }

  protected function getStatus()
  {
    if (preg_match($this->getStatusRegExp(), $this->data, $matches)) {
      if (preg_match("/^(\w+)(?: \((.+)\))?$/", trim($matches[1]), $matches)) {
        $result = [
          ["text" => $matches[1], "url" => ""],
        ];

        if (!empty($matches[2])) {
          $result[] = ["text" => $matches[2], "url" => ""];
        }

        return $result;
      }
    }

    return [];
  }
}
