<?php
class WHOISWeb
{
  public $domain;

  public $extension;

  public const EXTENSIONS = [
    "bb",
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

  private function getBB()
  {
    $curl = curl_init("https://whois.telecoms.gov.bb/status/{$this->domain}");

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

    curl_close($curl);

    if (preg_match("/<\/table>(.+<\/pre>)/is", $response, $matches)) {
      return ltrim(preg_replace("/<\/?pre>/", "", $matches[1]));
    }

    return $response;
  }

  private function getHM()
  {
    $curl = curl_init("https://www.registry.hm");
    curl_setopt_array($curl, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_HEADER => true,
      CURLOPT_NOBODY => true,
    ]);

    $response = curl_exec($curl);
    if ($response === false) {
      $error = curl_error($curl);
      curl_close($curl);
      throw new RuntimeException($error);
    }

    $sessionId = "";
    if (preg_match_all("/^Set-Cookie:\s*([^;]*)/im", $response, $matches)) {
      foreach ($matches[1] as $cookie) {
        if (str_starts_with($cookie, "PHPSESSID=")) {
          $sessionId = $cookie;
          break;
        }
      }
    }

    curl_close($curl);

    $curl = curl_init("https://www.registry.hm/HR_whois2.php");

    $data = [
      "domain_name" => $this->domain,
      "submit" => "Check WHOIS record",
    ];

    curl_setopt_array($curl, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_COOKIE => $sessionId,
    ]);

    $response = curl_exec($curl);
    if ($response === false) {
      $error = curl_error($curl);
      curl_close($curl);
      throw new RuntimeException($error);
    }

    curl_close($curl);

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

    $curl = curl_init("https://www.cenpac.net.nr/dns/whois.html?subdomain={$domain}&tld=nr&whois=Submit");

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

    curl_close($curl);

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
    $curl = curl_init("https://whois.dot.ph/?search={$this->domain}");

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

    curl_close($curl);

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

    $curl = curl_init("http://www.nic.tj/cgi/whois2?domain={$domain}");

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

    curl_close($curl);

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
    $curl = curl_init("https://www.tonic.to/whois?{$this->domain}");

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

    curl_close($curl);

    return trim(strip_tags($response));
  }

  private function getTT()
  {
    $curl = curl_init("https://www.nic.tt/cgi-bin/search.pl");

    $data = [
      "name" => $this->domain,
      "Search" => "Search",
    ];

    curl_setopt_array($curl, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
    ]);

    $response = curl_exec($curl);
    if ($response === false) {
      $error = curl_error($curl);
      curl_close($curl);
      throw new RuntimeException($error);
    }

    curl_close($curl);

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
