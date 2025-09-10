<?php
class WHOISWeb
{
  public $domain;

  public $extension;

  public const EXTENSIONS = [
    "bb",
    "bt",
    "cu",
    "cy",
    "dz",
    "gm",
    "gt",
    "gw",
    "hm",
    "jo",
    "lk",
    "mt",
    "ni",
    "np",
    "nr",
    "pa",
    "ph",
    "sv",
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
    $functionName = "get" . strtoupper($this->extension);

    return $this->$functionName();
  }

  private function request($url, $options = [], $returnArray = false)
  {
    $curl = curl_init($url);

    $headers = array_filter(
      $options[CURLOPT_HTTPHEADER] ?? [],
      fn($header) => !str_starts_with($header, "User-Agent:"),
    );
    $headers[] = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36";
    $options[CURLOPT_HTTPHEADER] = $headers;

    $defaultOptions = [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_TIMEOUT => 10,
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

    libxml_use_internal_errors(true);
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

  private function getBT()
  {
    $domainParts = explode(".", $this->domain, 2);

    $params = [
      "query" => $domainParts[0],
      "ext" => "." . $domainParts[1],
    ];

    $url = "https://www.nic.bt/search?" . http_build_query($params);

    $response = $this->request($url);

    libxml_use_internal_errors(true);
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

    libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML('<?xml encoding="UTF-8"?>' . $response);

    $whois = "";

    $xPath = new DOMXPath($document);
    $message = $xPath->query('//td[@class="commontextgray" and @height="5"]')->item(0);

    if ($message) {
      return trim($message->textContent);
    }

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

    $domainParts = explode(".", $this->domain, 2);

    $data = [
      "domainName" => $domainParts[0],
      "domainEndingName" => $domainParts[1],
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
      if (isset($json[0]["description"])) {
        return $json[0]["description"];
      } else if (isset($json[0]["status"]) && $json[0]["status"] === "Διαθέσιμο") {
        return "status: available";
      }

      return "";
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
    }
    if (isset($json["registrantWhoIs"]["organizationWhoIs"])) {
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
    $url = "https://api.nic.dz/v1/domains/" . $this->domain;

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

  private function getGM()
  {
    $url = "https://www.nic.gm/nic-scripts/checkdom.aspx?dname=" . $this->domain;

    $response = $this->request($url);

    libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML($response);

    $whois = "";

    $tds = $document->getElementsByTagName("td");
    foreach ($tds as $td) {
      $text = "";

      foreach ($td->childNodes as $child) {
        switch ($child->nodeName) {
          case "b":
          case "h":
          case "#text":
            $textContent = trim($child->textContent);
            if ($textContent) {
              $text .= $text ? " $textContent" : $textContent;
            }
            break;
          case "p":
            $text = "";
            foreach ($child->childNodes as $c) {
              if ($c->nodeName !== "a") {
                $text .= $c->textContent;
              }
            }
            $text = trim($text);
            if (str_starts_with($text, "Domain name:")) {
              $text = "$text.gm";
            }
            $whois .= str_replace("\n", " ", $text) . "\n";
            $text = "";
            break;
        }
      }

      if ($text) {
        $whois .= "$text\n";
      }
    }

    return $whois;
  }

  private function getGT()
  {
    $url = "https://www.gt/sitio/whois.php?dn=" . $this->domain . "&lang=en";

    $response = $this->request($url);

    libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML(str_replace("&nbsp;", " ", $response));

    $whois = "";

    $xPath = new DOMXPath($document);
    $message = $xPath->query('//div[@class="caja caja-message"]')->item(0);

    if ($message) {
      return trim(preg_replace("/ {2,}/", "", $message->textContent));
    }

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
              $whois .= "  " . trim(preg_replace(["/\n/", "/ +/"], ["", " "], $nameServer->textContent)) . "\n";
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

    libxml_use_internal_errors(true);
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
        $prevNodeValue = trim($fieldset->childNodes->item($j - 1)->nodeValue);
        $nodeValue = trim($fieldset->childNodes->item($j)->nodeValue);

        if ($nodeName === "span") {
          $whois .= "\n$nodeValue\n\n";
        } else if (
          $nodeName === "#text" &&
          $prevNodeName === "label" &&
          $prevNodeValue !== "E-mail:"
        ) {
          $whois .= "$prevNodeValue $nodeValue\n";
        } else if ($nodeName === "a") {
          $whois .= "E-mail: $nodeValue\n";
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

    libxml_use_internal_errors(true);
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

  private function getJO()
  {
    $domainParts = explode(".", $this->domain, 2);

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

    libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML($body);

    $xPath = new DOMXPath($document);
    $expression = "//select[@id='ddl']/option[normalize-space(text())='." . $domainParts[1] .  "']";
    $ddl = $xPath->query($expression)->item(0)?->attributes->getNamedItem("value")?->value;

    $viewState = $document->getElementById("__VIEWSTATE")?->attributes->getNamedItem("value")?->value;
    $viewStateGenerator = $document->getElementById("__VIEWSTATEGENERATOR")?->attributes->getNamedItem("value")?->value;
    $viewStateEncrypted = $document->getElementById("__VIEWSTATEENCRYPTED")?->attributes->getNamedItem("value")?->value;
    $eventValidation = $document->getElementById("__EVENTVALIDATION")?->attributes->getNamedItem("value")?->value;

    $data = [
      "ctl00" => "ResultsUpdatePanel|b1",
      "TextBox1" => $domainParts[0],
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
      "TextBox1" => $domainParts[0],
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

    $whois = "";

    $xPath = new DOMXPath($document);
    $spans = $xPath->query("//span[starts-with(@id, 'ContentPlaceHolder1_')]");

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
    $url = "https://www.domains.lk/wp-content/themes/bridge-child/getDomainData.php?domainname=" . $this->domain;

    $response = $this->request($url);

    $json = json_decode($response, true);

    $whois = "";

    $available = $json["Required"]["Available"] ?? -1;
    $message = $json["Message"] ?? "";

    if ($available === 0) {
      $messageCode = $json["MessageCode"] ?? -1;
      if ($messageCode === 105 || $messageCode === 106) {
        $domain = $json["DomainName"] ?? "";

        $expiryDate = trim(explode("-", $json["ExpireDate"] ?? "")[1] ?? "");
        $expiryDate = DateTime::createFromFormat("l, jS F, Y", $expiryDate);
        $expiryDate = $expiryDate ? $expiryDate->format("Y-m-d") : "";

        if ($messageCode === 105) {
          $whois .= "$domain is registered\n\n";
        } else {
          $whois .= "$domain is suspended\n\n";
        }
        $whois .= "Domain Name: $domain\n";
        $whois .= "Registry Expiry Date: " . $expiryDate . "\n";
        $whois .= "Registrant Name: " . trim(explode("-", $message)[1] ?? "") . "\n";
      } else {
        $whois = $message;
      }
    } else if ($available === 1) {
      $whois = $message;
    }

    return $whois;
  }

  private function getMT()
  {
    $url = "https://www.nic.org.mt/dotmt/whois/?" . $this->domain;

    $response = $this->request($url);

    libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML($response);

    $whois = "";

    $pre = $document->getElementsByTagName("pre")->item(0);
    if ($pre) {
      $whois = $pre->textContent;
    }

    return $whois;
  }

  private function getNI()
  {
    $url = "https://apiecommercenic.uni.edu.ni/api/v1/dominios/whois?dominio=" . $this->domain;

    $options = [CURLOPT_SSL_VERIFYPEER => false];

    ["response" => $response, "code" => $code] = $this->request($url, $options, true);

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

    libxml_use_internal_errors(true);
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

    $domainParts = explode(".", $this->domain, 2);

    $data = [
      "_token" => $token,
      "domainName" => $domainParts[0],
      "domainExtension" => "." . $domainParts[1],
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_COOKIE => $cookies,
    ];

    $response = $this->request($url, $options);

    $document->loadHTML($response);

    $whois = "";

    $xPath = new DOMXPath($document);
    $error = $xPath->query('//p[@class="error"] | //h1[@class="break-long-words exception-message"]');

    if ($error->length) {
      return trim($error->item(0)->textContent);
    }

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
          $whois = "Status: " . trim($tds->item(1)->textContent);
          break;
        }
      }
    }

    return $whois;
  }

  private function getNR()
  {
    $domainParts = explode(".", $this->domain, 2);

    $params = [
      "subdomain" => $domainParts[0],
      "tld" => $domainParts[1],
      "whois" => "Submit",
    ];

    $url = "https://www.cenpac.net.nr/dns/whois.html?" . http_build_query($params);

    $response = $this->request($url);

    libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML($response);

    $whois = "";

    $form = $document->getElementsByTagName("form")->item(0);
    if (!$form) {
      return "";
    }

    $next = $form->nextSibling;
    while ($next) {
      switch ($next->nodeName) {
        case "a":
        case "#text":
          $whois .= $next->textContent;
          break;
        case "table":
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
                    $text = $tr->childNodes->item(0)->textContent;
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

    libxml_use_internal_errors(true);
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

    return $whois;
  }

  private function getSV()
  {
    $url = "https://svnet.sv/accion/procesos.php";

    $data = [
      "key" => "Buscar",
      "nombre" => explode(".", $this->domain, 2)[0],
    ];

    $options = [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
    ];

    $response = $this->request($url, $options);

    libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML($response);

    $xPath = new DOMXPath($document);
    $button = $xPath->query("//strong[text()='$this->domain']/following-sibling::button[1]");

    $id = "";

    if ($button->length) {
      $onClick = $button->item(0)->attributes->getNamedItem("onclick");
      if ($onClick && preg_match("/\((\d+)\)/", $onClick->value, $matches)) {
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

    libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->loadHTML($response);

    $whois = "";

    $trs = $document->getElementsByTagName("tr");
    if ($trs->length) {
      foreach ($trs as $tr) {
        $tds = $tr->getElementsByTagName("td");
        if ($tds->length === 1) {
          $whois .= "\n" . strtoupper(trim($tds->item(0)->textContent)) . "\n";
        } else if ($tds->length === 2) {
          $class = $tds->item(0)->attributes->getNamedItem("class");
          $key = trim($tds->item(0)->textContent);
          if ($class && $class->value === "subfield") {
            $key = "  $key";
          } else {
            $key = ucwords($key, " -");
          }

          $value = trim($tds->item(1)->textContent);

          $whois .= ($value === html_entity_decode("&nbsp;") ? "$key" : "$key: $value") . "\n";
        }
      }
    } else {
      $p = $document->getElementsByTagName("p")->item(0);
      if ($p) {
        $whois = trim($p->textContent);
      }
    }

    return $whois;
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
    $document->loadHTML(str_replace("&nbsp", " ", $response));

    $whois = "";

    $xPath = new DOMXPath($document);
    $main = $xPath->query('//div[@class="main"]')->item(0);

    if ($main) {
      foreach ($main->childNodes as $child) {
        switch ($child->nodeName) {
          case "table":
            $trs = $xPath->query("./tr", $child);
            foreach ($trs as $tr) {
              $tds = $tr->childNodes;
              if ($tds->length === 3) {
                $key = trim($tds->item(0)->textContent);
                $value = trim($tds->item(2)->textContent);

                $whois .= "$key: $value\n";
              }
            }
            break;
          case "#text":
            $whois .= trim($child->textContent) . "\n";
            break;
        }
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
