<?php
class WHOISWeb
{
  public $domain;

  public $extension;

  private $domainParts;

  private static $extensionToFunctionSuffix = [
    "bb" => ["bb"],
    "bo" => ["bo"],
    "bt" => ["bt"],
    "cu" => ["cu"],
    "cy" => ["cy"],
    "dz" => ["dz", "الجزائر"],
    "gf" => ["gf", "mq"],
    "gm" => ["gm"],
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
    "sv" => ["sv"],
    "tj" => ["tj"],
    "tt" => ["tt"],
  ];

  public static function isSupported($extension)
  {
    foreach (self::$extensionToFunctionSuffix as $extensions) {
      if (in_array($extension, $extensions)) {
        return true;
      }
    }

    return false;
  }

  public function __construct($domain, $extension)
  {
    $this->domain = $domain;
    $this->extension = $extension;
    $this->domainParts = explode(".", $domain, 2);

    libxml_use_internal_errors(true);
  }

  public function getData()
  {
    foreach (self::$extensionToFunctionSuffix as $functionSuffix => $extensions) {
      if (in_array(strtolower($this->extension), $extensions)) {
        $functionName = "get" . strtoupper($functionSuffix);
        return $this->$functionName();
      }
    }

    return "";
  }

  private function request($url, $options = [], $returnArray = false)
  {
    $curl = curl_init($url);

    $defaultOptions = [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_USERAGENT => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36",
    ];

    curl_setopt_array($curl, array_replace($defaultOptions, $options));

    $response = curl_exec($curl);
    if ($response === false) {
      $error = curl_error($curl);
      curl_close($curl);
      throw new RuntimeException($error);
    }

    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

    curl_close($curl);

    if ($returnArray) {
      return [
        "response" => $response,
        "code" => $code,
        "headerSize" => $headerSize,
      ];
    }

    return $response;
  }

  private function getBB()
  {
    $url = "https://whois.telecoms.gov.bb/status/" . $this->domain;

    $response = $this->request($url);

    $response = str_replace(["<<<", ">>>"], ["&lt;&lt;&lt;", "&gt;&gt;&gt;"], $response);

    $document = new DOMDocument();
    $document->loadHTML($response);

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

  private function getBO()
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

    $response = $this->request($url, $options);

    $document = new DOMDocument();
    $document->loadHTML($response);

    $xPath = new DOMXPath($document);

    $error = $xPath->query('//div[@class="texto_error"]')->item(0)?->textContent;
    if ($error && trim($error)) {
      return trim($error);
    }

    preg_match('/window\.self\.location="(.+)"/i', $response, $matches);

    if (empty($matches[1])) {
      return "";
    }

    $url = "https://nic.bo/" . $matches[1];

    $options = [CURLOPT_COOKIE => "app_language=en"];

    $response = $this->request($url, $options);

    $document->loadHTML(str_replace(" :&nbsp;&nbsp;", "", $response));

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
      } else if ($tds->length === 2) {
        $key = trim($tds->item(0)->textContent);
        $value = trim($tds->item(1)->textContent);

        $whois .= "$key: $value\n";
      }
    }

    return $whois;
  }

  private function getBT()
  {
    $params = [
      "query" => $this->domainParts[0],
      "ext" => "." . $this->domainParts[1],
    ];

    $url = "https://www.nic.bt/search?" . http_build_query($params);

    $response = $this->request($url);

    $document = new DOMDocument();
    $document->loadHTML($response);

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

  private function getCU()
  {
    $url = "https://www.nic.cu/dom_search.php";

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => ["domsrch" => $this->domain],
    ];

    $response = $this->request($url, $options);

    $document = new DOMDocument();
    $document->loadHTML('<?xml encoding="UTF-8"?>' . $response);

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

  private function getCY()
  {
    $url = "https://registry.nic.cy/api/domains/_search";

    $data = [
      "domainName" => $this->domainParts[0],
      "domainEndingName" => $this->domainParts[1],
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => json_encode($data),
      CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    ];

    $response = $this->request($url, $options);

    $json = json_decode($response, true);

    if (!$json) {
      return "";
    } else if (!isset($json[0]["id"])) {
      $whois = "Status: " . ($json[0]["status"] ?? "") . "\n";
      $whois .= "Description: " . ($json[0]["description"] ?? "") . "\n";

      return $whois;
    }

    $url = "https://registry.nic.cy/api/whoIs/" . $json[0]["id"];

    $response = $this->request($url);

    $json = json_decode($response, true);

    $whois = "";
    if (isset($json["domainWhoIs"])) {
      $domain = $json["domainWhoIs"];

      $whois .= "Domain Name: " . ($domain["domainFullname"] ?? "") . "\n";
      $whois .= "Creation Date: " . implode("-", $domain["domainCreationDate"] ?? []) . "\n";
      $whois .= "Registry Expiry Date: " . implode("-", $domain["domainExpirationDate"] ?? []) . "\n";

      foreach ($domain["domainServers"] ?? [] as $server) {
        $whois .= "Name Server: " . ($server["name"] ?? "") . "\n";
      }
    }
    if (isset($json["registrantWhoIs"]["personWhoIs"])) {
      foreach ($json["registrantWhoIs"]["personWhoIs"] as $key => $value) {
        $label = $key === "personPostalCode"
          ? "Postal Code"
          : str_replace("person", "", $key);
        $whois .= "Registrant $label: $value\n";
      }
    } else if (isset($json["registrantWhoIs"]["organizationWhoIs"])) {
      foreach ($json["registrantWhoIs"]["organizationWhoIs"] as $key => $value) {
        $label = str_replace("company", "", $key);
        if ($label === "Adress") {
          $label = "Address";
        } else if ($label === "PostalCode") {
          $label = "Postal Code";
        }
        $whois .= "Registrant $label: $value\n";
      }
    }

    return $whois;
  }

  private function getDZ()
  {
    $segment = $this->extension === "dz" ? "/" : "/arabic/";
    $url = "https://api.nic.dz/v1" . $segment . "domains/" . $this->domain;

    $options = [CURLOPT_SSL_VERIFYPEER => false];

    $response = $this->request($url, $options);

    $json = json_decode($response, true);

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

  private function getGF()
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

    $response = $this->request($url, $options);

    $document = new DOMDocument();
    $document->loadHTML($response);

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

    return preg_replace("/ {2,}/", " ", $whois);
  }

  private function getGM()
  {
    $url = "https://www.nic.gm/NIC2/scripts/checkdom.aspx?dname=" . $this->domainParts[0];

    $options = [
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_HEADER => true,
      CURLOPT_NOBODY => true,
    ];

    ["response" => $response, "headerSize" => $headerSize] = $this->request($url, $options, true);

    $headers = substr($response, 0, $headerSize);

    $whois = "";

    if (str_contains($headers, "/NIC2/whois-available.html")) {
      $whois .= "No match for \"{$this->domain}\".\n";
    } else if (
      str_contains($headers, "/NIC2/whois-reserved.html") ||
      str_contains($headers, "/NIC2/whois-numbers.html")
    ) {
      $whois .= "This name is reserved by the registry.\n";
    } else if (str_contains($headers, "/NIC2/whois-details.html")) {
      $url = "https://www.nic.gm/NIC2/REG/login.aspx?whois=" . $this->domainParts[0];

      $response = $this->request($url);

      if ($response) {
        $array = explode(";", $response);

        $whois .= "Domain Name: {$this->domain}\n";
        $whois .= "Registrar: {$array[2]}\n";
        $whois .= "Creation Date: {$array[11]}\n";
        $whois .= "Registrant Name: {$array[1]}\n";
        $whois .= "Admin Name: {$array[3]}\n";
        $whois .= "Admin Organization: {$array[4]}\n";
        $whois .= "Tech Name: {$array[5]}\n";
        $whois .= "Tech Organization: {$array[6]}\n";
        $whois .= "Name Server: {$array[7]}\n";
        $whois .= "Name Server: {$array[8]}\n";
        $whois .= "Name Server: {$array[9]}\n";
        $whois .= "Name Server: {$array[10]}\n";
      }
    }

    if ($whois) {
      $url = "https://www.nic.gm/NIC2/motd.txt";

      $response = $this->request($url);

      if ($response) {
        $whois .= ">>> Last update of whois database: " . trim($response) . " <<<";
      }
    }

    return $whois;
  }

  private function getGT()
  {
    $url = "https://www.gt/sitio/whois.php?dn=" . $this->domain . "&lang=en";

    $response = $this->request($url);

    $document = new DOMDocument();
    $document->loadHTML(str_replace("&nbsp;", " ", $response));

    $xPath = new DOMXPath($document);

    $message = $xPath->query('//div[@class="caja caja-message"]')->item(0);
    if ($message) {
      return trim(preg_replace("/ {2,}/", "", $message->textContent));
    }

    $whois = "";

    $whoisNodeList = $xPath->query('//div[@class="caja caja-whois"]');
    if ($whoisNodeList->length === 2) {
      foreach ($whoisNodeList->item(0)->childNodes as $child) {
        if ($child->nodeName === "div") {
          $class = $child->attributes->getNamedItem("class")->value;
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
          } else if ($class === "alert alert-info") {
            $whois .= "\n" . trim($child->textContent) . ":\n";
          } else if ($class === "form-stack") {
            $expiration = $xPath->query(".//strong", $child)->item(0);
            if ($expiration) {
              $whois .= trim(preg_replace(["/\n/", "/ +/"], ["", " "], $expiration->textContent)) . "\n";
            } else {
              foreach ($xPath->query('.//div[@class="form-field"]', $child) as $field) {
                $whois .= "  " . trim(preg_replace(["/\n/", "/ +/"], ["", " "], $field->textContent)) . "\n";
              }
            }
          } else if ($class === "form-field") {
            foreach ($xPath->query(".//li", $child) as $nameServer) {
              $whois .= "  " . trim(preg_replace(["/\n/", "/ +/"], ["", " "], $nameServer->textContent), " \n.") . "\n";
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
            $whois .= "  " . trim(preg_replace(["/\n/", "/ +/"], ["", " "], $field->textContent)) . "\n";
          }
        }
      }
    }

    return $whois;
  }

  private function getGW()
  {
    $url = "https://registar.nic.gw/en/whois/" . $this->domain . "/";

    ["response" => $response, "code" => $code] = $this->request($url, [], true);

    if ($code === 404) {
      return "Domain not found";
    }

    $document = new DOMDocument();
    $document->loadHTML($response);

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
        } else if (
          $nodeName === "#text" &&
          $prevNodeName === "label" &&
          $prevTextContent !== "E-mail:"
        ) {
          $whois .= "$prevTextContent $textContent\n";
        } else if ($nodeName === "a") {
          $whois .= "E-mail: $textContent\n";
        }
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
    if (preg_match_all("/^Set-Cookie:\s*([^;]+)/im", $response, $matches)) {
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

    $document = new DOMDocument();
    $document->loadHTML($response);

    $whois = "";

    $pre = $document->getElementsByTagName("pre")->item(0);
    if ($pre) {
      foreach ($pre->childNodes as $child) {
        if ($child->nodeName === "a") {
          $class = $child->attributes->getNamedItem("class")->value;
          $cfEmail = $child->attributes->getNamedItem("data-cfemail")->value;
          if ($class === "__cf_email__" && $cfEmail) {
            $whois .= $this->decodeCFEmail($cfEmail);
          } else {
            $whois .= $child->textContent;
          }
        } else if ($child->nodeName === "br") {
          $whois .= "\n";
        } else {
          $whois .= $child->textContent;
        }
      }
    }

    return $whois;
  }

  private function getHU()
  {
    $url = "https://info.domain.hu/webwhois/en/domain/" . $this->domain;

    $options = [CURLOPT_POST => true, CURLOPT_POSTFIELDS => []];

    $response = $this->request($url, $options);

    $document = new DOMDocument();
    $document->loadHTML($response);

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

  private function getJO()
  {
    $url = "https://dns.jo/FirstPageen.aspx";

    $options = [CURLOPT_HEADER => true];

    ["response" => $response, "code" => $code, "headerSize" => $headerSize] = $this->request($url, $options, true);

    if ($code !== 200) {
      return "";
    }

    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    preg_match_all("/^Set-Cookie:\s*([^;]+)/im", $headers, $matches);
    $cookies = implode("; ", $matches[1]);

    $document = new DOMDocument();
    $document->loadHTML($body);

    $xPath = new DOMXPath($document);

    $expression = "//select[@id='ddl']/option[normalize-space(text())='." . $this->domainParts[1] .  "']";
    $ddl = $xPath->query($expression)->item(0)?->attributes->getNamedItem("value")?->value;

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

    ["response" => $response, "code" => $code] = $this->request($url, $options, true);

    if ($code !== 200) {
      return "";
    }

    $document->loadHTML($response);

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

    preg_match_all("/\|hiddenField\|([^|]+)\|([^|]*)\|/", $response, $matches, PREG_SET_ORDER);

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

    ["response" => $response, "code" => $code] = $this->request($url, $options, true);

    if ($code !== 200) {
      return "";
    }

    $url = "https://dns.jo/WhoisDetails.aspx";

    $options = [CURLOPT_COOKIE => $cookies];

    $response = $this->request($url, $options);

    $document->loadHTML($response);

    $xPath = new DOMXPath($document);

    $spans = $xPath->query("//span[starts-with(@id, 'ContentPlaceHolder1_')]");

    $whois = "";

    for ($i = 0; $i < $spans->length; $i += 2) {
      $key = trim($spans->item($i)->textContent ?? "");
      $value = trim($spans->item($i + 1)?->textContent ?? "");

      if ($key) {
        $whois .= "$key: $value\n";
      }
    }

    return $whois;
  }

  private function getLK()
  {
    $url = "https://register.domains.lk/proxy/domains/single-search?keyword=" . $this->domain;

    $response = $this->request($url);

    $json = json_decode($response, true);

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

  private function getMT()
  {
    $url = "https://www.nic.org.mt/dotmt/whois/?" . $this->domain;

    $response = $this->request($url);

    $document = new DOMDocument();
    $document->loadHTML($response);

    $pre = $document->getElementsByTagName("pre")->item(0);
    if ($pre) {
      return $pre->textContent;
    }

    return "";
  }

  private function getNI()
  {
    $url = "https://apiecommercenic.uni.edu.ni/api/v1/dominios/whois?dominio=" . $this->domain;

    ["response" => $response, "code" => $code] = $this->request($url, [], true);

    if ($code === 404) {
      return "Domain not found";
    }

    $json = json_decode($response, true);

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

  private function getNP()
  {
    $url = "https://register.com.np/whois-lookup";

    $options = [CURLOPT_HEADER => true];

    ["response" => $response, "headerSize" => $headerSize] = $this->request($url, $options, true);

    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    preg_match_all("/^Set-Cookie:\s*([^;]+)/im", $headers, $matches);
    $cookies = implode("; ", $matches[1]);

    $document = new DOMDocument();
    $document->loadHTML($body);

    $token = "";

    $inputs = $document->getElementsByTagName("input");
    foreach ($inputs as $input) {
      if ($input->attributes->getNamedItem("name")->value === "_token") {
        $token = $input->attributes->getNamedItem("value")->value;
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
      CURLOPT_COOKIE => $cookies,
    ];

    $response = $this->request($url, $options);

    $document->loadHTML($response);

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

  private function getNR()
  {
    $params = [
      "subdomain" => $this->domainParts[0],
      "tld" => $this->domainParts[1],
      "whois" => "Submit",
    ];

    $url = "https://www.cenpac.net.nr/dns/whois.html?" . http_build_query($params);

    $response = $this->request($url);

    $document = new DOMDocument();
    $document->loadHTML($response);

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
                  } else if ($subTr->childNodes->length) {
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
            } else if ($tr->childNodes->length === 2) {
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

  private function getPA()
  {
    $url = "https://nic.pa:8080/whois/" . $this->domain;

    $options = [CURLOPT_SSL_VERIFYPEER => false];

    ["response" => $response, "code" => $code] = $this->request($url, $options, true);

    if ($code === 404) {
      return "Domain not found";
    }

    $json = json_decode($response, true);

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
    } else if (isset($json["mensaje"])) {
      $whois .= $json["mensaje"];
    }

    return $whois;
  }

  private function getPH()
  {
    $url = "https://whois.dot.ph/?search=" . $this->domain;

    $response = $this->request($url);

    $document = new DOMDocument();
    $document->loadHTML($response);

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

      if (preg_match("/createDate = moment\('(.+?)'\)/", $response, $matches)) {
        $whois = str_replace('<span id="create-date"></span>', $matches[1], $whois);
      }
      if (preg_match("/expiryDate = moment\('(.+?)'\)/", $response, $matches)) {
        $whois = str_replace('<span id="expiry-date"></span>', $matches[1], $whois);
      }
      if (preg_match("/updateDate = moment\('(.+?)'\)/", $response, $matches)) {
        $whois = str_replace('<span id="update-date"></span>', $matches[1], $whois);
      }
    }

    return trim($whois);
  }

  private function getSV()
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

    $response = $this->request($url, $options);

    $document = new DOMDocument();
    $document->loadHTML($response);

    $xPath = new DOMXPath($document);

    $danger = $xPath->query("//div[contains(@class, 'alert-danger')]")->item(0);
    if ($danger) {
      return trim(str_replace("\t", " ", $danger->textContent));
    }

    $id = "";

    $button = $xPath->query("//strong[text()='$this->domain']/following-sibling::button[1]")->item(0);
    if ($button) {
      $value = $button->attributes->getNamedItem("onclick")?->value;
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

    $response = $this->request($url, $options);

    $document->loadHTML('<?xml encoding="UTF-8"?>' . str_replace("&nbsp;", "", $response));

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

  private function getTJ()
  {
    $url = "http://www.nic.tj/cgi/whois2?domain=" . substr($this->domain, 0, -3);

    $response = $this->request($url);

    $document = new DOMDocument();
    $document->loadHTML($response);

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
      } else if ($tds->length === 2) {
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

  private function getTT()
  {
    $url = "https://nic.tt/cgi-bin/search.pl";

    $data = [
      "name" => $this->domain,
      "Search" => "Search",
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data
    ];

    $response = $this->request($url, $options);

    $document = new DOMDocument();
    $document->loadHTML(str_replace("&nbsp", " ", $response));

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
