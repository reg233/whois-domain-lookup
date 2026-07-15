<?php

declare(strict_types=1);

class WHOIS
{
  public string $domain;

  public string $extension;

  /** @var array<string, string|array{host:string, query:string}> */
  private array $servers;

  /** @var string|array{host:string, query:string} */
  private string|array $server;

  private const SERVERS_IANA = __DIR__ . "/data/whois-servers-iana.json";

  private const SERVERS_EXTRA = __DIR__ . "/data/whois-servers-extra.json";

  public function __construct(string $domain, string $extension, ?string $extensionTop)
  {
    $this->domain = $domain;
    $this->extension = $extension;

    $this->servers = $this->getServers();

    if (!empty($extensionTop) && !array_key_exists($extension, $this->servers)) {
      $this->extension = $extensionTop;
    }

    $server = $_GET["whois-server"] ?? "";
    if ($server) {
      $this->server = $server;
    } else {
      $this->server = $this->getServer();
    }
  }

  /** @return array<string, string|array{host:string, query:string}> */
  private function getServers(): array
  {
    $servers = [];

    if (
      file_exists(self::SERVERS_IANA) &&
      ($json = file_get_contents(self::SERVERS_IANA)) !== false
    ) {
      $decoded = json_decode($json, true);
      if (is_array($decoded)) {
        $servers = array_merge($servers, $decoded);
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

  /** @return string|array{host:string, query:string} */
  private function getServer(): string|array
  {
    if ($this->extension === "iana") {
      return "whois.iana.org";
    }

    $extension = idn_to_ascii($this->extension) ?: $this->extension;
    $server = $this->servers[$extension] ?? "";

    if (!$server && !WHOISWeb::isSupported($this->extension)) {
      throw new RuntimeException("No WHOIS server found for '{$this->domain}'.");
    }

    return $server;
  }

  public function getData(): string
  {
    if (empty($_GET["whois-server"]) && WHOISWeb::isSupported($this->extension)) {
      return (new WHOISWeb($this->domain, $this->extension))->getData();
    }

    $domain = idn_to_ascii($this->domain) ?: $this->domain;

    $server = $this->server;

    if (is_array($server)) {
      $host = $server["host"];
      $query = sprintf($server["query"], $domain);
    } else {
      $host = $server;
      $query = "$domain\r\n";
    }

    $socket = @stream_socket_client("tcp://$host:43", $errno, $errstr, 10);
    if ($socket === false) {
      throw new RuntimeException("WHOIS lookup failed for '{$this->domain}': $errstr.");
    }

    stream_set_timeout($socket, 10);

    if (fwrite($socket, $query) === false) {
      fclose($socket);
      throw new RuntimeException("WHOIS lookup failed for '{$this->domain}': failed to send request.");
    }

    $response = stream_get_contents($socket);
    if ($response === false) {
      fclose($socket);
      throw new RuntimeException("WHOIS lookup failed for '{$this->domain}': failed to read response.");
    }

    $metaData = stream_get_meta_data($socket);
    if ($metaData["timed_out"]) {
      fclose($socket);
      throw new RuntimeException("WHOIS lookup failed for '{$this->domain}': timed out.");
    }

    fclose($socket);

    // Skip encoding the response for "հայ" extension to avoid garbled text
    if ($this->extension !== "հայ") {
      $encoding = mb_detect_encoding($response, ["UTF-8", "ISO-8859-1"], true);
      if ($encoding && $encoding !== "UTF-8") {
        $convertedResponse = mb_convert_encoding($response, "UTF-8", $encoding);
        if ($convertedResponse !== false) {
          $response = $convertedResponse;
        }
      }
    }

    return $response;
  }
}
