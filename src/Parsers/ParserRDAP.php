<?php
class ParserRDAP extends Parser
{
  private $json = [];

  public function __construct($code, $data, $json)
  {
    $this->rdapData = $data;
    $this->json = $json;

    $this->registered = $code !== 404;
    if (!$this->registered) {
      return;
    }

    if (empty($this->rdapData)) {
      $this->unknown = true;
      return;
    }

    $this->getDomain();

    $this->getRegistrar();

    $this->getDate();

    $this->getStatus();

    $this->getNameServers();

    $this->age = $this->getDateDiffText($this->creationDateISO8601, "now");
    $this->ageSeconds = $this->getDateDiffSeconds($this->creationDateISO8601, "now");
    $this->remaining = $this->getDateDiffText("now", $this->expirationDateISO8601);
    $this->remainingSeconds = $this->getDateDiffSeconds("now", $this->expirationDateISO8601);

    $this->gracePeriod = $this->hasKeywordInStatus(self::GRACE_PERIOD_KEYWORDS);
    $this->redemptionPeriod = $this->hasKeywordInStatus(self::REDEMPTION_PERIOD_KEYWORDS);
    $this->pendingDelete = $this->hasKeywordInStatus(self::PENDING_DELETE_KEYWORDS);

    $this->unknown = $this->getUnknown();
    if ($this->unknown) {
      $this->registered = false;
    }
  }

  protected function getDomain()
  {
    if (!empty($this->json["ldhName"])) {
      $this->domain = idn_to_utf8(strtolower($this->json["ldhName"]));
    }
  }

  protected function getRegistrar()
  {
    if (empty($this->json["entities"])) {
      return;
    }

    foreach ($this->json["entities"] as $entity) {
      $roles = $entity["roles"];

      if (
        (is_string($roles) && $roles === "registrar") ||
        (is_array($roles) && in_array("registrar", $roles))
      ) {
        if (empty($entity["vcardArray"])) {
          // ar, cz
          if (!empty($entity["handle"])) {
            $this->registrar = $entity["handle"];
          }
        } else {
          foreach ($entity["vcardArray"][1] as $item) {
            switch ($item[0]) {
              case "fn":
              case "org":
                $this->registrar = $item[3];
                break;
              case "url":
                $this->registrarURL = $this->formatURL($item[3]);
                break;
            }
          }
        }

        if (empty($this->registrarURL)) {
          if (!empty($entity["links"])) {
            foreach ($entity["links"] as $link) {
              if (
                !empty($link["title"]) &&
                !empty($link["href"]) &&
                $link["title"] === "Registrar's Website"
              ) {
                $this->registrarURL = $this->formatURL($link["href"]);
                break;
              }
            }
          } else if (!empty($entity["url"])) {
            $this->registrarURL = $this->formatURL($entity["url"]);
          }
        }

        break;
      }
    }
  }

  private function formatURL($url)
  {
    if (empty($url)) {
      return "";
    }

    return preg_match("/^https?:\/\//i", $url) ? $url : "http://" . $url;
  }

  protected const EXPIRATION_DATE_KEYWORDS = [
    "expiration", // com
  ];

  protected function getDate()
  {
    if (empty($this->json["events"])) {
      return;
    }

    foreach ($this->json["events"] as $event) {
      if (!empty($event["eventDate"])) {
        $action = strtolower($event["eventAction"]);
        if ($action === "registration") {
          $this->creationDate = $event["eventDate"];
          $this->creationDateISO8601 = $this->getCreationDateISO8601();
        } else if (in_array($action, self::EXPIRATION_DATE_KEYWORDS)) {
          $this->expirationDate = $event["eventDate"];
          $this->expirationDateISO8601 = $this->getExpirationDateISO8601();
        } else if ($action === "last changed") {
          $this->updatedDate = $event["eventDate"];
          $this->updatedDateISO8601 = $this->getUpdatedDateISO8601();
        }
      }
    }
  }

  protected function getStatus()
  {
    if (empty($this->json["status"])) {
      return;
    }

    $this->status = array_map(
      function ($item) {
        $text = str_replace(" ", "", lcfirst(ucwords($item)));
        if ($text === "active") {
          $text = "ok";
        }

        return ["text" => $text, "url" => self::STATUS_MAP[$text] ?? ""];
      },
      $this->json["status"],
    );
  }

  protected function getNameServers()
  {
    if (empty($this->json["nameservers"])) {
      return;
    }

    $this->nameServers = array_unique(array_map(
      fn($item) => idn_to_utf8(strtolower(explode(" ", $item["ldhName"])[0])),
      $this->json["nameservers"]
    ));
  }
}
