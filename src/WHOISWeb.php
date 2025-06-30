<?php
class WHOISWeb
{
  public $domain;

  public $extension;

  public const EXTENSIONS = [
    "bb",
    "cy",
    "hm",
    "nr",
    "ph",
    "tj",
    "to",
    "tt",
  ];

  public function __construct($domain, $extension)
  {
    $this->domain = $domain;
    $this->extension = $extension;
  }

  public function getData()
  {
    $functionName = "get" . strtoupper(str_replace(".", "", $this->extension));

    return $this->$functionName();
  }

  private function request($url, $options = [])
  {
    $curl = curl_init($url);

    curl_setopt_array(
      $curl,
      array_replace([CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10], $options),
    );

    $response = curl_exec($curl);
    if ($response === false) {
      $error = curl_error($curl);
      curl_close($curl);
      throw new RuntimeException($error);
    }

    curl_close($curl);

    return $response;
  }

  private function getBB()
  {
    $url = "https://whois.telecoms.gov.bb/status/" . $this->domain;

    $response = $this->request($url);

    if (preg_match("/<\/table>(.+<\/pre>)/is", $response, $matches)) {
      return ltrim(preg_replace("/<\/?pre>/", "", $matches[1]));
    }

    return $response;
  }

  private function getCY()
  {
    $url = "https://registry.nic.cy/api/domains/_search";

    $firstDotPos = strpos($this->domain, ".");

    $data = [
      "domainEndingName" => substr($this->domain, $firstDotPos + 1),
      "domainName" => substr($this->domain, 0, $firstDotPos),
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => json_encode($data),
      CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    ];

    $response = $this->request($url, $options);

    $json = json_decode($response, true);

    if (!$json) {
      return $response;
    } else if ($json[0]["status"] === "Διαθέσιμο") {
      return "status: available";
    }

    $url = "https://registry.nic.cy/api/whoIs/" . $json[0]["id"];

    $response = $this->request($url);

    $json = json_decode($response, true);

    $whois = "";
    if (isset($json["domainWhoIs"])) {
      $domain = $json["domainWhoIs"];
      if (array_key_exists("domainFullname", $domain)) {
        $whois .= "Domain Name: " . $domain["domainFullname"] . "\n";
      }
      if (array_key_exists("domainCreationDate", $domain)) {
        $whois .= "Creation Date: " . implode("-", $domain["domainCreationDate"] ?: []) . "\n";
      }
      if (array_key_exists("domainExpirationDate", $domain)) {
        $whois .= "Registry Expiry Date: " . implode("-", $domain["domainExpirationDate"] ?: []) . "\n";
      }
      if (array_key_exists("domainServers", $domain)) {
        $servers = $domain["domainServers"] ?: [];
        foreach ($servers as $server) {
          if (array_key_exists("name", $server)) {
            $whois .= "Name Server: " . $server["name"] . "\n";
          }
        }
      }
    }
    if (isset($json["registrantWhoIs"]["personWhoIs"])) {
      foreach ($json["registrantWhoIs"]["personWhoIs"] as $key => $value) {
        $label = $key === "personPostalCode"
          ? "Postal Code"
          : str_replace("person", "", $key);
        $whois .= "Registrant " . $label . ": " . ($value ?? "") . "\n";
      }
    }
    if (isset($json["registrantWhoIs"]["organizationWhoIs"])) {
      foreach ($json["registrantWhoIs"]["organizationWhoIs"] as $key => $value) {
        $label = str_replace("company", "", $key);
        if ($label === "Adress") {
          $label = "Address";
        } else if ($label === "PostalCode") {
          $label = "Postal Code";
        }
        $whois .= "Registrant " . $label . ": " . ($value ?? "") . "\n";
      }
    }

    return $whois;
  }

  private function getHM()
  {
    $url = "https://www.registry.hm";

    $options = [CURLOPT_HEADER => true, CURLOPT_NOBODY => true];

    $response = $this->request($url, $options);

    $sessionId = "";
    if (preg_match_all("/^Set-Cookie:\s*([^;]*)/im", $response, $matches)) {
      foreach ($matches[1] as $cookie) {
        if (str_starts_with($cookie, "PHPSESSID=")) {
          $sessionId = $cookie;
          break;
        }
      }
    }

    $url = "https://www.registry.hm/HR_whois2.php";

    $data = [
      "domain_name" => $this->domain,
      "submit" => "Check WHOIS record",
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_COOKIE => $sessionId,
    ];

    $response = $this->request($url, $options);

    libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML($response);

    $whois = "";

    $preTags = $document->getElementsByTagName("pre");
    if ($preTags->length) {
      foreach ($preTags->item(0)->childNodes as $child) {
        if ($child->nodeName === "a") {
          $class = $child->attributes->getNamedItem("class")->nodeValue;
          $cfEmail = $child->attributes->getNamedItem("data-cfemail")->nodeValue;
          if ($class === "__cf_email__" && $cfEmail) {
            $whois .= $this->decodeCFEmail($cfEmail);
          } else {
            $whois .= $document->saveHTML($child);
          }
        } else if ($child->nodeName === "br") {
          $whois .= "\n";
        } else {
          $whois .= $document->saveHTML($child);
        }
      }
    }

    return $whois;
  }

  private function getNR()
  {
    $domain = substr($this->domain, 0, -3);

    $url = "https://www.cenpac.net.nr/dns/whois.html?subdomain={$domain}&tld=nr&whois=Submit";

    $response = $this->request($url);

    libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML($response);

    $whois = "";

    $xPath = new DOMXPath($document);
    $hrs = $xPath->query("//body//hr");
    $lastHr = $hrs->item($hrs->length - 1);

    $next = $lastHr->nextSibling;
    while ($next) {
      if ($next->nodeName === "table") {
        foreach ($next->childNodes as $tr) {
          if ($tr->childNodes->length === 1) {
            $td = $tr->childNodes->item(0);
            if ($td->childNodes->item(0)->nodeName === "table") {
              $whois .= "\n";
              foreach ($td->childNodes->item(0)->childNodes as $tr) {
                if ($tr->childNodes->length === 2) {
                  $key = trim($tr->childNodes->item(0)->textContent);
                  $value = $tr->childNodes->item(1)->textContent;

                  $whois .= "$key $value\n";
                } else if ($tr->childNodes->length > 0) {
                  $text = $tr->childNodes->item(0)->childNodes->item(0)->textContent;
                  if ($text === html_entity_decode("&nbsp;")) {
                    $whois .= "\n";
                  } else {
                    $whois .= "$text\n";
                  }
                }
              }
            } else {
              $text = $td->childNodes->item(0)->textContent;
              if ($text === html_entity_decode("&nbsp;")) {
                $whois .= "\n";
              } else {
                $whois .= "$text\n";
              }
            }
          } else if ($tr->childNodes->length === 2) {
            $key = trim($tr->childNodes->item(0)->textContent);
            $value = $tr->childNodes->item(1)->textContent;

            $whois .= "$key $value\n";
          }
        }
      } else {
        $whois .= $next->textContent;
      }

      $next = $next->nextSibling;
    }

    return str_replace(" (modify)", "", ltrim($whois));
  }

  private function getPH()
  {
    $url = "https://whois.dot.ph/?search=" . $this->domain;

    $response = $this->request($url);

    libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML($response);

    $message = $document->getElementById("alert-message");
    if ($message) {
      return trim($message->nodeValue);
    }

    $preTags = $document->getElementsByTagName("pre");
    if ($preTags->length) {
      $whois = "";
      foreach ($preTags->item(0)->childNodes as $child) {
        if ($child->nodeName === "b") {
          continue;
        }

        if ($child->nodeName === "br") {
          $whois .= "\n";
        } else {
          $whois .= $document->saveHTML($child);
        }
      }

      if (preg_match("/createDate = moment\('(.+?)'\)/", $response, $matches)) {
        $whois = str_replace('<span id="create-date"></span>', $matches[1], $whois);
      }
      if (preg_match("/expiryDate = moment\('(.+?)'\)/", $response, $matches)) {
        $whois = str_replace('<span id="expiry-date"></span>', $matches[1], $whois);
      }
      if (preg_match("/updateDate = moment\('(.+?)'\)/", $response, $matches)) {
        $whois = str_replace('<span id="update-date"></span>', $matches[1], $whois);
      }

      return ltrim($whois);
    }

    return $response;
  }

  private function getTJ()
  {
    $domain = substr($this->domain, 0, -3);

    $url = "http://www.nic.tj/cgi/whois2?domain={$domain}";

    $response = $this->request($url);

    libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML($response);

    $xPath = new DOMXPath($document);
    $table = $xPath->query('//table')->item(0);

    $whois = "";
    if ($table) {
      $rows = $xPath->query(".//tr", $table);

      foreach ($rows as $row) {
        $cols = $xPath->query("td", $row);
        if ($cols->length === 1) {
          $whois .= "\n" . strtoupper($cols->item(0)->nodeValue) . "\n";
        } else if ($cols->length === 2) {
          $class = $cols->item(0)->attributes->getNamedItem("class");
          $key = $cols->item(0)->nodeValue;
          if ($class->nodeValue === "subfield") {
            $key = "  $key";
          } else {
            $key = ucwords($key, " -");
          }

          $value = "";
          foreach ($cols->item(1)->childNodes as $child) {
            $value .= $document->saveHTML($child);
          }

          $whois .= ($value === "\u{00A0}" ? "$key" : "$key: $value") . "\n";
        }
      }
    } else {
      $body = $xPath->query("//body")->item(0);
      if ($body) {
        $whois = trim($body->nodeValue);
      }
    }

    return $whois;
  }

  private function getTO()
  {
    $url = "https://www.tonic.to/whois?" . $this->domain;

    $response = $this->request($url);

    return trim(strip_tags($response));
  }

  private function getTT()
  {
    $url = "https://www.nic.tt/cgi-bin/search.pl";

    $data = [
      "name" => $this->domain,
      "Search" => "Search",
    ];

    $options = [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $data];

    $response = $this->request($url, $options);

    libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML($response);

    $xPath = new DOMXPath($document);
    $elements = $xPath->query('//*[@class="main"]');

    $whois = "";
    if ($elements->length && $elements->item(0)->childNodes->length > 1) {
      $target = $elements->item(0)->childNodes->item(1);
      if ($target->nodeName === "table") {
        $rows = $xPath->query(".//tr", $target);

        foreach ($rows as $row) {
          $cols = $xPath->query("td", $row);
          if ($cols->length === 2) {
            $key = $cols->item(0)->nodeValue;

            if ($key === "Expiration Date") {
              $value = str_replace("&nbsp", " ", $cols->item(1)->nodeValue);
            } else {
              $value = "";
              foreach ($cols->item(1)->childNodes as $child) {
                $value .= $document->saveHTML($child);
              }
            }

            $whois .= "$key: $value\n";
          }
        }
      } else {
        foreach ($elements->item(0)->childNodes as $key => $child) {
          if ($key === 0) {
            continue;
          }

          $whois .= $document->saveHTML($child);
        }
      }
    }

    return $whois;
  }

  private function decodeCFEmail($cfEmail)
  {
    $result = "";

    $key = hexdec(substr($cfEmail, 0, 2));

    for ($i = 2; $i < strlen($cfEmail); $i += 2) {
      $result .= chr(hexdec(substr($cfEmail, $i, 2)) ^ $key);
    }

    return $result;
  }
}
