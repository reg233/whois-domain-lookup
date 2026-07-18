<?php

declare(strict_types=1);

require_once __DIR__ . "/utils.php";

class WHOISWeb
{
  public string $domain;

  public string $extension;

  /** @var array{0:string, 1:string} */
  private array $domainParts;

  /** @var array<string, list<string>> */
  private static array $extensionToFunctionSuffix = [
    "ao" => ["ao"],
    "az" => ["az"],
    "ba" => ["ba"],
    "bb" => ["bb"],
    "bo" => ["bo"],
    "bt" => ["bt"],
    "cu" => ["cu"],
    "cy" => ["cy"],
    "dj" => ["dj"],
    "dz" => ["dz", "الجزائر"],
    "gf" => ["gf", "mq"],
    "gm" => ["gm"],
    "gq" => ["gq"],
    "gr" => ["gr", "ελ"],
    "gt" => ["gt"],
    "gw" => ["gw"],
    "hm" => ["hm"],
    "hu" => ["hu"],
    "jo" => ["jo", "الاردن"],
    "lk" => ["lk"],
    "mt" => ["mt"],
    "ni" => ["ni"],
    "np" => ["np"],
    "nr" => ["nr"],
    "pa" => ["pa"],
    "ph" => ["ph"],
    "py" => ["py"],
    "sv" => ["sv"],
    "tj" => ["tj"],
    "tt" => ["tt"],
    "vn" => ["vn"],
  ];

  public static function isSupported(string $extension): bool
  {
    foreach (self::$extensionToFunctionSuffix as $extensions) {
      if (in_array($extension, $extensions, true)) {
        return true;
      }
    }

    return false;
  }

  public function __construct(string $domain, string $extension)
  {
    $this->domain = $domain;
    $this->extension = $extension;

    $parts = explode(".", $domain, 2);
    $this->domainParts = [$parts[0], $parts[1] ?? ""];

    libxml_use_internal_errors(true);
  }

  public function getData(): string
  {
    foreach (self::$extensionToFunctionSuffix as $functionSuffix => $extensions) {
      if (in_array(strtolower($this->extension), $extensions, true)) {
        $functionName = "get" . strtoupper($functionSuffix);
        return $this->$functionName();
      }
    }

    return "";
  }

  /** @param array<int, mixed> $options */
  private function request(string $url, array $options = []): string
  {
    return $this->requestWithInfo($url, $options)["body"];
  }

  /**
   * @param array<int, mixed> $options
   *
   * @return array{code:int, headers:string, body:string}
   */
  private function requestWithInfo(string $url, array $options = []): array
  {
    $ch = curl_init($url);

    $defaultOptions = [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_USERAGENT => USER_AGENT,
    ];

    curl_setopt_array($ch, array_replace($defaultOptions, $options));

    $response = curl_exec($ch);
    if (is_bool($response)) {
      $error = curl_error($ch);
      throw new RuntimeException("WHOISWeb lookup failed for '$this->domain': $error.");
    }

    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($code >= 400 && $code !== 404) {
      throw new RuntimeException("WHOISWeb lookup failed for '$this->domain': HTTP $code.");
    }

    $headers = "";
    $body = $response;

    if ($options[CURLOPT_HEADER] ?? false) {
      $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
      $headers = substr($response, 0, $headerSize);
      $body = substr($response, $headerSize);
    }
    if ($options[CURLOPT_NOBODY] ?? false) {
      $body = "";
    }

    return [
      "code" => $code,
      "headers" => $headers,
      "body" => $body,
    ];
  }

  private function getCookiesFromHeaders(string $headers): string
  {
    preg_match_all("/^Set-Cookie: *([^\r\n ;]+)/im", $headers, $matches);

    return implode("; ", $matches[1]);
  }

  private function getAO(): string
  {
    return "Please visit https://www.dns.ao/ao/whois/";
  }

  private function getAZ(): string
  {
    return "Please visit https://whois.az";
  }

  private function getBA(): string
  {
    return "Please visit https://nic.ba/?culture=en";
  }

  private function getBB(): string
  {
    $url = "https://whois.telecoms.gov.bb/status/" . $this->domain;

    $html = $this->request($url);

    $html = str_replace(["<<<", ">>>"], ["&lt;&lt;&lt;", "&gt;&gt;&gt;"], $html);

    $document = new DOMDocument();
    $document->loadHTML('<?xml encoding="UTF-8"?>' . $html);

    $whois = "";

    $table = $document->getElementsByTagName("table")->item(0);
    if ($table) {
      $next = $table->nextSibling;
      while ($next) {
        if ($next->nodeName === "p") {
          break;
        }

        $text = trim($next->textContent);
        if ($text) {
          $whois .= "$text\n\n";
        }

        $next = $next->nextSibling;
      }
    }

    return trim($whois);
  }

  private function getBO(): string
  {
    $url = "https://nic.bo/whois.php";

    $data = [
      "dominio" => $this->domainParts[0],
      "subdominio" => "." . $this->domainParts[1],
      "enviar" => "",
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_COOKIE => "app_language=en",
    ];

    $html = $this->request($url, $options);

    $document = new DOMDocument();
    $document->loadHTML($html);

    $xPath = new DOMXPath($document);

    $error = $xPath->query('//div[@class="texto_error"]')->item(0)?->textContent;
    if ($error && trim($error)) {
      return trim($error);
    }

    preg_match('/window\.self\.location="(.+)"/i', $html, $matches);

    if (empty($matches[1])) {
      return "";
    }

    $url = "https://nic.bo/" . $matches[1];

    $options = [CURLOPT_COOKIE => "app_language=en"];

    $html = $this->request($url, $options);

    $document->loadHTML(str_replace(" :&nbsp;&nbsp;", "", $html));

    $whois = "";

    $h4 = $document->getElementById("whois")?->getElementsByTagName("h4")->item(0);
    if ($h4) {
      $whois .= trim($h4->textContent) . "\n";
    }

    $trs = $document->getElementsByTagName("tr");
    foreach ($trs as $tr) {
      $tds = $tr->getElementsByTagName("td");
      if ($tds->length === 1) {
        $whois .= strtoupper(trim($tds->item(0)->textContent)) . "\n";
      } elseif ($tds->length === 2) {
        $key = trim($tds->item(0)->textContent);
        $value = trim($tds->item(1)->textContent);

        $whois .= "$key: $value\n";
      }
    }

    return $whois;
  }

  private function getBT(): string
  {
    $params = [
      "query" => $this->domainParts[0],
      "ext" => "." . $this->domainParts[1],
    ];

    $url = "https://www.nic.bt/search?" . http_build_query($params);

    $html = $this->request($url);

    $document = new DOMDocument();
    $document->loadHTML($html);

    $whois = "";

    $table = $document->getElementsByTagName("table")->item(0);
    if ($table) {
      $whois .= trim($table->textContent);
    } else {
      $xPath = new DOMXPath($document);
      $cardBodies = $xPath->query('//div[@class="card-body"]/div[@class="card-body"]');

      foreach ($cardBodies as $cardBody) {
        foreach ($cardBody->childNodes as $child) {
          $text = trim($child->textContent);

          switch ($child->nodeName) {
            case "h5":
              $whois .= str_replace(" :", "", $text) . "\n";
              break;
            case "p":
              $whois .= str_replace(" :", ":", $text) . "\n";
              break;
          }
        }

        $whois .= "\n";
      }
    }

    return $whois;
  }

  private function getCU(): string
  {
    $url = "https://www.nic.cu/dom_search.php";

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => ["domsrch" => $this->domain],
    ];

    $html = $this->request($url, $options);

    $document = new DOMDocument();
    $document->loadHTML('<?xml encoding="UTF-8"?>' . $html);

    $xPath = new DOMXPath($document);

    $message = $xPath->query('//td[@class="commontextgray" and @height="5"]')->item(0);
    if ($message) {
      return trim($message->textContent);
    }

    $whois = "";

    foreach ($xPath->query('//table[@id="whitetbl"]') as $table) {
      foreach ($xPath->query("./tr", $table) as $tr) {
        $tds = $xPath->query("./td", $tr);
        if ($tds->length === 3) {
          $childTable = $xPath->query("./table", $tds->item(1))->item(0);
          if ($childTable) {
            $childTds = $xPath->query(".//td", $childTable);
            if ($childTds->length === 2) {
              $key = trim($childTds->item(0)->textContent);
              $value = trim($childTds->item(1)->textContent);

              $whois .= "$key $value\n";
            }
          } else {
            $whois .= trim($tds->item(1)->textContent) . "\n";
          }
        }
      }

      $whois .= "\n";
    }

    return $whois;
  }

  private function getCY(): string
  {
    return "Please visit https://registry.nic.cy/cy-ui/home";
  }

  private function getDJ(): string
  {
    return "Please visit https://dot.dj";
  }

  private function getDZ(): string
  {
    $segment = $this->extension === "dz" ? "/" : "/arabic/";
    $url = "https://api.nic.dz/v1" . $segment . "domains/" . $this->domain;

    $options = [CURLOPT_SSL_VERIFYPEER => false];

    $jsonText = $this->request($url, $options);

    $json = json_decode($jsonText, true);

    if (isset($json["title"])) {
      return $json["title"];
    }

    $whois = "Domain Name: " . ($json["domainName"] ?? "") . "\n";
    $whois .= "Registrar: " . ($json["registrar"] ?? "") . "\n";
    $whois .= "Creation Date: " . ($json["creationDate"] ?? "") . "\n";
    $whois .= "Registrant Organization: " . ($json["orgName"] ?? "") . "\n";
    $whois .= "Registrant Address: " . ($json["addressOrg"] ?? "") . "\n";
    $whois .= "Admin Name: " . ($json["contactAdm"] ?? "") . "\n";
    $whois .= "Admin Organization: " . ($json["orgNameAdm"] ?? "") . "\n";
    $whois .= "Admin Address: " . ($json["addressAdm"] ?? "") . "\n";
    $whois .= "Admin Phone: " . ($json["phoneAdm"] ?? "") . "\n";
    $whois .= "Admin Fax: " . ($json["faxAdm"] ?? "") . "\n";
    $whois .= "Admin Email: " . ($json["emailAdm"] ?? "") . "\n";
    $whois .= "Tech Name: " . ($json["contactTech"] ?? "") . "\n";
    $whois .= "Tech Organization: " . ($json["orgNameTech"] ?? "") . "\n";
    $whois .= "Tech Address: " . ($json["addressTech"] ?? "") . "\n";
    $whois .= "Tech Phone: " . ($json["phoneTech"] ?? "") . "\n";
    $whois .= "Tech Fax: " . ($json["faxTech"] ?? "") . "\n";
    $whois .= "Tech Email: " . ($json["emailTech"] ?? "") . "\n";

    return $whois;
  }

  private function getGF(): string
  {
    $url = "https://www.dom-enic.com/whois.html";

    $data = [
      "SMq5BXJw" => $this->domainParts[0],
      "UQWhRrMF" => "." . $this->domainParts[1],
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
    ];

    $html = $this->request($url, $options);

    $document = new DOMDocument();
    $document->loadHTML($html);

    $xPath = new DOMXPath($document);

    $message = $xPath->query('//div[@class="texte1"]')->item(0)?->textContent;
    if ($message && trim($message)) {
      return trim($message);
    }

    $whois = "";

    $blockquotes = $document->getElementsByTagName("blockquote");
    foreach ($blockquotes as $i => $blockquote) {
      foreach ($blockquote->childNodes as $child) {
        switch ($child->nodeName) {
          case "br":
            $whois .= "\n";
            break;
          case "#text":
          case "u":
            $whois .= str_replace("\r\n", " ", trim($child->textContent));
            break;
          default:
            break;
        }
      }

      if ($i < $blockquotes->length - 1) {
        $whois .= "\n\n";
      }
    }

    return pregReplace("/ {2,}/", " ", $whois);
  }

  private function getGM(): string
  {
    $url = "https://www.nic.gm/NIC2/scripts/checkdom.aspx?dname=" . $this->domainParts[0];

    $options = [
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_HEADER => true,
      CURLOPT_NOBODY => true,
    ];

    ["headers" => $headers] = $this->requestWithInfo($url, $options);

    $whois = "";

    if (str_contains($headers, "/NIC2/whois-available.html")) {
      $whois .= "No match for \"$this->domain\".\n";
    } elseif (
      str_contains($headers, "/NIC2/whois-reserved.html") ||
      str_contains($headers, "/NIC2/whois-numbers.html")
    ) {
      $whois .= "This name is reserved by the registry.\n";
    } elseif (str_contains($headers, "/NIC2/whois-details.html")) {
      $url = "https://www.nic.gm/NIC2/REG/login.aspx?whois=" . $this->domainParts[0];

      $body = $this->request($url);

      if ($body) {
        $array = explode(";", $body);

        $whois .= "Domain Name: $this->domain\n";
        $whois .= "Registrar: $array[2]\n";
        $whois .= "Creation Date: $array[11]\n";
        $whois .= "Registrant Name: $array[1]\n";
        $whois .= "Admin Name: $array[3]\n";
        $whois .= "Admin Organization: $array[4]\n";
        $whois .= "Tech Name: $array[5]\n";
        $whois .= "Tech Organization: $array[6]\n";
        $whois .= "Name Server: $array[7]\n";
        $whois .= "Name Server: $array[8]\n";
        $whois .= "Name Server: $array[9]\n";
        $whois .= "Name Server: $array[10]\n";
      }
    }

    if ($whois) {
      $url = "https://www.nic.gm/NIC2/motd.txt";

      $body = $this->request($url);

      if ($body) {
        $whois .= ">>> Last update of whois database: " . trim($body) . " <<<";
      }
    }

    return $whois;
  }

  private function getGQ(): string
  {
    return "Please visit http://www.dominio.gq/en/whois.html";
  }

  private function getGR(): string
  {
    $url = "https://grweb.ics.forth.gr/public/whois?lang=en";

    $options = [CURLOPT_HEADER => true];

    ["headers" => $headers, "body" => $html] = $this->requestWithInfo($url, $options);

    $document = new DOMDocument();
    $document->loadHTML($html);

    $xPath = new DOMXPath($document);

    $csrf = $xPath->query("//input[@name='_csrf']")->item(0)?->attributes?->getNamedItem("value")?->value;

    if (!$csrf) {
      return "";
    }

    $url = "https://grweb.ics.forth.gr/public/whois/query";

    $data = [
      "_csrf" => $csrf,
      "domain" => $this->domain,
      "Submit" => "",
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => http_build_query($data),
      CURLOPT_COOKIE => $this->getCookiesFromHeaders($headers),
    ];

    $html = $this->request($url, $options);

    $document->loadHTML($html);

    $xPath = new DOMXPath($document);

    $invalid = $xPath->query('//span[@class="invalid-feedback"]')->item(0);
    if ($invalid) {
      return trim($invalid->textContent);
    }

    $whois = "";

    $alert = $xPath->query('//div[@role="alert"]')->item(0);
    if ($alert) {
      $rows = $xPath->query('.//div[@class="row"]', $alert);
      foreach ($rows as $row) {
        $whois .= trim($row->textContent) . "\n";
      }

      $whois .= "\n";
    }

    $cards = $xPath->query('//div[@class="card"]');
    foreach ($cards as $card) {
      $heading = $xPath->query('.//div[contains(@class, "card-heading")]', $card)->item(0);
      if ($heading) {
        $whois .= trim($heading->textContent) . "\n";
      }

      $rows = $xPath->query('.//li/div[@class="row"]', $card);
      foreach ($rows as $row) {
        $children = $xPath->query("./div", $row);
        if ($children->length === 2) {
          $key = trim($children->item(0)->textContent, " :\n");

          $nestedRows = $xPath->query('./div[@class="row"]', $children->item(1));
          if ($nestedRows->length) {
            foreach ($nestedRows as $nestedRow) {
              $value = trim($nestedRow->textContent);

              $whois .= "$key: $value\n";
            }
          } else {
            $value = trim($children->item(1)->textContent);

            $whois .= "$key: $value\n";
          }
        }
      }

      $accordionHeader = $xPath->query('.//h2[@class="accordion-header"]', $card)->item(0);
      if ($accordionHeader) {
        $whois .= trim($accordionHeader->textContent) . ":\n";
      }

      $accordionBody = $xPath->query('.//div[contains(@class, "accordion-body")]', $card)->item(0);
      if ($accordionBody) {
        $ths = $xPath->query("./div/text()", $accordionBody);
        foreach ($ths as $th) {
          $whois .= trim($th->textContent) . "  ";
        }

        $whois .= "\n";

        $lis = $xPath->query('.//li[@class="list-group-item"]', $accordionBody);
        foreach ($lis as $li) {
          $spans = $xPath->query('.//span[not(@class)]', $li);
          foreach ($spans as $span) {
            $whois .= trim($span->textContent) . "  ";
          }

          $whois .= "\n";
        }
      }

      $whois .= "\n";
    }

    return $whois;
  }

  private function getGT(): string
  {
    $url = "https://www.gt/sitio/whois.php?dn=" . $this->domain . "&lang=en";

    $html = $this->request($url);

    $document = new DOMDocument();
    $document->loadHTML(str_replace("&nbsp;", " ", $html));

    $xPath = new DOMXPath($document);

    $message = $xPath->query('//div[@class="caja caja-message"]')->item(0);
    if ($message) {
      return trim(pregReplace("/ {2,}/", "", $message->textContent));
    }

    $whois = "";

    $whoisNodeList = $xPath->query('//div[@class="caja caja-whois"]');
    if ($whoisNodeList->length === 2) {
      foreach ($whoisNodeList->item(0)->childNodes as $child) {
        if ($child->nodeName === "div") {
          $class = $child->attributes?->getNamedItem("class")?->value;
          if ($class === "alert alert-success") {
            $h3 = $xPath->query(".//h3", $child)->item(0);
            if ($h3) {
              $domainName = $h3->childNodes->item(0);
              if ($domainName) {
                $whois .= "Domain Name: " . trim($domainName->textContent, " \n.") . "\n";
              }

              $domainStatus = $h3->childNodes->item(1);
              if ($domainStatus) {
                $whois .= "Domain Status: " . trim($domainStatus->textContent) . "\n";
              }
            }
          } elseif ($class === "alert alert-info") {
            $whois .= "\n" . trim($child->textContent) . ":\n";
          } elseif ($class === "form-stack") {
            $expiration = $xPath->query(".//strong", $child)->item(0);
            if ($expiration) {
              $whois .= trim(pregReplace(["/\n/", "/ +/"], ["", " "], $expiration->textContent)) . "\n";
            } else {
              foreach ($xPath->query('.//div[@class="form-field"]', $child) as $field) {
                $whois .= "  " . trim(pregReplace(["/\n/", "/ +/"], ["", " "], $field->textContent)) . "\n";
              }
            }
          } elseif ($class === "form-field") {
            foreach ($xPath->query(".//li", $child) as $nameServer) {
              $whois .= "  " . trim(pregReplace(["/\n/", "/ +/"], ["", " "], $nameServer->textContent), " \n.") . "\n";
            }
          }
        }
      }

      foreach ($whoisNodeList->item(1)->childNodes as $child) {
        if ($child->nodeName === "div") {
          $h4 = $xPath->query(".//h4", $child)->item(0);
          if ($h4) {
            $whois .= "\n" . trim($h4->textContent) . ":\n";
          }

          $fields = $xPath->query('.//div[@class="form-field"]', $child);
          foreach ($fields as $field) {
            $whois .= "  " . trim(pregReplace(["/\n/", "/ +/"], ["", " "], $field->textContent)) . "\n";
          }
        }
      }
    }

    return $whois;
  }

  private function getGW(): string
  {
    $url = "https://registar.nic.gw/en/whois/" . $this->domain . "/";

    ["code" => $code, "body" => $html] = $this->requestWithInfo($url);

    if ($code === 404) {
      return "Domain not found";
    }

    $document = new DOMDocument();
    $document->loadHTML($html);

    $whois = "";

    $domainName = $document->getElementsByTagName("h2")->item(0);
    if ($domainName) {
      $whois .= "Domain Name: " . $domainName->textContent . "\n";
    }

    $fieldsets = $document->getElementsByTagName("fieldset");
    for ($i = 0; $i < $fieldsets->length; $i++) {
      $fieldset = $fieldsets->item($i);
      for ($j = 1; $j < $fieldset->childNodes->length; $j++) {
        $prevNodeName = $fieldset->childNodes->item($j - 1)->nodeName;
        $nodeName = $fieldset->childNodes->item($j)->nodeName;
        $prevTextContent = trim($fieldset->childNodes->item($j - 1)->textContent);
        $textContent = trim($fieldset->childNodes->item($j)->textContent);

        if ($nodeName === "span") {
          $whois .= "\n$textContent\n\n";
        } elseif (
          $nodeName === "#text" &&
          $prevNodeName === "label" &&
          $prevTextContent !== "E-mail:"
        ) {
          $whois .= "$prevTextContent $textContent\n";
        } elseif ($nodeName === "a") {
          $whois .= "E-mail: $textContent\n";
        }
      }
    }

    return $whois;
  }

  private function getHM(): string
  {
    $url = "https://www.registry.hm";

    $options = [CURLOPT_HEADER => true, CURLOPT_NOBODY => true];

    ["headers" => $headers] = $this->requestWithInfo($url, $options);

    $url = "https://www.registry.hm/HR_whois2.php";

    $data = [
      "domain_name" => $this->domain,
      "submit" => "Check WHOIS record",
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_COOKIE => $this->getCookiesFromHeaders($headers),
    ];

    $html = $this->request($url, $options);

    $document = new DOMDocument();
    $document->loadHTML($html);

    $whois = "";

    $pre = $document->getElementsByTagName("pre")->item(0);
    if ($pre) {
      foreach ($pre->childNodes as $child) {
        if ($child->nodeName === "a") {
          $class = $child->attributes?->getNamedItem("class")?->value;
          $cfEmail = $child->attributes?->getNamedItem("data-cfemail")?->value;
          if ($class === "__cf_email__" && $cfEmail) {
            $whois .= $this->decodeCFEmail($cfEmail);
          } else {
            $whois .= $child->textContent;
          }
        } elseif ($child->nodeName === "br") {
          $whois .= "\n";
        } else {
          $whois .= $child->textContent;
        }
      }
    }

    return $whois;
  }

  private function decodeCFEmail(string $cfEmail): string
  {
    $result = "";

    $key = hexdec(substr($cfEmail, 0, 2));

    for ($i = 2; $i < strlen($cfEmail); $i += 2) {
      $result .= chr(hexdec(substr($cfEmail, $i, 2)) ^ $key);
    }

    return $result;
  }

  private function getHU(): string
  {
    $url = "https://info.domain.hu/webwhois/en/domain/" . $this->domain;

    $options = [CURLOPT_POST => true, CURLOPT_POSTFIELDS => []];

    $html = $this->request($url, $options);

    $document = new DOMDocument();
    $document->loadHTML($html);

    $xPath = new DOMXPath($document);

    $error = $xPath->query('//p[@class="error"]')->item(0);
    if ($error && trim($error->textContent)) {
      $textContent = trim($error->textContent);

      // Conflict with co.ms
      if ($textContent === "Reserved domain") {
        return "Reserved domain name";
      }

      return $textContent;
    }

    $whois = "";

    $trs = $document->getElementsByTagName("tr");
    foreach ($trs as $tr) {
      $tds = $tr->getElementsByTagName("td");
      if ($tds->length === 2) {
        $key = trim($tds->item(0)->textContent);
        $value = trim($tds->item(1)->textContent);

        $whois .= "$key $value\n";
      }
    }

    return $whois;
  }

  private function getJO(): string
  {
    $url = "https://dns.jo/FirstPageen.aspx";

    $options = [CURLOPT_HEADER => true];

    ["code" => $code, "headers" => $headers, "body" => $html] = $this->requestWithInfo($url, $options);

    if ($code !== 200) {
      return "";
    }

    $cookies = $this->getCookiesFromHeaders($headers);

    $document = new DOMDocument();
    $document->loadHTML($html);

    $xPath = new DOMXPath($document);

    $expression = "//select[@id='ddl']/option[normalize-space(text())='." . $this->domainParts[1] . "']";
    $ddl = $xPath->query($expression)->item(0)?->attributes?->getNamedItem("value")?->value;

    $viewState = $document->getElementById("__VIEWSTATE")?->attributes->getNamedItem("value")?->value;
    $viewStateGenerator = $document->getElementById("__VIEWSTATEGENERATOR")?->attributes->getNamedItem("value")?->value;
    $viewStateEncrypted = $document->getElementById("__VIEWSTATEENCRYPTED")?->attributes->getNamedItem("value")?->value;
    $eventValidation = $document->getElementById("__EVENTVALIDATION")?->attributes->getNamedItem("value")?->value;

    $data = [
      "ctl00" => "ResultsUpdatePanel|b1",
      "TextBox1" => $this->domainParts[0],
      "ddl" => $ddl,
      "b1" => "WhoIs",
      "__ASYNCPOST" => "true",
      "__EVENTTARGET" => "",
      "__EVENTARGUMENT" => "",
      "__VIEWSTATE" => $viewState,
      "__VIEWSTATEGENERATOR" => $viewStateGenerator,
      "__VIEWSTATEENCRYPTED" => $viewStateEncrypted,
      "__EVENTVALIDATION" => $eventValidation,
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_COOKIE => $cookies,
    ];

    ["code" => $code, "body" => $html] = $this->requestWithInfo($url, $options);

    if ($code !== 200) {
      return "";
    }

    $document->loadHTML($html);

    $result = trim($document->getElementById("Result")?->textContent ?? "");
    if ($result) {
      return $result;
    }

    $data = [
      "ctl00" => "ResultsUpdatePanel|WhoIs\$ctl02\$link",
      "TextBox1" => $this->domainParts[0],
      "ddl" => $ddl,
      "__ASYNCPOST" => "true",
      "__EVENTTARGET" => "WhoIs\$ctl02\$link",
    ];

    preg_match_all("/\|hiddenField\|([^|]+)\|([^|]*)\|/", $html, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
      if ($match[1] !== "__EVENTTARGET") {
        $data[$match[1]] = $match[2];
      }
    }

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_COOKIE => $cookies,
    ];

    ["code" => $code] = $this->requestWithInfo($url, $options);

    if ($code !== 200) {
      return "";
    }

    $url = "https://dns.jo/WhoisDetails.aspx";

    $options = [CURLOPT_COOKIE => $cookies];

    $html = $this->request($url, $options);

    $document->loadHTML($html);

    $xPath = new DOMXPath($document);

    $spans = $xPath->query("//span[starts-with(@id, 'ContentPlaceHolder1_')]");

    $whois = "";

    for ($i = 0; $i < $spans->length; $i += 2) {
      $key = trim($spans->item($i)->textContent);
      $value = trim($spans->item($i + 1)?->textContent ?? "");

      if ($key) {
        $whois .= "$key: $value\n";
      }
    }

    return $whois;
  }

  private function getLK(): string
  {
    $url = "https://register.domains.lk/proxy/domains/single-search?keyword=" . $this->domain;

    $jsonText = $this->request($url);

    $json = json_decode($jsonText, true);

    $whois = "";

    $availability = $json["result"]["domainAvailability"] ?? null;

    if ($availability) {
      $message = $availability["message"] ?? "";
      if ($message === "Domain name you searched is restricted") {
        $message = "Domain name is restricted";
      }

      $whois .= "Message: " . $message . "\n";
      $whois .= "Domain Name: " . ($availability["domainName"] ?? "") . "\n";

      $domainInfo = $availability["domainInfo"] ?? null;
      if ($domainInfo) {
        $expireDate = $domainInfo["expireDate"] ?? "";
        $expireDate = DateTime::createFromFormat("l, jS F, Y", $expireDate);
        $expireDate = $expireDate ? $expireDate->format("Y-m-d") : "";
        $whois .= "Registry Expiry Date: " . $expireDate . "\n";

        $whois .= "Registrant Name: " . ($domainInfo["registeredTo"] ?? "") . "\n";
      }
    }

    return $whois;
  }

  private function getMT(): string
  {
    $url = "https://www.nic.org.mt/dotmt/whois/?" . $this->domain;

    $html = $this->request($url);

    $document = new DOMDocument();
    $document->loadHTML($html);

    $pre = $document->getElementsByTagName("pre")->item(0);
    if ($pre) {
      return $pre->textContent;
    }

    return "";
  }

  private function getNI(): string
  {
    $url = "https://apiecommercenic.uni.edu.ni/api/v1/dominios/whois?dominio=" . $this->domain;

    ["code" => $code, "body" => $jsonText] = $this->requestWithInfo($url);

    if ($code === 404) {
      return "Domain not found";
    }

    $json = json_decode($jsonText, true);

    $whois = "Domain Name: " . $this->domain . "\n";
    if (isset($json["datos"])) {
      $data = $json["datos"];

      $whois .= "Registry Expiry Date: " . ($data["fechaExpiracion"] ?? "") . "\n";
      $whois .= "Registrant Name: " . ($data["cliente"] ?? "") . "\n";
      $whois .= "Registrant Address: " . ($data["direccion"] ?? "") . "\n";
    }
    if (isset($json["contactos"])) {
      $contacts = $json["contactos"];

      $whois .= "Contact Type: " . ($contacts["tipoContacto"] ?? "") . "\n";
      $whois .= "Contact Name: " . ($contacts["nombre"] ?? "") . "\n";
      $whois .= "Contact Email: " . implode(",", array_column($contacts["correoElectronico"] ?? [], "value")) . "\n";
      $whois .= "Contact Phone: " . ($contacts["telefono"] ?? "") . "\n";
      $whois .= "Contact Cellphone: " . ($contacts["celular"] ?? "") . "\n";
    }

    return $whois;
  }

  private function getNP(): string
  {
    $url = "https://register.com.np/whois-lookup";

    $options = [CURLOPT_HEADER => true];

    ["headers" => $headers, "body" => $html] = $this->requestWithInfo($url, $options);

    $document = new DOMDocument();
    $document->loadHTML($html);

    $token = "";

    $inputs = $document->getElementsByTagName("input");
    foreach ($inputs as $input) {
      if ($input->attributes->getNamedItem("name")?->value === "_token") {
        $token = $input->attributes->getNamedItem("value")?->value;
        break;
      }
    }

    if (!$token) {
      return "";
    }

    $url = "https://register.com.np/checkdomain_whois";

    $data = [
      "_token" => $token,
      "domainName" => $this->domainParts[0],
      "domainExtension" => "." . $this->domainParts[1],
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_COOKIE => $this->getCookiesFromHeaders($headers),
    ];

    $html = $this->request($url, $options);

    $document->loadHTML($html);

    $xPath = new DOMXPath($document);

    $error = $xPath->query('//p[@class="error"]')->item(0);
    if ($error) {
      return trim($error->textContent);
    }

    $whois = "";

    $trs = $document->getElementsByTagName("tr");
    foreach ($trs as $tr) {
      $tds = $tr->getElementsByTagName("td");
      if ($tds->length === 2) {
        $key = trim($tds->item(0)->textContent);
        $value = trim($tds->item(1)->textContent);

        $whois .= "$key $value\n";
      } else {
        $ths = $tr->getElementsByTagName("th");
        if ($ths->item(1) && trim($ths->item(1)->textContent) === "Status" && $tds->item(1)) {
          return "Status: " . trim($tds->item(1)->textContent);
        }
      }
    }

    return $whois;
  }

  private function getNR(): string
  {
    $params = [
      "subdomain" => $this->domainParts[0],
      "tld" => $this->domainParts[1],
      "whois" => "Submit",
    ];

    $url = "https://www.cenpac.net.nr/dns/whois.html?" . http_build_query($params);

    $html = $this->request($url);

    $document = new DOMDocument();
    $document->loadHTML($html);

    $form = $document->getElementsByTagName("form")->item(0);
    if (!$form) {
      return "";
    }

    $whois = "";

    $next = $form->nextSibling;

    while ($next) {
      switch ($next->nodeName) {
        case "a":
        case "#text":
          $whois .= ltrim($next->textContent);
          break;
        case "table":
          foreach ($next->childNodes as $tr) {
            if ($tr->childNodes->length === 1) {
              $td = $tr->childNodes->item(0);

              if ($td->childNodes->item(0)?->nodeName === "table") {
                $whois .= "\n";

                foreach ($td->childNodes->item(0)->childNodes as $subTr) {
                  if ($subTr->childNodes->length === 2) {
                    $key = trim($subTr->childNodes->item(0)->textContent);
                    $value = $subTr->childNodes->item(1)->textContent;

                    $whois .= "$key $value\n";
                  } elseif ($subTr->childNodes->length) {
                    $text = $subTr->childNodes->item(0)->textContent;
                    if ($text === html_entity_decode("&nbsp;")) {
                      $whois .= "\n";
                    } else {
                      $whois .= "$text\n";
                    }
                  }
                }
              } else {
                $text = $td->textContent;
                if ($text === html_entity_decode("&nbsp;")) {
                  $whois .= "\n";
                } else {
                  $whois .= "$text\n";
                }
              }
            } elseif ($tr->childNodes->length === 2) {
              $key = trim($tr->childNodes->item(0)->textContent);
              $value = $tr->childNodes->item(1)->textContent;

              $whois .= "$key $value\n";
            }
          }
          break;
      }

      $next = $next->nextSibling;
    }

    return str_replace(" (modify)", "", $whois);
  }

  private function getPA(): string
  {
    $url = "https://nic.pa:8080/whois/" . $this->domain;

    $options = [CURLOPT_SSL_VERIFYPEER => false];

    ["code" => $code, "body" => $jsonText] = $this->requestWithInfo($url, $options);

    if ($code === 404) {
      return "Domain not found";
    }

    $json = json_decode($jsonText, true);

    $whois = "";

    if (isset($json["payload"])) {
      $payload = $json["payload"];

      $whois .= "Domain Name: " . ($payload["Dominio"] ?? "") . "\n";
      $whois .= "Updated Date: " . ($payload["fecha_actualizacion"] ?? "") . "\n";
      $whois .= "Creation Date: " . ($payload["fecha_creacion"] ?? "") . "\n";
      $whois .= "Registry Expiry Date: " . ($payload["fecha_expiracion"] ?? "") . "\n";
      $whois .= "Domain Status: " . ($payload["Estatus"] ?? "") . "\n";

      foreach ($payload["NS"] ?? [] as $nameServer) {
        $whois .= "Name Server: " . $nameServer . "\n";
      }

      if (isset($payload["titular"]["contacto"])) {
        $contact = $payload["titular"]["contacto"];

        $whois .= "Registrant Name: " . ($contact["nombre"] ?? "") . "\n";
        $whois .= "Registrant Street: " . implode(", ", array_filter([$contact["direccion1"] ?? "", $contact["direccion2"] ?? ""])) . "\n";
        $whois .= "Registrant City: " . ($contact["ciudad"] ?? "") . "\n";
        $whois .= "Registrant State/Province: " . ($contact["estado"] ?? "") . "\n";
        $whois .= "Registrant Country: " . ($contact["ubicacion"] ?? "") . "\n";
        $whois .= "Registrant Phone: " . implode(", ", array_filter([$contact["telefono"] ?? "", $contact["telefono_oficina"] ?? ""])) . "\n";
        $whois .= "Registrant Email: " . ($contact["email"] ?? "") . "\n";
      }
    } elseif (isset($json["mensaje"])) {
      $whois .= $json["mensaje"];
    }

    return $whois;
  }

  private function getPH(): string
  {
    $url = "https://whois.dot.ph/?search=" . $this->domain;

    $html = $this->request($url);

    $document = new DOMDocument();
    $document->loadHTML($html);

    $message = $document->getElementById("alert-message");
    if ($message) {
      return trim($message->textContent);
    }

    $whois = "";

    $pre = $document->getElementsByTagName("pre")->item(0);
    if ($pre) {
      foreach ($pre->childNodes as $child) {
        switch ($child->nodeName) {
          case "b":
          case "#text":
            $whois .= $child->textContent;
            break;
          case "br":
            $whois .= "\n";
            break;
          case "span":
            $whois .= $document->saveHTML($child);
            break;
        }
      }

      if (preg_match("/createDate = moment\('(.+?)'\)/", $html, $matches)) {
        $whois = str_replace('<span id="create-date"></span>', $matches[1], $whois);
      }
      if (preg_match("/expiryDate = moment\('(.+?)'\)/", $html, $matches)) {
        $whois = str_replace('<span id="expiry-date"></span>', $matches[1], $whois);
      }
      if (preg_match("/updateDate = moment\('(.+?)'\)/", $html, $matches)) {
        $whois = str_replace('<span id="update-date"></span>', $matches[1], $whois);
      }
    }

    return trim($whois);
  }

  private function getPY(): string
  {
    return "Please visit https://www.nic.py/consultdompy.php";
  }

  private function getSV(): string
  {
    $url = "https://svnet.sv/accion/procesos.php";

    $data = [
      "key" => "Buscar",
      "nombre" => $this->domainParts[0],
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
    ];

    $html = $this->request($url, $options);

    $document = new DOMDocument();
    $document->loadHTML($html);

    $xPath = new DOMXPath($document);

    $danger = $xPath->query("//div[contains(@class, 'alert-danger')]")->item(0);
    if ($danger) {
      return trim(str_replace("\t", " ", $danger->textContent));
    }

    $id = "";

    $button = $xPath->query("//strong[text()='$this->domain']/following-sibling::button[1]")->item(0);
    if ($button) {
      $value = $button->attributes?->getNamedItem("onclick")?->value;
      if ($value && preg_match("/\((\d+)\)/", $value, $matches)) {
        $id = $matches[1];
      }
    }

    if (!$id) {
      return "DOMINIO NO REGISTRADO";
    }

    $data = [
      "key" => "Whois",
      "ID" => $id,
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
    ];

    $html = $this->request($url, $options);

    $document->loadHTML('<?xml encoding="UTF-8"?>' . str_replace("&nbsp;", "", $html));

    $whois = "";

    $trs = $document->getElementsByTagName("tr");
    foreach ($trs as $tr) {
      $tds = $tr->getElementsByTagName("td");
      if ($tds->length === 2) {
        $key = trim($tds->item(0)->textContent);
        $value = trim($tds->item(1)->textContent);

        $whois .= "$key $value\n";
      }
    }

    return $whois;
  }

  private function getTJ(): string
  {
    $url = "http://www.nic.tj/cgi/whois2?domain=" . substr($this->domain, 0, -3);

    $html = $this->request($url);

    $document = new DOMDocument();
    $document->loadHTML($html);

    $p = $document->getElementsByTagName("p")->item(0);
    if ($p) {
      return trim($p->textContent);
    }

    $whois = "";

    $trs = $document->getElementsByTagName("tr");
    foreach ($trs as $tr) {
      $tds = $tr->getElementsByTagName("td");
      if ($tds->length === 1) {
        $whois .= "\n" . strtoupper(trim($tds->item(0)->textContent)) . "\n";
      } elseif ($tds->length === 2) {
        $key = trim($tds->item(0)->textContent);
        if ($tds->item(0)->attributes->getNamedItem("class")?->value === "subfield") {
          $key = "  $key";
        } else {
          $key = ucwords($key, " -");
        }

        $value = trim($tds->item(1)->textContent);

        $whois .= ($value === html_entity_decode("&nbsp;") ? "$key" : "$key: $value") . "\n";
      }
    }

    return $whois;
  }

  private function getTT(): string
  {
    $url = "https://nic.tt/cgi-bin/search.pl";

    $data = [
      "name" => $this->domain,
      "Search" => "Search",
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
    ];

    $html = $this->request($url, $options);

    $document = new DOMDocument();
    $document->loadHTML(str_replace("&nbsp", " ", $html));

    $xPath = new DOMXPath($document);

    $message = $xPath->query("//div[@class='main']/text()")->item(0);
    if ($message) {
      return trim($message->textContent);
    }

    $whois = "";

    $trs = $document->getElementsByTagName("tr");
    foreach ($trs as $tr) {
      $tds = $tr->getElementsByTagName("td");
      if ($tds->length === 2) {
        $key = trim($tds->item(0)->textContent);
        $value = trim($tds->item(1)->textContent);

        $whois .= "$key: $value\n";
      }
    }

    return str_replace(" (owner can view under Retrieve->Domain Details)", "", $whois);
  }

  /**
   * @throws Exception
   */
  private function getVN(): string
  {
    $url = "https://whois.inet.vn/whois?domain=" . $this->domain;

    $options = [CURLOPT_HEADER => true, CURLOPT_NOBODY => true];

    ["headers" => $headers] = $this->requestWithInfo($url, $options);

    $url = "https://whois.inet.vn/api/whois/domainspecify/" . $this->domain;

    $options = [
      CURLOPT_HTTPHEADER => ["X-Requested-With: XMLHttpRequest"],
      CURLOPT_COOKIE => $this->getCookiesFromHeaders($headers),
    ];

    $jsonText = $this->request($url, $options);

    $json = json_decode($jsonText, true);

    unset($json["message"]);

    $code = $json["code"] ?? "";

    if ($code === "1") {
      $url = "https://whois.inet.vn/api/domain/checkavailable";

      $data = ["name" => $this->domain];

      $options = [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
      ];

      $jsonText = $this->request($url, $options);

      $availableJson = json_decode($jsonText, true);

      $availability = $availableJson["availability"] ?? "";
      if ($availability) {
        $json["availability"] = $availability;
      }

      $message = $availableJson["message"] ?? "";
      if ($message) {
        $json["message"] = $message;
      }
    }

    $whois = "";

    $availability = $json["availability"] ?? "";
    if ($availability) {
      switch ($availability) {
        case "available":
          $whois .= "The domain name has not been registered\n";
          break;
        case "notavailable":
          $whois .= "The domain $this->domain cannot be registered\n";

          $message = $json["message"] ?? "";
          if ($message) {
            $whois .= "$message\n";
          }
          break;
      }
    }

    if ($code === "0") {
      $whois .= "Domain Name: " . ($json["domainName"] ?? "") . "\n";
      $whois .= "Registrar: " . ($json["registrar"] ?? "") . "\n";

      $rawText = $json["rawtext"] ?? "";
      if ($rawText) {
        $raw = json_decode($rawText, true);

        $issuedDate = $raw["issuedDate"] ?? null;
        if ($issuedDate) {
          $whois .= "Creation Date: " . $this->formatVNDate($issuedDate) . "\n";
        }

        $expiredDate = $raw["expiredDate"] ?? null;
        if ($expiredDate) {
          $whois .= "Registry Expiry Date: " . $this->formatVNDate($expiredDate) . "\n";
        }
      } else {
        $whois .= "Creation Date: " . ($json["creationDate"] ?? "") . "\n";
        $whois .= "Registry Expiry Date: " . ($json["expirationDate"] ?? "") . "\n";
      }

      foreach ($json["status"] ?? [] as $status) {
        $whois .= "Domain Status: " . $status . "\n";
      }

      foreach ($json["nameServer"] ?? [] as $nameServer) {
        $whois .= "Name Server: " . $nameServer . "\n";
      }

      $whois .= "Registrant Name: " . ($json["registrantName"] ?? "") . "\n";
      $whois .= "DNSSEC: " . ($json["DNSSEC"] ?? "") . "\n";
    }

    return $whois;
  }

  /**
   * @param array{
   *   year?: int,
   *   month?: int,
   *   day?: int,
   *   hour?: int,
   *   minute?: int,
   *   second?: int,
   *   timezone?: int
   * } $date
   *
   * @throws Exception
   */
  private function formatVNDate(array $date): string
  {
    $dateString = sprintf(
      "%04d-%02d-%02d %02d:%02d:%02d",
      $date["year"] ?? 1970,
      $date["month"] ?? 1,
      $date["day"] ?? 1,
      $date["hour"] ?? 0,
      $date["minute"] ?? 0,
      $date["second"] ?? 0
    );

    $timezoneOffset = $date["timezone"] ?? 0;
    $timezoneSign = $timezoneOffset < 0 ? "-" : "+";
    $timezoneOffset = abs($timezoneOffset);

    $offsetHours = intdiv($timezoneOffset, 60);
    $offsetMinutes = $timezoneOffset % 60;

    $timezone = sprintf("%s%02d:%02d", $timezoneSign, $offsetHours, $offsetMinutes);

    $dateTime = new DateTime($dateString, new DateTimeZone($timezone));
    $dateTime->setTimezone(new DateTimeZone("UTC"));

    return $dateTime->format('Y-m-d\TH:i:s\Z');
  }
}
