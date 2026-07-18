<?php

declare(strict_types=1);

class ParserRDAP extends Parser
{
  /** @var array<string, mixed> */
  private array $json = [];

  public function __construct(string $extension, int $code, string $data)
  {
    $this->extension = $extension;
    $this->rdapData = $data;

    $json = json_decode($data, true);
    $this->json = is_array($json) ? $json : [];

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

    $this->domain = $this->getDomain();

    $this->setLinks();

    $this->registryWHOISServer = $this->getRegistryWHOISServer();

    $this->setRegistrarInfo();

    $this->setDates();

    $this->status = $this->getStatus();
    $this->formatStatus();

    $this->nameServers = $this->getNameServers();
    $this->dnssecSigned = $this->getDNSSECSigned();

    $this->createdAgo = $this->getDateDiffText($this->creationDateISO8601, "now");
    $this->createdAgoSeconds = $this->getDateDiffSeconds($this->creationDateISO8601, "now");
    $this->expiresIn = $this->getDateDiffText("now", $this->expirationDateISO8601);
    $this->expiresInSeconds = $this->getDateDiffSeconds("now", $this->expirationDateISO8601);
    $this->updatedAgo = $this->getDateDiffText($this->updatedDateISO8601, "now");
    $this->updatedAgoSeconds = $this->getDateDiffSeconds($this->updatedDateISO8601, "now");

    $this->gracePeriod = $this->hasAnyStatusText(self::GRACE_PERIOD_STATUS_TEXTS);
    $this->redemptionPeriod = $this->hasAnyStatusText(self::REDEMPTION_PERIOD_STATUS_TEXTS);
    $this->pendingDelete = $this->hasAnyStatusText(self::PENDING_DELETE_STATUS_TEXTS);
    $this->hold = $this->hasAnyStatusText(self::HOLD_STATUS_TEXTS);
    $this->inactive = $this->hasAnyStatusText(self::INACTIVE_STATUS_TEXTS);

    $this->unknown = $this->getUnknown();
    if ($this->unknown) {
      $this->registered = false;
    }
  }

  protected function getReserved(): bool
  {
    // aa.af, xxx.as, bw.bw, email.cm, cv.cv, fuck.cx, 233.ec, xxx.gn, gy.gy, fuck.hn, fuck.ht
    // fuck.ki, ac.kn, lb.lb, 233.ly, mg.mg, xxx.mr, xxx.ms, fuck.nf, 233.ng, xxx.rw, fuck.sb, a.so
    // ss.ss, fuck.tl
    if (isset($this->json["variants"])) {
      foreach ($this->json["variants"] as $variant) {
        if (
          isset($variant["relations"]) &&
          in_array("RESTRICTED_REGISTRATION", $variant["relations"], true)
        ) {
          return true;
        }
      }
    }

    // The description of sr and ye extension is a string
    if (isset($this->json["description"]) && is_array($this->json["description"])) {
      foreach ($this->json["description"] as $desc) {
        $keywords = [
          // ca.ca, xxx.sg
          // xn--clchc0ea0b2g2a9gcd.xn--clchc0ea0b2g2a9gcd, xn--yfro4i67o.xn--yfro4i67o
          "has usage restrictions",
          // in.in, www.iq, ky.ky, xxx.my, co.pw
          "is not available",
        ];
        if (preg_match("/" . implode("|", $keywords) . "/i", $desc)) {
          return true;
        }
      }
    }

    // iana.ye
    if (
      isset($this->json["error"]) &&
      is_string($this->json["error"]) &&
      $this->json["error"] === "Domain name is reserved or restricted"
    ) {
      return true;
    }

    return false;
  }

  protected function getDomain(): string
  {
    if (empty($this->json["ldhName"])) {
      return "";
    }

    // The ldhName of et extension ends with a dot
    $domain = strtolower(rtrim($this->json["ldhName"], "."));

    return idn_to_utf8($domain) ?: $domain;
  }

  private function setLinks(): void
  {
    if (!isset($this->json["links"])) {
      return;
    }

    foreach ($this->json["links"] as $link) {
      $href = $link["href"] ?? "";
      $rel = $link["rel"] ?? "";
      $title = $link["title"] ?? "";

      if ($href && $rel) {
        if ($this->extension === "iana") {
          if ($rel === "related" && $title === "Registration URL") {
            $this->registryWebsite = $href;
          } elseif ($rel === "alternate" && $title === "RDAP Server") {
            $this->registryRDAPServer = $href;
          }
        } elseif ($rel === "related") {
          $this->registrarRDAPServer = explode("/domain/", $href)[0];
        }
      }
    }
  }

  protected function getRegistryWHOISServer(): string
  {
    return $this->json["port43"] ?? "";
  }

  private function setRegistrarInfo(): void
  {
    if (empty($this->json["entities"])) {
      return;
    }

    foreach ($this->json["entities"] as $entity) {
      $roles = $entity["roles"] ?? [];

      if (
        (is_array($roles) && in_array("registrar", $roles, true)) ||
        ($roles === "registrar") // kg
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
        } elseif (isset($entity["entities"])) {
          // as, bw, kn, mg, ml, pg, sd, td, zm
          foreach ($entity["entities"] as $subEntity) {
            if (
              isset($subEntity["roles"]) &&
              in_array("abuse", $subEntity["roles"], true) &&
              isset($subEntity["vcardArray"][1])
            ) {
              foreach ($subEntity["vcardArray"][1] as $vcard) {
                if ($vcard[0] === "fn") {
                  $this->registrar = $vcard[3];
                }
              }

              break;
            }
          }
        } elseif (!empty($entity["handle"])) {
          // ar, cr, cz, tz, ve
          $this->registrar = $entity["handle"];
        }

        if (isset($entity["publicIds"])) {
          foreach ($entity["publicIds"] as $publicId) {
            if (
              isset($publicId["type"]) &&
              $publicId["type"] === "IANA Registrar ID" &&
              !empty($publicId["identifier"])
            ) {
              $this->registrarIANAId = $publicId["identifier"];
              break;
            }
          }
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
          } elseif (!empty($entity["url"])) {
            // ch, li
            $this->registrarURL = $this->formatURL($entity["url"]);
          }
        }

        break;
      }
    }
  }

  private function formatURL(string $url): string
  {
    if ($url) {
      return preg_match("#^https?://#i", $url) ? $url : "http://$url";
    }

    return "";
  }

  protected const EXPIRATION_DATE_KEYWORDS = [
    "expiration",
    "registrar expiration",
    // is
    "soft expiration",
    // kg
    "record expires",
  ];

  private function setDates(): void
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
        } elseif (in_array($action, self::EXPIRATION_DATE_KEYWORDS, true)) {
          $this->expirationDate = $event["eventDate"];
          $this->expirationDateISO8601 = $this->getExpirationDateISO8601();
        } elseif ($action === "last changed") {
          $this->updatedDate = $event["eventDate"];
          $this->updatedDateISO8601 = $this->getUpdatedDateISO8601();
        }
      }
    }
  }

  protected function getStatus(?string $subject = null): array
  {
    if (empty($this->json["status"])) {
      return [];
    }

    return array_map(
      fn($item) => ["text" => $item, "url" => ""],
      array_values(array_unique($this->json["status"])),
    );
  }

  protected function getNameServers(?string $subject = null): array
  {
    if (empty($this->json["nameservers"])) {
      return [];
    }

    return array_values(array_unique(array_map(
      function ($item) {
        $nameServer = strtolower(explode(" ", $item["ldhName"])[0]);

        return idn_to_utf8($nameServer) ?: $nameServer;
      },
      $this->json["nameservers"],
    )));
  }

  protected function getDNSSECSigned(): ?bool
  {
    if (isset($this->json["secureDNS"]["delegationSigned"])) {
      // The delegationSigned of kg extension is a bool string
      return filter_var($this->json["secureDNS"]["delegationSigned"], FILTER_VALIDATE_BOOL);
    }

    return null;
  }
}
