<?php

declare(strict_types=1);

function isPasswordValid(): bool
{
  if (!SITE_PASSWORD) {
    return true;
  }

  $password = $_COOKIE["password"] ?? null;
  if ($password === hash("sha256", SITE_PASSWORD)) {
    return true;
  }

  $authorization = $_SERVER["HTTP_AUTHORIZATION"] ?? null;
  $bearerPrefix = "Bearer ";
  if ($authorization && str_starts_with($authorization, $bearerPrefix)) {
    $hash = substr($authorization, strlen($bearerPrefix));
    if ($hash === hash("sha256", SITE_PASSWORD)) {
      return true;
    }
  }

  return false;
}

function cleanDomain(string $domain): string
{
  $domain = trim($domain);

  if (!$domain) {
    return "";
  }

  $domain = trim(preg_replace(["/\s+/", "/\.{2,}/"], ["", "."], $domain), ".");

  $parsedUrl = parse_url($domain);
  if ($parsedUrl["host"] ?? "") {
    $domain = $parsedUrl["host"];
  }

  return $domain;
}

function getProtocol(): string
{
  if (!empty($_SERVER["HTTP_X_FORWARDED_PROTO"])) {
    $values = array_map("strtolower", explode(",", $_SERVER["HTTP_X_FORWARDED_PROTO"]));

    if (in_array("https", $values, true)) {
      return "https://";
    }
  }

  if (!empty($_SERVER["HTTP_FORWARDED"])) {
    $values = array_map("strtolower", explode(";", $_SERVER["HTTP_FORWARDED"]));

    if (in_array("proto=https", $values, true)) {
      return "https://";
    }
  }

  if (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) !== "off") {
    return "https://";
  }

  return "http://";
}

/**
 * @return array<int, mixed>
 */
function getServerCurlOptions(string $url): array
{
  $certificateHosts = [
    "gsrsaovsslca2018.pem" => ["rdap.ethiotelecom.et", "rdap.registry.om"],
    "RapidSSLTLSRSACAG1.pem" => ["nic.pa"],
    "rdap.nic.cr.pem" => ["rdap.nic.cr"],
    "SectigoPublicServerAuthenticationCAOVR36.pem" => ["apiecommercenic.uni.edu.ni"],
    "ye1.pem" => ["api.nic.dz"],
  ];

  $host = strtolower((string)parse_url($url, PHP_URL_HOST));

  $options = [];

  foreach ($certificateHosts as $cert => $hosts) {
    if (in_array($host, $hosts, true)) {
      $filename = __DIR__ . "/data/certs/$cert";

      if (is_file($filename) && is_readable($filename)) {
        $options[CURLOPT_CAINFO] = $filename;
      }

      break;
    }
  }

  // rdap.nic.cr uses a self-signed certificate with no host name.
  if ($host === "rdap.nic.cr") {
    $options[CURLOPT_SSL_VERIFYHOST] = 0;
  }

  return $options;
}
