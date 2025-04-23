<?php

use Pdp\Domain;
use Pdp\Rules;

class Lookup
{
  public $domain;

  private $extension;

  private $extensionTop;

  public $whoisData;

  private $whoisParser;

  private $whoisUnknown;

  private $whoisError;

  public $rdapData;

  private $rdapParser;

  private $rdapUnknown;

  private $rdapError;

  public $parser;

  public function __construct($domain)
  {
    $this->parseDomain($domain);

    if (DATA_SOURCE === "whois" || DATA_SOURCE === "all") {
      $this->getWHOIS();
    }
    if (DATA_SOURCE === "rdap" || DATA_SOURCE === "all") {
      $this->getRDAP();
    }
    if (DATA_SOURCE === "all") {
      $this->merge();
    }
  }

  private function parseDomain($domain)
  {
    $parsedUrl = parse_url($domain);
    if (!empty($parsedUrl["host"])) {
      $domain = $parsedUrl["host"];
    }

    if (!empty(DEFAULT_EXTENSION) && strpos($domain, ".") === false) {
      $domain .= "." . DEFAULT_EXTENSION;
    }

    $publicSuffixList = Rules::fromPath(__DIR__ . "/data/public-suffix-list.dat");
    $domain = Domain::fromIDNA2008($domain);

    try {
      $domainName = $publicSuffixList->getPrivateDomain($domain);
      $this->domain = $domainName->registrableDomain()->toString();
      $this->extension = $domainName->suffix()->toString();
    } catch (Throwable $t) {
      try {
        $domainName = $publicSuffixList->getICANNDomain($domain);
        $this->domain = $domainName->registrableDomain()->toString();
        $this->extension = $domainName->suffix()->toString();
        $this->extensionTop = $domainName->domain()->label(0);
      } catch (Throwable $t) {
        if (
          str_starts_with($t->getMessage(), "The public suffix and the domain name are identical") &&
          count($domain->labels()) > 1
        ) {
          $this->domain = $domain->toString();
          $this->extension = $domain->label(0);
        } else {
          throw $t;
        }
      }
    }
  }

  private function getWHOIS()
  {
    try {
      $whois = new WHOIS($this->domain, $this->extension, $this->extensionTop);
      $data = $whois->getData();

      $this->whoisData = $data;

      $parser = ParserFactory::create($whois->extension, $data);
      if (DATA_SOURCE === "whois") {
        $this->parser = $parser;

        if (!empty($this->parser->domain)) {
          $this->domain = $this->parser->domain;
        }
      } else {
        $this->whoisParser = $parser;
      }
    } catch (Exception $e) {
      if (DATA_SOURCE === "whois") {
        throw $e;
      }

      if ($e->getMessage() === "No WHOIS server found for '$this->domain'") {
        $this->whoisUnknown = $e->getMessage();
      } else {
        $this->whoisError = $e->getMessage();
      }
    }
  }

  private function getRDAP()
  {
    try {
      $rdap = new RDAP($this->domain, $this->extension, $this->extensionTop);
      [$code, $data] = $rdap->getData();

      $json = json_decode($data, true);
      if ($json) {
        $prettyData = json_encode(
          $json,
          JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $this->rdapData = preg_replace("/^(  +?)\\1(?=[^ ])/m", "$1", $prettyData);
      }

      $parser = new ParserRDAP($code, $data, $json);
      if (DATA_SOURCE === "rdap") {
        $this->parser = $parser;

        if (!empty($this->parser->domain)) {
          $this->domain = $this->parser->domain;
        }
      } else {
        $this->rdapParser = $parser;
      }
    } catch (Exception $e) {
      if (DATA_SOURCE === "rdap") {
        throw $e;
      }

      if ($e->getMessage() === "No RDAP server found for '$this->domain'") {
        $this->rdapUnknown = $e->getMessage();
      } else {
        $this->rdapError = $e->getMessage();
      }
    }
  }

  private function merge()
  {
    if ($this->whoisUnknown && $this->rdapUnknown) {
      throw new RuntimeException("No WHOIS or RDAP server found for '$this->domain'");
    }

    if ($this->whoisError && $this->rdapError) {
      throw new RuntimeException("A temporary error has occurred");
    }

    if ($this->whoisParser && $this->rdapParser) {
      $this->mergeParser();
    } else if ($this->whoisParser) {
      $this->parser = $this->whoisParser;
    } else if ($this->rdapParser) {
      $this->parser = $this->rdapParser;
    } else {
      throw new RuntimeException("A temporary error has occurred");
    }
  }

  private function mergeParser()
  {
    $this->parser = $this->whoisParser;
    $this->parser->rdapData = $this->rdapParser->rdapData;

    if (!$this->rdapParser->registered) {
      return;
    }

    $properties = [
      "registered",
      "domain",
      "registrar",
      "registrarURL",
      "creationDate",
      "creationDateISO8601",
      "expirationDate",
      "expirationDateISO8601",
      "updatedDate",
      "updatedDateISO8601",
      "availableDate",
      "availableDateISO8601",
      "status",
      "nameServers",
      "age",
      "remaining",
      "gracePeriod",
      "redemptionPeriod",
      "pendingDelete",
    ];

    foreach ($properties as $property) {
      if (is_bool($this->rdapParser->$property) || $this->rdapParser->$property) {
        $this->parser->$property = $this->rdapParser->$property;
      }
    }

    foreach (["ageSeconds", "remainingSeconds"] as $property) {
      if ($this->rdapParser->$property !== null) {
        $this->parser->$property = $this->rdapParser->$property;
      }
    }

    $this->parser->unknown = $this->parser->getUnknown();
    if ($this->parser->unknown) {
      $this->parser->registered = false;
    }
  }
}
