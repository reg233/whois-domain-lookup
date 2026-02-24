<?php
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

if ($path === "/prices") {
  require_once __DIR__ . "/../src/prices.php";
} else if ($path === "/manifest") {
  require_once __DIR__ . "/../src/manifest.php";
} else if ($path === "/login") {
  require_once __DIR__ . "/../src/login.php";
} else {
  require_once __DIR__ . "/../src/index.php";
}
