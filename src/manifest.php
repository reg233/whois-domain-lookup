<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/utils.php";

header("Content-Type: application/json");

$shortName = SITE_SHORT_TITLE;
$name = SITE_TITLE;
$id = ($_SERVER["QUERY_STRING"] ?? "") ? (BASE . "?" . $_SERVER["QUERY_STRING"]) : BASE;

$domain = $_GET["domain"] ?? "";
if ($domain) {
  $shortName = $domain;
  $name = $domain . " | " . SITE_TITLE;
}

$manifest = [
  "short_name" => $shortName,
  "name" => $name,
  "icons" => [
    [
      "src" => BASE . asset("public/images/manifest-icon-192.maskable.png"),
      "sizes" => "192x192",
      "type" => "image/png",
      "purpose" => "any",
    ],
    [
      "src" => BASE . asset("public/images/manifest-icon-192.maskable.png"),
      "sizes" => "192x192",
      "type" => "image/png",
      "purpose" => "maskable",
    ],
    [
      "src" => BASE . asset("public/images/manifest-icon-512.maskable.png"),
      "sizes" => "512x512",
      "type" => "image/png",
      "purpose" => "any",
    ],
    [
      "src" => BASE . asset("public/images/manifest-icon-512.maskable.png"),
      "sizes" => "512x512",
      "type" => "image/png",
      "purpose" => "maskable",
    ],
  ],
  "id" => $id,
  "start_url" => $id,
  "display" => "standalone",
  "scope" => BASE,
  "description" => SITE_DESCRIPTION,
  "screenshots" => [
    [
      "src" => BASE . asset("public/images/manifest-screenshot-narrow.png"),
      "sizes" => "1320x2868",
      "type" => "image/png",
      "form_factor" => "narrow",
    ],
    [
      "src" => BASE . asset("public/images/manifest-screenshot-narrow-dark.png"),
      "sizes" => "1320x2868",
      "type" => "image/png",
      "form_factor" => "narrow",
    ],
    [
      "src" => BASE . asset("public/images/manifest-screenshot-wide.png"),
      "sizes" => "2752x2064",
      "type" => "image/png",
      "form_factor" => "wide",
    ],
    [
      "src" => BASE . asset("public/images/manifest-screenshot-wide-dark.png"),
      "sizes" => "2752x2064",
      "type" => "image/png",
      "form_factor" => "wide",
    ],
  ],
];

echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
