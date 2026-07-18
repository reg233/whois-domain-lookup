<?php

declare(strict_types=1);

class RDAP
{
  public string $domain;

  public string $extension;

  /** @var array<string, string> */
  private array $servers;

  private string $server;

  private const SERVERS_IANA = __DIR__ . "/data/rdap-servers-iana.json";

  private const SERVERS_EXTRA = __DIR__ . "/data/rdap-servers-extra.json";

  public function __construct(string $domain, string $extension, ?string $extensionTop)
  {
    $this->domain = $domain;
    $this->extension = $extension;

    $this->servers = $this->getServers();

    if (!empty($extensionTop) && !array_key_exists($extension, $this->servers)) {
      $this->extension = $extensionTop;
    }

    $server = $_GET["rdap-server"] ?? "";
    if ($server) {
      $this->server = $server;
    } else {
      $this->server = $this->getServer();
    }
  }

  /** @return array<string, string> */
  private function getServers(): array
  {
    $servers = [];

    if (
      file_exists(self::SERVERS_IANA) &&
      ($json = file_get_contents(self::SERVERS_IANA)) !== false
    ) {
      $decoded = json_decode($json, true);
      if (is_array($decoded)) {
        foreach ($decoded["services"] as $service) {
          $tlds = $service[0];
          $server = $service[1][0];

          foreach ($tlds as $tld) {
            $servers[$tld] = $server;
          }
        }
      }
    }

    if (
      file_exists(self::SERVERS_EXTRA) &&
      ($json = file_get_contents(self::SERVERS_EXTRA)) !== false
    ) {
      $decoded = json_decode($json, true);
      if (is_array($decoded)) {
        $servers = array_merge($servers, $decoded);
      }
    }

    return $servers;
  }

  private function getServer(): string
  {
    if ($this->extension === "iana") {
      return "https://rdap.iana.org/";
    }

    $extension = idn_to_ascii($this->extension) ?: $this->extension;
    $server = $this->servers[$extension] ?? "";

    if (empty($server)) {
      throw new RuntimeException("No RDAP server found for '$this->domain'.");
    }

    return $server;
  }

  /** @return array{0:int, 1:string} */
  public function getData(): array
  {
    $server = rtrim($this->server, "/");
    $domain = idn_to_ascii($this->domain) ?: $this->domain;

    $ch = curl_init("$server/domain/$domain");

    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_USERAGENT => USER_AGENT,
    ]);

    $response = curl_exec($ch);
    if (is_bool($response)) {
      $error = curl_error($ch);
      throw new RuntimeException("RDAP lookup failed for '$this->domain': $error.");
    }

    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($code >= 400 && $code !== 404) {
      throw new RuntimeException("RDAP lookup failed for '$this->domain': HTTP $code.");
    }

    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    if (!is_string($contentType) || !preg_match("/^application\/(rdap\+)?json/i", $contentType)) {
      $response = "";
    }

    return [$code, $response];
  }
}
