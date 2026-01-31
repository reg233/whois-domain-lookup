<?php
class ParserRDAP extends Parser
{
  private $json = [];

  public function __construct($extension, $code, $data)
  {
    $this->extension = $extension;
    $this->rdapData = $data;
    $this->json = json_decode($data, true);

    $this->reserved = $this->getReserved();
    if ($this->reserved) {
      return;
    }

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
    $this->getRegistrarWHOISServer();
    $this->getRegistrarRDAPServer();

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

  protected function getReserved()
  {
    // aa.af, as, bw, cm, cv, fuck.cx, ec, gn, gy.gy, hn, fuck.ht, fuck.ki, kn, lb, 233.ly, mg, mr, ms
    // fuck.nf, ng, rw, fuck.sb, so, ss, fuck.tl
    if (isset($this->json["variants"])) {
      foreach ($this->json["variants"] as $variant) {
        if (
          isset($variant["relations"]) &&
          in_array("RESTRICTED_REGISTRATION", $variant["relations"])
        ) {
          return true;
        }
      }
    }

    // The description of sr and ye extension is a string
    if (isset($this->json["description"]) && is_array($this->json["description"])) {
      foreach ($this->json["description"] as $desc) {
        $keywords = [
          // fuck.ca
          "has usage restrictions",
          // www.iq, 233.ky, xxx.my
          "is not available",
        ];
        if (preg_match("/" . implode("|", $keywords) . "/i", $desc)) {
          return true;
        }
      }
    }

    return false;
  }

  protected function getDomain()
  {
    if (!empty($this->json["ldhName"])) {
      // The ldhName of et extension ends with a dot
      $this->domain = idn_to_utf8(strtolower(rtrim($this->json["ldhName"], ".")));
    }
  }

  protected function getRegistrar()
  {
    if (empty($this->json["entities"])) {
      return;
    }

    foreach ($this->json["entities"] as $entity) {
      $roles = $entity["roles"] ?? [];

      if (
        (is_array($roles) && in_array("registrar", $roles)) ||
        (is_string($roles) && $roles === "registrar") // kg
      ) {
        if (isset($entity["vcardArray"][1])) {
          foreach ($entity["vcardArray"][1] as $vcard) {
            switch ($vcard[0]) {
              case "fn":
              case "org":
                if (!$this->registrar) {
                  $this->registrar = $vcard[3];
                }
                break;
              case "url":
                $this->registrarURL = $this->formatURL($vcard[3]);
                break;
            }
          }
        } else if (isset($entity["entities"])) {
          // as, bw, kn, mg, ml, pg, sd, td, zm
          foreach ($entity["entities"] as $subEntity) {
            if (
              isset($subEntity["roles"]) &&
              in_array("abuse", $subEntity["roles"]) &&
              isset($subEntity["vcardArray"][1])
            ) {
              foreach ($subEntity["vcardArray"][1] as $vcard) {
                switch ($vcard[0]) {
                  case "fn":
                    $this->registrar = $vcard[3];
                    break;
                }
              }

              break;
            }
          }
        } else if (!empty($entity["handle"])) {
          // ar, cr, cz, tz, ve
          $this->registrar = $entity["handle"];
        }

        if (!$this->registrarURL) {
          if (isset($entity["links"])) {
            foreach ($entity["links"] as $link) {
              if (
                isset($link["title"]) &&
                $link["title"] === "Registrar's Website" &&
                !empty($link["href"])
              ) {
                $this->registrarURL = $this->formatURL($link["href"]);
                break;
              }
            }
          } else if (!empty($entity["url"])) {
            // ch, li
            $this->registrarURL = $this->formatURL($entity["url"]);
          }
        }

        break;
      }
    }
  }

  protected function getRegistrarWHOISServer()
  {
    if (!empty($this->json["port43"])) {
      $this->registrarWHOISServer = $this->json["port43"];
    }
  }

  protected function getRegistrarRDAPServer()
  {
    if (!isset($this->json["links"])) {
      return;
    }

    $rel = $this->extension === "iana" ? "alternate" : "related";

    foreach ($this->json["links"] as $link) {
      if (isset($link["rel"]) && $link["rel"] === $rel && !empty($link["href"])) {
        $href = $link["href"];
        if ($rel === "related") {
          $this->registrarRDAPServer = explode("/domain/", $href)[0];
        } else {
          $this->registrarRDAPServer = $href;
        }
        return;
      }
    }
  }

  private function formatURL($url)
  {
    if ($url) {
      return preg_match("#^https?://#i", $url) ? $url : "http://" . $url;
    }

    return "";
  }

  protected const EXPIRATION_DATE_KEYWORDS = [
    "expiration",
    // is
    "soft expiration",
    // kg
    "record expires",
  ];

  protected function getDate()
  {
    if (empty($this->json["events"])) {
      return;
    }

    foreach ($this->json["events"] as $event) {
      if (isset($event["eventAction"]) && !empty($event["eventDate"])) {
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

  protected function getStatus($subject = null)
  {
    if (empty($this->json["status"])) {
      return;
    }

    $this->status = array_map(
      function ($item) {
        $key = str_replace(" ", "", strtolower($item));

        if (isset(self::STATUS_MAP[$key])) {
          $value = self::STATUS_MAP[$key];

          return ["text" => $value, "url" => "https://icann.org/epp#$value"];
        }

        return ["text" => $item, "url" => ""];
      },
      array_values(array_unique($this->json["status"])),
    );
  }

  protected function getNameServers($subject = null)
  {
    if (empty($this->json["nameservers"])) {
      return;
    }

    $this->nameServers = array_values(array_unique(array_map(
      fn($item) => idn_to_utf8(strtolower(explode(" ", $item["ldhName"])[0])),
      $this->json["nameservers"],
    )));
  }
}
