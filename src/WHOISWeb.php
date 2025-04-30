<?php
class WHOISWeb
{
  public $domain;

  public $extension;

  public const EXTENSIONS = [
    "bb",
    "ph",
    "tj",
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
}
