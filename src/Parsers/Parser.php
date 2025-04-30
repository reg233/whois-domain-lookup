<?php
class Parser
{
  protected $dateFormat = null;

  protected $timezone = "UTC";

  protected $data = "";

  public $whoisData = "";

  public $rdapData = "";

  public $unknown = false;

  public $reserved = false;

  public $registered = false;

  public $domain = "";

  public $registrar = "";

  public $registrarURL = "";

  public $creationDate = "";

  public $creationDateISO8601 = null;

  public $expirationDate = "";

  public $expirationDateISO8601 = null;

  public $updatedDate = "";

  public $updatedDateISO8601 = null;

  public $availableDate = "";

  public $availableDateISO8601 = null;

  public $status = [];

  public $nameServers = [];

  public $age = "";

  public $ageSeconds = null;

  public $remaining = "";

  public $remainingSeconds = null;

  public $gracePeriod = false;

  public $redemptionPeriod = false;

  public $pendingDelete = false;

  public function __construct($data)
  {
    $this->data = $data;
    $this->whoisData = $data;

    if (empty($this->data)) {
      $this->unknown = true;
      return;
    }

    $this->reserved = $this->getReserved();
    if ($this->reserved) {
      return;
    }

    $this->registered = !$this->getUnregistered();
    if (!$this->registered) {
      return;
    }

    $this->domain = $this->getDomain();

    $this->registrar = $this->getRegistrar();
    $this->registrarURL = $this->getRegistrarURL();

    $this->creationDate = $this->getCreationDate();
    $this->creationDateISO8601 = $this->getCreationDateISO8601();

    $this->expirationDate = $this->getExpirationDate();
    $this->expirationDateISO8601 = $this->getExpirationDateISO8601();

    $this->updatedDate = $this->getUpdatedDate();
    $this->updatedDateISO8601 = $this->getUpdatedDateISO8601();

    $this->availableDate = $this->getAvailableDate();
    $this->availableDateISO8601 = $this->getAvailableDateISO8601();

    $this->status = $this->getStatus();
    $this->setStatusUrl();

    $this->nameServers = $this->getNameServers();

    $this->age = $this->getDateDiffText($this->creationDateISO8601, "now");
    $this->ageSeconds = $this->getDateDiffSeconds($this->creationDateISO8601, "now");
    $this->remaining = $this->getDateDiffText("now", $this->expirationDateISO8601);
    $this->remainingSeconds = $this->getDateDiffSeconds("now", $this->expirationDateISO8601);

    $this->gracePeriod = $this->hasKeywordInStatus(self::GRACE_PERIOD_KEYWORDS);
    $this->redemptionPeriod = $this->hasKeywordInStatus(self::REDEMPTION_PERIOD_KEYWORDS);
    $this->pendingDelete = $this->hasKeywordInStatus(self::PENDING_DELETE_KEYWORDS);

    $this->removeEmptyValues();

    $this->unknown = $this->getUnknown();
    if ($this->unknown) {
      $this->registered = false;
    }
  }

  // TODO
  private const RESERVED_KEYWORDS = [
    "reserved by the registry", // ac
    "has been reserved", // ae
    "temporarily reserved", // am
    "reserved by registry", // au
    "registration status: forbidden", // bg
    "is on a restricted list", // bi
    "prohibited string", // bj
    "object is blocked", // by
  ];

  protected function getReservedRegExp()
  {
    return "/" . implode("|", self::RESERVED_KEYWORDS) . "/i";
  }

  protected function getReserved()
  {
    return (bool)preg_match($this->getReservedRegExp(), $this->data);
  }

  private const UNREGISTERED_KEYWORDS = [
    "no match", // com
    "not found", // ac
    "not exist", // ad
    "no data", // ae
    "nothing found", // at
    "status:\tavailable", // be
    "no object found", // bf
    "status: available", // bg
    "domain is available", // co.ca
    "no entries found", // cl
    "status: free", // de
    "is available for registration", // dm
    "has not been registered", // hk
    "no such domain", // lu
    "object_not_found", // mx
    "domain unknown", // pf
    "not registered", // pk
    "no information", // pl
    "is available for purchase", // tm
    "domain name is available", // tt
    "no found", // tw
  ];

  protected function getUnregisteredRegExp()
  {
    return "/" . implode("|", self::UNREGISTERED_KEYWORDS) . "/i";
  }

  protected function getUnregistered()
  {
    return preg_match($this->getUnregisteredRegExp(), $this->data);
  }

  protected function getBaseRegExp($pattern)
  {
    return "/^[\t ]*(?:$pattern):(.+)$/im";
  }

  private const DOMAIN_KEYWORDS = [
    "domain name", // com
    "domain", // ar
    "domainname", // lu
    "domain name \(utf8\)", // укр
  ];

  protected function getDomainRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::DOMAIN_KEYWORDS));
  }

  protected function getDomain()
  {
    if (preg_match($this->getDomainRegExp(), $this->data, $matches)) {
      $domain = strtolower(explode(" ", trim($matches[1]))[0]);
      if (!empty($domain)) {
        return idn_to_utf8($domain);
      }
    }

    return "";
  }

  private const REGISTRAR_KEYWORDS = [
    "registrar", // com
    "registrar name", // ae
    "sponsoring registrar", // cn
    "sponsoring registrar organization", // id
    "current registar", // kz
    "registrar-name", // lu
    "registration service provider", // tw
    "registered by", // ac.uk
  ];

  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::REGISTRAR_KEYWORDS));
  }

  protected function getRegistrar()
  {
    if (preg_match($this->getRegistrarRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  private const REGISTRAR_URL_KEYWORDS = [
    "registrar url", // com
    "sponsoring registrar url", // id
    "registrar website", // lt
    "registrar-url", // lu
    "registration service url", // tw
  ];

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::REGISTRAR_URL_KEYWORDS));
  }

  protected function getRegistrarURL()
  {
    if (preg_match($this->getRegistrarURLRegExp(), $this->data, $matches)) {
      $url = trim($matches[1]);

      if (!empty($url) && !preg_match("/^https?:\/\//i", $url)) {
        return "http://$url";
      }

      return $url;
    }

    return "";
  }

  private const CREATION_DATE_KEYWORDS = [
    "creation date", // com
    "registered", // am
    "created", // br
    "registration time", // cn
    "domain name commencement date", // hk
    "record created", // hu
    "created on", // id
    "assigned", // il
    "registered date", // kr
    "registered on", // ro
    "registration date", // rs
    "activation", // tg
    "created date", // th
  ];

  protected function getCreationDateRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::CREATION_DATE_KEYWORDS));
  }

  protected function getCreationDate()
  {
    if (preg_match($this->getCreationDateRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  protected function getCreationDateISO8601()
  {
    return $this->getISO8601($this->creationDate);
  }

  private const EXPIRATION_DATE_KEYWORDS = [
    "registry expiry date", // com
    "expires", // am
    "expire", // ar
    "expiration date", // bn
    "expiration time", // cn
    "expiry date", // fr
    "registrar registration expiration date", // hr
    "validity", // il
    "expire date", // it
    "expires on", // jp
    "record expires on", // kg
    "renewal date", // pl
    "paid-till", // ru
    "valid until", // sk
    "expiration", // tg
    "exp date", // th
    "expiry", // tm
  ];

  protected function getExpirationDateRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::EXPIRATION_DATE_KEYWORDS));
  }

  protected function getExpirationDate()
  {
    if (preg_match($this->getExpirationDateRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  protected function getExpirationDateISO8601()
  {
    return $this->getISO8601($this->expirationDate);
  }

  protected const UPDATED_DATE_KEYWORDS = [
    "updated date", // com
    "last modified", // am
    "changed", // ar
    "modified", // ax
    "modified date", // bn
    "update date", // by
    "last-update", // fr
    "last updated on", // id
    "last update", // it
    "last updated", // jp
    "record last updated on", // kg
    "lastmod", // co.pl
    "modification date", // rs
    "updated", // sk
  ];

  protected function getUpdatedDateRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::UPDATED_DATE_KEYWORDS));
  }

  protected function getUpdatedDate()
  {
    if (preg_match($this->getUpdatedDateRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  protected function getUpdatedDateISO8601()
  {
    return $this->getISO8601($this->updatedDate);
  }

  private const AVAILABLE_DATE_KEYWORDS = [
    "available", // ax
    "date_to_release", // nu
    "free-date", // ru
  ];

  protected function getAvailableDateRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::AVAILABLE_DATE_KEYWORDS));
  }

  protected function getAvailableDate()
  {
    $regExp = $this->getAvailableDateRegExp();

    if ($regExp && preg_match($regExp, $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  protected function getAvailableDateISO8601()
  {
    return $this->getISO8601($this->availableDate);
  }

  protected function getISO8601($dateString, $format = null)
  {
    if (empty($dateString)) {
      return null;
    }

    try {
      $hasTime = preg_match("/\d{2}:\d{2}(:\d{2}(\.\d{1,6})?)?/", $dateString);

      if (empty($format)) {
        $format = $this->dateFormat;
      }

      $timezone = new DateTimeZone($hasTime ? $this->timezone : "UTC");

      $date = empty($format)
        ? new DateTime($dateString, $timezone)
        : DateTime::createFromFormat($format, $dateString, $timezone);

      $date->setTimezone(new DateTimeZone("UTC"));

      return $date->format($hasTime ? "Y-m-d\TH:i:s\Z" : "Y-m-d");
    } catch (Throwable $e) {
      return null;
    }
  }

  protected function getDateDiffText($start, $end)
  {
    if (empty($start) || empty($end)) {
      return "";
    }

    try {
      $timezone = new DateTimeZone("UTC");

      $startDate = new DateTime($start, $timezone);
      $endDate = new DateTime($end, $timezone);
      $interval = $startDate->diff($endDate);

      $parts = [];
      if ($interval->y) {
        $parts[] = "{$interval->y}Y";
      }
      if ($interval->m) {
        $parts[] = "{$interval->m}Mo";
      }
      if ($interval->d) {
        $parts[] = "{$interval->d}D";
      }

      return ($interval->invert ? "-" : "") . ($parts ? implode(" ", $parts) : "0D");
    } catch (Throwable $e) {
      return "";
    }
  }

  protected function getDateDiffSeconds($start, $end)
  {
    if (empty($start) || empty($end)) {
      return null;
    }

    try {
      $timezone = new DateTimeZone("UTC");

      $startDate = new DateTime($start, $timezone);
      $endDate = new DateTime($end, $timezone);

      return $endDate->getTimestamp() - $startDate->getTimestamp();
    } catch (Throwable $e) {
      return null;
    }
  }

  private const STATUS_KEYWORDS = [
    "domain status", // com
    "status", // ae
    "registration status", // bg
    "registry status", // укр
  ];

  protected const STATUS_MAP = [
    "addperiod" => "addPeriod",
    "autorenewperiod" => "autoRenewPeriod",
    "inactive" => "inactive",
    "ok" => "ok",
    "active" => "ok",
    "pendingcreate" => "pendingCreate",
    "pendingdelete" => "pendingDelete",
    "pendingrenew" => "pendingRenew",
    "pendingrestore" => "pendingRestore",
    "pendingtransfer" => "pendingTransfer",
    "pendingupdate" => "pendingUpdate",
    "redemptionperiod" => "redemptionPeriod",
    "renewperiod" => "renewPeriod",
    "serverdeleteprohibited" => "serverDeleteProhibited",
    "serverhold" => "serverHold",
    "serverrenewprohibited" => "serverRenewProhibited",
    "servertransferprohibited" => "serverTransferProhibited",
    "serverupdateprohibited" => "serverUpdateProhibited",
    "transferperiod" => "transferPeriod",
    "clientdeleteprohibited" => "clientDeleteProhibited",
    "clienthold" => "clientHold",
    "clientrenewprohibited" => "clientRenewProhibited",
    "clienttransferprohibited" => "clientTransferProhibited",
    "clientupdateprohibited" => "clientUpdateProhibited",
  ];

  protected function getStatusRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::STATUS_KEYWORDS));
  }

  protected function getStatus()
  {
    if (preg_match_all($this->getStatusRegExp(), $this->data, $matches)) {
      return array_map(
        function ($item) {
          if (preg_match("/^[a-z]+ https?:\/\/.+/i", $item, $matches)) {
            $parts = explode(" ", $item, 2);

            return ["text" => $parts[0], "url" => $parts[1]];
          }

          return ["text" => $item, "url" => ""];
        },
        array_unique(array_filter(array_map("trim", $matches[1]))),
      );
    }

    return [];
  }

  protected function getStatusFromExplode($separator)
  {
    if (preg_match($this->getStatusRegExp(), $this->data, $matches)) {
      return array_map(
        fn($item) => ["text" => $item, "url" => ""],
        array_filter(array_map("trim", explode($separator, $matches[1]))),
      );
    }

    return [];
  }

  private function setStatusUrl()
  {
    array_walk($this->status, function (&$item) {
      $key = str_replace(" ", "", strtolower($item["text"]));
      if (isset(self::STATUS_MAP[$key])) {
        $value = self::STATUS_MAP[$key];
        $item["text"] = $value;
        $item["url"] = "https://icann.org/epp#$value";
      }
    });
  }

  private const NAME_SERVERS_KEYWORDS = [
    "name server", // com
    "nserver", // ar
    "nameserver", // gf
    "name server \(db\)", // tg
  ];

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::NAME_SERVERS_KEYWORDS));
  }

  protected function getNameServers()
  {
    if (preg_match_all($this->getNameServersRegExp(), $this->data, $matches)) {
      return array_map(
        fn($item) => strtolower(explode(" ", $item)[0]),
        array_unique(array_filter(array_map("trim", $matches[1]))),
      );
    }

    return [];
  }

  protected function getNameServersFromExplode($separator)
  {
    if (preg_match($this->getNameServersRegExp(), $this->data, $matches)) {
      return array_map(
        fn($item) => strtolower(explode(" ", $item)[0]),
        array_unique(array_filter(array_map("trim", explode($separator, $matches[1])))),
      );
    }

    return [];
  }

  protected const GRACE_PERIOD_KEYWORDS = [
    "autoRenewPeriod", // com
  ];

  protected const REDEMPTION_PERIOD_KEYWORDS = [
    "redemptionPeriod", // com
  ];

  protected const PENDING_DELETE_KEYWORDS = [
    "pendingDelete", // com
  ];

  protected function hasKeywordInStatus($keywords)
  {
    $texts = array_map("strtolower", array_column($this->status, "text"));
    $keywords = array_map("strtolower", $keywords);

    return !empty(array_intersect($texts, $keywords));
  }

  private const EMPTY_PROPERTIES = [
    "domain",
    "registrar",
    "registrarURL",
    "creationDate",
    "expirationDate",
    "updatedDate",
    "availableDate",
    "status",
    "nameServers"
  ];

  private const EMPTY_VALUES = [
    "http://null", // ml
    "none", // nc
    "<no", // uz
    "-", // uz
    "not.defined." // uz
  ];

  protected function removeEmptyValues()
  {
    foreach (self::EMPTY_PROPERTIES as $property) {
      $value = $this->$property;

      if (empty($value)) {
        continue;
      }

      switch ($property) {
        case "status":
          $this->status = array_filter(
            $value,
            fn($item) => !in_array(strtolower($item["text"]), self::EMPTY_VALUES)
          );
          break;
        case "nameServers":
          $this->nameServers = array_diff(
            array_map("strtolower", $value),
            self::EMPTY_VALUES
          );
          break;
        default:
          if (in_array(strtolower($value), self::EMPTY_VALUES)) {
            $this->$property = "";
          }
          break;
      }
    }
  }

  public function getUnknown()
  {
    return empty($this->registrar) &&
      empty($this->creationDate) &&
      empty($this->expirationDate) &&
      empty($this->updatedDate) &&
      empty($this->availableDate) &&
      empty($this->status) &&
      empty($this->nameServers);
  }
}
