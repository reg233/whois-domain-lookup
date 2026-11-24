<?php

declare(strict_types=1);

use Pdp\CannotProcessHost;
use Pdp\Domain;
use Pdp\Rules;

class Lookup
{
  public string $domain;

  public string $extension;

  private ?string $extensionTop = null;

  /** @var list<'whois'|'rdap'> */
  private array $dataSource;

  public ?string $whoisData = null;

  private ?Parser $whoisParser = null;

  private string $whoisUnknown = "";

  private string $whoisError = "";

  public ?string $rdapData = null;

  private ?ParserRDAP $rdapParser = null;

  private string $rdapUnknown = "";

  private string $rdapError = "";

  public Parser $parser;

  private string $error = "";

  /**
   * @param list<'whois'|'rdap'> $dataSource
   *
   * @throws CannotProcessHost
   * @throws Throwable
   */
  public function __construct(string $domain, array $dataSource)
  {
    $this->parseDomain($domain);
    $this->dataSource = $dataSource;

    $useWHOIS = in_array("whois", $dataSource, true);
    $useRDAP = in_array("rdap", $dataSource, true);

    if ($useWHOIS) {
      $this->getWHOIS();
    }
    if ($useRDAP) {
      $this->getRDAP();
    }
    if ($useWHOIS && $useRDAP) {
      $this->merge();
    }

    if ($this->parser->registrarIANAId || $this->parser->registrar) {
      $this->setRegistrarInfoFromICANN();
    }
  }

  /**
   * @throws CannotProcessHost
   * @throws Throwable
   */
  private function parseDomain(string $domain): void
  {
    $publicSuffixList = Rules::fromPath(__DIR__ . "/data/public-suffix-list.dat");
    $domain = Domain::fromIDNA2008($domain)->toUnicode();

    try {
      $domainName = $publicSuffixList->getPrivateDomain($domain);
      $this->domain = $domainName->registrableDomain()->toString();
      $this->extension = $domainName->suffix()->toString();
    } catch (Throwable) {
      try {
        $domainName = $publicSuffixList->getICANNDomain($domain);
        $this->domain = $domainName->registrableDomain()->toString();
        $this->extension = $domainName->suffix()->toString();
        $this->extensionTop = $domainName->domain()->label(0);
      } catch (Throwable $t) {
        if ($t->getMessage() === "The domain \"{$domain->toString()}\" can not contain a public suffix.") {
          $this->domain = $domain->toString();
          $this->extension = "iana";
        } elseif ($t->getMessage() === "The public suffix and the domain name are identical `{$domain->toString()}`.") {
          $this->domain = $domain->toString();
          $this->extension = $domain->label(0);
        } else {
          throw $t;
        }
      }
    }
  }

  private function getWHOIS(): void
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
    } catch (Throwable $t) {
      if ($this->dataSource === ["whois"]) {
        $this->parser = $this->fallbackParser();
      }

      if ($t instanceof NoServerFoundException) {
        $this->whoisUnknown = $t->getMessage();
      } else {
        $this->whoisError = $t->getMessage();
      }
    }
  }

  private function getRDAP(): void
  {
    try {
      $rdap = new RDAP($this->domain, $this->extension, $this->extensionTop);
      [$code, $data] = $rdap->getData();

      $this->rdapData = $data;

      if ($code >= 400 && $code !== 404 && $code !== 429) {
        throw new RuntimeException("RDAP lookup failed for '$this->domain': HTTP $code.");
      }

      $parser = new ParserRDAP($rdap->extension, $code, $data);
      if ($this->dataSource === ["rdap"]) {
        $this->parser = $parser;

        if (!empty($this->parser->domain)) {
          $this->domain = $this->parser->domain;
        }
      } else {
        $this->rdapParser = $parser;
      }
    } catch (Throwable $t) {
      if ($this->dataSource === ["rdap"]) {
        $this->parser = $this->fallbackParser();
      }

      if ($t instanceof NoServerFoundException) {
        $this->rdapUnknown = $t->getMessage();
      } else {
        $this->rdapError = $t->getMessage();
      }
    }
  }

  private function fallbackParser(): Parser
  {
    $parser = new Parser("");
    $parser->whoisData = $this->whoisData ?? "";
    $parser->rdapData = $this->rdapData ?? "";

    return $parser;
  }

  private function merge(): void
  {
    if ($this->whoisParser && $this->rdapParser) {
      $this->mergeParser($this->whoisParser, $this->rdapParser);
    } elseif ($this->whoisParser) {
      $this->parser = $this->whoisParser;
      $this->parser->rdapData = $this->rdapData ?? "";
    } elseif ($this->rdapParser) {
      $this->parser = $this->rdapParser;
    } else {
      $this->error = match (true) {
        $this->whoisUnknown && $this->rdapUnknown => "No WHOIS or RDAP server found for '$this->domain'.",
        $this->whoisError && $this->rdapError => "WHOIS and RDAP lookups failed for '$this->domain'.",
        $this->whoisError !== "" => $this->whoisError,
        default => $this->rdapError,
      };
      $this->parser = $this->fallbackParser();
    }
  }

  private function mergeParser(Parser $whoisParser, ParserRDAP $rdapParser): void
  {
    $this->parser = $whoisParser;
    $this->parser->rdapData = $rdapParser->rdapData;
    $this->parser->rateLimited = $whoisParser->rateLimited && $rdapParser->rateLimited;

    if ($rdapParser->rateLimited) {
      return;
    }

    if ($rdapParser->reserved) {
      $this->parser->unknown = false;
      $this->parser->reserved = true;
      $this->parser->registered = false;
      return;
    }

    if (!$rdapParser->registered || $rdapParser->unknown) {
      return;
    }

    $boolProperties = [
      "registered",
      "gracePeriod",
      "redemptionPeriod",
      "pendingDelete",
      "hold",
      "inactive",
    ];

    foreach ($boolProperties as $property) {
      $this->parser->$property = $rdapParser->$property;
    }

    $stringProperties = [
      "domain",
      "registryWebsite",
      "registryWHOISServer",
      "registryRDAPServer",
      "registrar",
      "registrarURL",
      "registrarIANAId",
      "registrarWHOISServer",
      "registrarRDAPServer",
      "creationDate",
      "expirationDate",
      "updatedDate",
      "availableDate",
      "createdAgo",
      "expiresIn",
      "updatedAgo",
      "availableIn",
    ];

    foreach ($stringProperties as $property) {
      if ($rdapParser->$property !== "") {
        $this->parser->$property = $rdapParser->$property;
      }
    }

    $nullableStringProperties = [
      "creationDateISO8601",
      "expirationDateISO8601",
      "updatedDateISO8601",
      "availableDateISO8601",
    ];

    foreach ($nullableStringProperties as $property) {
      if ($rdapParser->$property !== null && $rdapParser->$property !== "") {
        $this->parser->$property = $rdapParser->$property;
      }
    }

    if ($rdapParser->status) {
      $this->parser->status = $rdapParser->status;
    }

    if ($rdapParser->nameServers) {
      $this->parser->nameServers = $rdapParser->nameServers;
    }

    if ($rdapParser->dnssecSigned !== null) {
      $this->parser->dnssecSigned = $rdapParser->dnssecSigned;
    }

    $nullableIntProperties = [
      "createdAgoSeconds",
      "expiresInSeconds",
      "updatedAgoSeconds",
      "availableInSeconds",
    ];

    foreach ($nullableIntProperties as $property) {
      if ($rdapParser->$property !== null) {
        $this->parser->$property = $rdapParser->$property;
      }
    }

    $this->parser->unknown = $this->parser->getUnknown();
    if ($this->parser->unknown) {
      $this->parser->registered = false;
    }
  }

  private function setRegistrarInfoFromICANN(): void
  {
    $filename = __DIR__ . "/data/icann-accredited-registrars.csv";

    if (($stream = @fopen($filename, "r")) === false) {
      return;
    }

    try {
      if (fgetcsv($stream, escape: "") === false) {
        return;
      }

      $registrar = strtolower($this->parser->registrar);

      while (($row = fgetcsv($stream, escape: "")) !== false) {
        $id = trim($row[0] ?? "");
        $name = trim($row[1] ?? "");

        if (
          !($this->parser->registrarIANAId && $id === $this->parser->registrarIANAId) &&
          !($registrar && strtolower($name) === $registrar)
        ) {
          continue;
        }

        if (!$this->parser->registrar) {
          $this->parser->registrar = $name;
        }

        $this->parser->registrarIANAId = $id;

        if (!$this->parser->registrarURL) {
          $this->parser->registrarURL = trim($row[2] ?? "");
        }

        if (!$this->parser->registrarRDAPServer) {
          $this->parser->registrarRDAPServer = trim($row[3] ?? "");
        }

        return;
      }

      if ($this->parser->registrarIANAId) {
        $this->parser->registrarIANAId = "";
      }
    } finally {
      fclose($stream);
    }
  }

  public function getError(): string
  {
    if ($this->parser->rateLimited) {
      $prefix = match ($this->dataSource) {
        ["whois"] => "WHOIS lookup",
        ["rdap"] => "RDAP lookup",
        default => "WHOIS and RDAP lookups",
      };

      return "$prefix failed for '$this->domain': temporarily unavailable due to rate limiting.";
    }

    $error = match ($this->dataSource) {
      ["whois"] => $this->whoisError ?: $this->whoisUnknown,
      ["rdap"] => $this->rdapError ?: $this->rdapUnknown,
      default => $this->error,
    };

    return $error === "" ? "" : rtrim($error, ".") . ".";
  }
}
