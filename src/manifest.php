<?php
require_once __DIR__ . "/../config/config.php";

header("Content-Type: application/json");

$shortName = SITE_SHORT_TITLE;
$name = SITE_TITLE;
$id = "/";

$domain = $_GET["domain"] ?? "";
if ($domain) {
  $shortName = $domain;
  $name = $domain . " | " . SITE_TITLE;
  $id = "/?domain=$domain";
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
  "background_color" => "#ffffff",
  "display" => "standalone",
  "scope" => "/",
  "theme_color" => "#e1f9f9",
  "description" => SITE_DESCRIPTION,
  "screenshots" => [
    [
      "src" => BASE . "public/images/manifest-screenshot-narrow.png",
      "sizes" => "1170x2532",
      "type" => "image/png",
      "form_factor" => "narrow",
    ],
    [
      "src" => BASE . "public/images/manifest-screenshot-wide.png",
      "sizes" => "3024x1890",
      "type" => "image/png",
      "form_factor" => "wide",
    ],
  ],
];

echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
