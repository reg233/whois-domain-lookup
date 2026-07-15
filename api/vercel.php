<?php

declare(strict_types=1);

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$filename = match ($path) {
  "/dns-records" => "dns-records.php",
  "/manifest" => "manifest.php",
  "/login" => "login.php",
  default => "index.php",
};

require_once __DIR__ . "/../src/$filename";
