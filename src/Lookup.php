<?php

use Pdp\Domain;
use Pdp\Rules;

class Lookup
{
  public $domain;

  public $extension;

  private $extensionTop;

  private $dataSource = [];

  public $whoisData;

  private $whoisParser;

  private $whoisUnknown;

  private $whoisError;

  public $rdapData;

  private $rdapParser;

  private $rdapUnknown;

  private $rdapError;

  public $parser;

  public function __construct($domain, $dataSource)
  {
    $this->parseDomain($domain);
    $this->dataSource = $dataSource;

    if (in_array("whois", $dataSource, true)) {
      $this->getWHOIS();
    }
    if (in_array("rdap", $dataSource, true)) {
      $this->getRDAP();
    }
    if (in_array("whois", $dataSource, true) && in_array("rdap", $dataSource, true)) {
      $this->merge();
    }
  }

  private function parseDomain($domain)
  {
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
        if ($t->getMessage() === "The domain \"{$domain->toString()}\" can not contain a public suffix.") {
          $this->domain = $domain->toString();
          $this->extension = "iana";
        } else if (
          $t->getMessage() === "The public suffix and the domain name are identical `{$domain->toString()}`." &&
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
      if ($this->dataSource === ["whois"]) {
        $this->parser = $parser;

        if (!empty($this->parser->domain)) {
          $this->domain = $this->parser->domain;
        }
      } else {
        $this->whoisParser = $parser;
      }
    } catch (Exception $e) {
      if ($this->dataSource === ["whois"]) {
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

      $this->rdapData = $data;

      $parser = new ParserRDAP($this->extension, $code, $data);
      if ($this->dataSource === ["rdap"]) {
        $this->parser = $parser;

        if (!empty($this->parser->domain)) {
          $this->domain = $this->parser->domain;
        }
      } else {
        $this->rdapParser = $parser;
      }
    } catch (Exception $e) {
      if ($this->dataSource === ["rdap"]) {
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

    if (($this->whoisError && $this->rdapUnknown) || ($this->whoisUnknown && $this->rdapError)) {
      throw new RuntimeException($this->whoisError ?: $this->rdapError);
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
      "registrarWHOISServer",
      "registrarRDAPServer",
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
