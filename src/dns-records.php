<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../vendor/autoload.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
  http_response_code(405);
  header("Allow: GET");
  die;
}

$domain = $_GET["domain"] ?? "";
$subdomain = trim($_GET["subdomain"] ?? "");

if (!$domain) {
  http_response_code(400);
  die;
}

if ($subdomain) {
  $subdomain = htmlspecialchars($subdomain, ENT_QUOTES, "UTF-8");
  $subdomain = trim(preg_replace(["/\s+/", "/\.{2,}/"], ["", "."], $subdomain), ".");
  $domain = "$subdomain.$domain";
}

function checkPassword()
{
  if (!SITE_PASSWORD) {
    return;
  }

  $password = $_COOKIE["password"] ?? null;
  if ($password === hash("sha256", SITE_PASSWORD)) {
    return;
  }

  $authorization = $_SERVER["HTTP_AUTHORIZATION"] ?? null;
  $bearerPrefix = "Bearer ";
  if ($authorization && str_starts_with($authorization, $bearerPrefix)) {
    $hash = substr($authorization, strlen($bearerPrefix));
    if ($hash === hash("sha256", SITE_PASSWORD)) {
      return;
    }
  }

  http_response_code(401);
  header("Content-Type: application/json");

  die;
}

checkPassword();

$types = [
  "A" => [
    "flag" => DNS_A,
    "fields" => ["ip", "ttl"],
  ],
  "AAAA" => [
    "flag" => DNS_AAAA,
    "fields" => ["ipv6", "ttl"],
  ],
  "CNAME" => [
    "flag" => DNS_CNAME,
    "fields" => ["target", "ttl"],
  ],
  "MX" => [
    "flag" => DNS_MX,
    "fields" => ["target", "pri", "ttl"],
  ],
  "NS" => [
    "flag" => DNS_NS,
    "fields" => ["target", "ttl"],
  ],
  "TXT" => [
    "flag" => DNS_TXT,
    "fields" => ["txt", "ttl"],
  ],
];

$fieldMap = [
  "ip" => "value",
  "ipv6" => "value",
  "txt" => "value",
  "pri" => "priority",
];

$dnsRecords = [];

foreach ($types as $type => $value) {
  $records = @dns_get_record(idn_to_ascii($domain), $value["flag"]);

  if ($records) {
    foreach ($records as $record) {
      if (!isset($dnsRecords[$type])) {
        $dnsRecords[$type] = [];
      }

      $dnsRecord = [];
      foreach ($value["fields"] as $field) {
        if (isset($record[$field])) {
          $key = $fieldMap[$field] ?? $field;
          $dnsRecord[$key] = $record[$field];
        }
      }

      $dnsRecords[$type][] = $dnsRecord;
    }
  }
}

header("Content-Type: application/json");

echo json_encode([
  "domain" => $domain,
  "data" => $dnsRecords ?: new stdClass(),
]);
