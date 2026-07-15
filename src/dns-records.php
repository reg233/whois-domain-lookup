<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../src/utils.php";
require_once __DIR__ . "/../vendor/autoload.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
  http_response_code(405);
  header("Allow: GET");
  exit;
}

if (!isPasswordValid()) {
  http_response_code(401);
  exit;
}

$domain = $_GET["domain"] ?? "";
if (!$domain) {
  http_response_code(400);
  exit;
}

$subdomain = cleanDomain($_GET["subdomain"] ?? "");
if ($subdomain) {
  $domain = "$subdomain.$domain";
}

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

$hostname = idn_to_ascii($domain) ?: $domain;

$dnsRecords = [];

foreach ($types as $type => $value) {
  $records = @dns_get_record($hostname, $value["flag"]);

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
  "domain" => htmlspecialchars($domain, ENT_QUOTES, "UTF-8"),
  "data" => $dnsRecords ?: new stdClass(),
]);
