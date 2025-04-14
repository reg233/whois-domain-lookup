<?php
class Whois
{
  public $domain;

  public $extension;

  private $servers;

  private $server;

  private const TLD_SERVERS = "./data/tld-servers.json";

  private const SLD_SERVERS = "./data/sld-servers.json";

  public function __construct($domain, $extension, $extensionTop)
  {
    $this->domain = $domain;
    $this->extension = $extension;

    $this->servers = $this->getServers();

    if (!empty($extensionTop) && !array_key_exists($extension, $this->servers)) {
      $this->extension = $extensionTop;
    }

    $this->server = $this->getServer();
  }

  private function getServers()
  {
    $servers = [];

    if (
      file_exists(self::TLD_SERVERS) &&
      ($json = file_get_contents(self::TLD_SERVERS)) !== false
    ) {
      $decoded = json_decode($json, true);
      if (is_array($decoded)) {
        $servers = array_merge($servers, $decoded);
      }
    }

    if (
      file_exists(self::SLD_SERVERS) &&
      ($json = file_get_contents(self::SLD_SERVERS)) !== false
    ) {
      $decoded = json_decode($json, true);
      if (is_array($decoded)) {
        $servers = array_merge($servers, $decoded);
      }
    }

    return $servers;
  }

  private function getServer()
  {
    $server = $this->servers[idn_to_ascii($this->extension)] ?? "";

    if (empty($server)) {
      throw new RuntimeException("'$this->domain' contains an unknown extension");
    }

    return $server;
  }

  public function getData()
  {
    $domain = idn_to_ascii($this->domain);

    $host = $this->server;
    $query = "$domain\r\n";

    if (is_array($this->server)) {
      $host = $this->server["host"];
      $query = str_replace("{domain}", $domain, $this->server["query"]);
    }

    $socket = @stream_socket_client("tcp://$host:43", $errno, $errstr, 10);

    if (!$socket) {
      throw new RuntimeException($errstr);
    }

    stream_set_timeout($socket, 10);

    fwrite($socket, $query);

    $data = stream_get_contents($socket);

    $metaData = stream_get_meta_data($socket);
    if ($metaData["timed_out"]) {
      fclose($socket);
      throw new RuntimeException("Operation timed out");
    }

    fclose($socket);

    return $data;
  }
}
