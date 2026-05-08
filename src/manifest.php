<?php
require_once __DIR__ . "/../config/config.php";

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
      "src" => BASE . "public/images/manifest-icon-192.maskable.png",
      "sizes" => "192x192",
      "type" => "image/png",
      "purpose" => "any"
    ],
    [
      "src" => BASE . "public/images/manifest-icon-192.maskable.png",
      "sizes" => "192x192",
      "type" => "image/png",
      "purpose" => "maskable"
    ],
    [
      "src" => BASE . "public/images/manifest-icon-512.maskable.png",
      "sizes" => "512x512",
      "type" => "image/png",
      "purpose" => "any"
    ],
    [
      "src" => BASE . "public/images/manifest-icon-512.maskable.png",
      "sizes" => "512x512",
      "type" => "image/png",
      "purpose" => "maskable"
    ],
  ],
  "id" => $id,
  "start_url" => $id,
  "display" => "standalone",
  "scope" => BASE,
  "description" => SITE_DESCRIPTION,
  "screenshots" => [
    [
      "src" => BASE . "public/images/manifest-screenshot-narrow.png",
      "sizes" => "1290x2796",
      "type" => "image/png",
      "form_factor" => "narrow",
    ],
    [
      "src" => BASE . "public/images/manifest-screenshot-narrow-dark.png",
      "sizes" => "1290x2796",
      "type" => "image/png",
      "form_factor" => "narrow",
    ],
    [
      "src" => BASE . "public/images/manifest-screenshot-wide.png",
      "sizes" => "3024x1890",
      "type" => "image/png",
      "form_factor" => "wide",
    ],
    [
      "src" => BASE . "public/images/manifest-screenshot-wide-dark.png",
      "sizes" => "3024x1890",
      "type" => "image/png",
      "form_factor" => "wide",
    ],
  ],
];

echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
