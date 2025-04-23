<?php
class RDAP
{
  public $domain;

  public $extension;

  private $servers;

  private $server;

  private const RDAP_SERVERS = __DIR__ . "/data/rdap-tld-servers.json";

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
      file_exists(self::RDAP_SERVERS) &&
      ($json = file_get_contents(self::RDAP_SERVERS)) !== false
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

    return $servers;
  }

  private function getServer()
  {
    $server = $this->servers[idn_to_ascii($this->extension)] ?? "";

    if (empty($server)) {
      throw new RuntimeException("No RDAP server found for '$this->domain'");
    }

    return $server;
  }

  public function getData()
  {
    $curl = curl_init("{$this->server}domain/{$this->domain}");

    curl_setopt_array($curl, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($curl);
    if ($response === false) {
      $error = curl_error($curl);
      curl_close($curl);
      throw new RuntimeException($error);
    }

    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

    curl_close($curl);

    if (!preg_match("/^application\/(rdap\+)?json/i", $contentType)) {
      $response = "";
    }

    return [$code, $response];
  }
}
