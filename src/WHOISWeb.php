<?php
class WHOISWeb
{
  public $domain;

  public $extension;

  public const EXTENSIONS = [
    "bb",
    "bt",
    "cy",
    "dz",
    "gm",
    "gw",
    "hm",
    "lk",
    "mt",
    "ni",
    "np",
    "nr",
    "pa",
    "ph",
    "sv",
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
    $functionName = "get" . strtoupper($this->extension);

    return $this->$functionName();
  }

  private function request($url, $options = [], $returnArray = false)
  {
    $curl = curl_init($url);

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

    $whois = "";
    if (array_key_exists("domainName", $json)) {
      $whois .= "Domain Name: " . $json["domainName"] . "\n";
    }
    if (array_key_exists("registrar", $json)) {
      $whois .= "Registrar: " . $json["registrar"] . "\n";
    }
    if (array_key_exists("creationDate", $json)) {
      $whois .= "Creation Date: " . $json["creationDate"] . "\n";
    }
    if (array_key_exists("orgName", $json)) {
      $whois .= "Registrant Organization: " . $json["orgName"] . "\n";
    }
    if (array_key_exists("addressOrg", $json)) {
      $whois .= "Registrant Address: " . $json["addressOrg"] . "\n";
    }
    if (array_key_exists("contactAdm", $json)) {
      $whois .= "Admin Name: " . $json["contactAdm"] . "\n";
    }
    if (array_key_exists("orgNameAdm", $json)) {
      $whois .= "Admin Organization: " . $json["orgNameAdm"] . "\n";
    }
    if (array_key_exists("addressAdm", $json)) {
      $whois .= "Admin Address: " . $json["addressAdm"] . "\n";
    }
    if (array_key_exists("phoneAdm", $json)) {
      $whois .= "Admin Phone: " . $json["phoneAdm"] . "\n";
    }
    if (array_key_exists("faxAdm", $json)) {
      $whois .= "Admin Fax: " . $json["faxAdm"] . "\n";
    }
    if (array_key_exists("emailAdm", $json)) {
      $whois .= "Admin Email: " . $json["emailAdm"] . "\n";
    }
    if (array_key_exists("contactTech", $json)) {
      $whois .= "Tech Name: " . $json["contactTech"] . "\n";
    }
    if (array_key_exists("orgNameTech", $json)) {
      $whois .= "Tech Organization: " . $json["orgNameTech"] . "\n";
    }
    if (array_key_exists("addressTech", $json)) {
      $whois .= "Tech Address: " . $json["addressTech"] . "\n";
    }
    if (array_key_exists("phoneTech", $json)) {
      $whois .= "Tech Phone: " . $json["phoneTech"] . "\n";
    }
    if (array_key_exists("faxTech", $json)) {
      $whois .= "Tech Fax: " . $json["faxTech"] . "\n";
    }
    if (array_key_exists("emailTech", $json)) {
      $whois .= "Tech Email: " . $json["emailTech"] . "\n";
    }

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
      if (array_key_exists("fechaExpiracion", $data)) {
        $whois .= "Registry Expiry Date: " . $data["fechaExpiracion"] . "\n";
      }
      if (array_key_exists("cliente", $data)) {
        $whois .= "Registrant Name: " . $data["cliente"] . "\n";
      }
      if (array_key_exists("direccion", $data)) {
        $whois .= "Registrant Address: " . $data["direccion"] . "\n";
      }
    }
    if (isset($json["contactos"])) {
      $contacts = $json["contactos"];
      if (array_key_exists("tipoContacto", $contacts)) {
        $whois .= "Contact Type: " . $contacts["tipoContacto"] . "\n";
      }
      if (array_key_exists("nombre", $contacts)) {
        $whois .= "Contact Name: " . $contacts["nombre"] . "\n";
      }
      if (array_key_exists("correoElectronico", $contacts)) {
        $emails = implode(",", array_column($contacts["correoElectronico"] ?? [], "value"));
        $whois .= "Contact Email: $emails\n";
      }
      if (array_key_exists("telefono", $contacts)) {
        $whois .= "Contact Phone: " . $contacts["telefono"] . "\n";
      }
      if (array_key_exists("celular", $contacts)) {
        $whois .= "Contact Cellphone: " . $contacts["celular"] . "\n";
      }
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
      if (array_key_exists("Dominio", $payload)) {
        $whois .= "Domain Name: " . $payload["Dominio"] . "\n";
      }
      if (array_key_exists("fecha_actualizacion", $payload)) {
        $whois .= "Updated Date: " . $payload["fecha_actualizacion"] . "\n";
      }
      if (array_key_exists("fecha_creacion", $payload)) {
        $whois .= "Creation Date: " . $payload["fecha_creacion"] . "\n";
      }
      if (array_key_exists("fecha_expiracion", $payload)) {
        $whois .= "Registry Expiry Date: " . $payload["fecha_expiracion"] . "\n";
      }
      if (array_key_exists("Estatus", $payload)) {
        $whois .= "Domain Status: " . $payload["Estatus"] . "\n";
      }
      if (isset($payload["NS"])) {
        foreach ($payload["NS"] as $nameServer) {
          $whois .= "Name Server: " . $nameServer . "\n";
        }
      }
      if (isset($payload["titular"]["contacto"])) {
        $contact = $payload["titular"]["contacto"];
        if (array_key_exists("nombre", $contact)) {
          $whois .= "Registrant Name: " . $contact["nombre"] . "\n";
        }
        if (array_key_exists("direccion1", $contact) && array_key_exists("direccion2", $contact)) {
          $street = implode(", ", array_filter([$contact["direccion1"], $contact["direccion2"]]));
          $whois .= "Registrant Street: " . $street . "\n";
        }
        if (array_key_exists("ciudad", $contact)) {
          $whois .= "Registrant City: " . $contact["ciudad"] . "\n";
        }
        if (array_key_exists("estado", $contact)) {
          $whois .= "Registrant State/Province: " . $contact["estado"] . "\n";
        }
        if (array_key_exists("ubicacion", $contact)) {
          $whois .= "Registrant Country: " . $contact["ubicacion"] . "\n";
        }
        if (array_key_exists("telefono", $contact) && array_key_exists("telefono_oficina", $contact)) {
          $phone = implode(", ", array_filter([$contact["telefono"], $contact["telefono_oficina"]]));
          $whois .= "Registrant Phone: " . $phone . "\n";
        }
        if (array_key_exists("email", $contact)) {
          $whois .= "Registrant Email: " . $contact["email"] . "\n";
        }
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
