<?php
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
  http_response_code(405);
  header("Allow: GET");
  die;
}

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../vendor/autoload.php";

spl_autoload_register(function ($class) {
  if (str_starts_with($class, "Parser")) {
    require_once __DIR__ . "/Parsers/$class.php";
  } else {
    require_once __DIR__ . "/$class.php";
  }
});

use Pdp\SyntaxError;
use Pdp\UnableToResolveDomain;

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

  if (filter_var($_GET["json"] ?? 0, FILTER_VALIDATE_BOOL)) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    echo json_encode(["code" => 1, "msg" => "Incorrect password.", "data" => null]);
  } else {
    $requestUri = $_SERVER["REQUEST_URI"];
    if ($requestUri === BASE) {
      header("Location: " . BASE . "login");
    } else {
      header("Location: " . BASE . "login?redirect=" . urlencode($requestUri));
    }
  }

  die;
}

function cleanDomain()
{
  $domain = htmlspecialchars($_GET["domain"] ?? "", ENT_QUOTES, "UTF-8");
  $domain = trim(preg_replace(["/\s+/", "/\.{2,}/"], ["", "."], $domain), ".");

  $parsedUrl = parse_url($domain);
  if ($parsedUrl["host"] ?? "") {
    $domain = $parsedUrl["host"];
  }

  return $domain;
}

function getDataSource()
{
  $whois = filter_var($_GET["whois"] ?? 0, FILTER_VALIDATE_BOOL);
  $rdap = filter_var($_GET["rdap"] ?? 0, FILTER_VALIDATE_BOOL);

  if (!$whois && !$rdap) {
    $whois = $rdap = true;
  }

  $dataSource = [];

  if ($whois) {
    $dataSource[] = "whois";
  }
  if ($rdap) {
    $dataSource[] = "rdap";
  }

  return $dataSource;
}

function generateRegistrarServerHref($dataSource, $server)
{
  $parsedUrl = parse_url($_SERVER["REQUEST_URI"]);

  parse_str($parsedUrl["query"] ?? "", $queryParams);

  $queryParams[$dataSource] = 1;
  $queryParams["$dataSource-server"] = $server;

  return $parsedUrl["path"] . "?" . http_build_query($queryParams);
}

checkPassword();

$domain = cleanDomain();

$dataSource = [];
$whoisData = null;
$rdapData = null;
$parser = new Parser("");
$error = null;

if ($domain) {
  $dataSource = getDataSource();

  try {
    $lookup = new Lookup($domain, $dataSource);
    $domain = $lookup->domain;
    $whoisData = $lookup->whoisData;
    $rdapData = $lookup->rdapData;
    $parser = $lookup->parser;
  } catch (Exception $e) {
    if ($e instanceof SyntaxError || $e instanceof UnableToResolveDomain) {
      $error = "'$domain' is not a valid domain";
    } else {
      $error = $e->getMessage();
    }
  }

  if (filter_var($_GET["json"] ?? 0, FILTER_VALIDATE_BOOL)) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");

    if ($error) {
      $value = ["code" => 1, "msg" => $error, "data" => null];
    } else {
      $value = ["code" => 0, "msg" => "Query successful", "data" => $parser];
    }

    $json = json_encode($value, JSON_UNESCAPED_UNICODE);

    if ($json === false) {
      $value = ["code" => 1, "msg" => json_last_error_msg(), "data" => null];
      echo json_encode($value, JSON_UNESCAPED_UNICODE);
    } else {
      echo $json;
    }

    die;
  }
}

$manifestHref = "manifest";
if ($_SERVER["QUERY_STRING"] ?? "") {
  $manifestHref .= "?" . htmlspecialchars($_SERVER["QUERY_STRING"], ENT_QUOTES, "UTF-8");
}
?>

<!doctype html>
<html lang="en-US">

<head>
  <base href="<?= BASE; ?>">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="theme-color" content="#eef6ff" media="(prefers-color-scheme: light)">
  <meta name="theme-color" content="#050a1a" media="(prefers-color-scheme: dark)">
  <meta name="description" content="<?= SITE_DESCRIPTION ?>">
  <meta name="keywords" content="<?= SITE_KEYWORDS ?>">
  <link rel="shortcut icon" href="public/favicon.ico">
  <link rel="icon" href="public/images/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="public/images/apple-icon-180.png">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2048-2732.jpg" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1668-2388.jpg" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1536-2048.jpg" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1640-2360.jpg" media="(device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1668-2224.jpg" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1620-2160.jpg" media="(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1488-2266.jpg" media="(device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1320-2868.jpg" media="(device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1206-2622.jpg" media="(device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1260-2736.jpg" media="(device-width: 420px) and (device-height: 912px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1290-2796.jpg" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1179-2556.jpg" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1170-2532.jpg" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1284-2778.jpg" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1125-2436.jpg" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1242-2688.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-828-1792.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1242-2208.jpg" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-750-1334.jpg" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-640-1136.jpg" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2732-2048.jpg" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2388-1668.jpg" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2048-1536.jpg" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2360-1640.jpg" media="(device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2224-1668.jpg" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2160-1620.jpg" media="(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2266-1488.jpg" media="(device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2868-1320.jpg" media="(device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2622-1206.jpg" media="(device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2736-1260.jpg" media="(device-width: 420px) and (device-height: 912px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2796-1290.jpg" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2556-1179.jpg" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2532-1170.jpg" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2778-1284.jpg" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2436-1125.jpg" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2688-1242.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1792-828.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2208-1242.jpg" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1334-750.jpg" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1136-640.jpg" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2048-2732.jpg" media="(prefers-color-scheme: dark) and (device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1668-2388.jpg" media="(prefers-color-scheme: dark) and (device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1536-2048.jpg" media="(prefers-color-scheme: dark) and (device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1640-2360.jpg" media="(prefers-color-scheme: dark) and (device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1668-2224.jpg" media="(prefers-color-scheme: dark) and (device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1620-2160.jpg" media="(prefers-color-scheme: dark) and (device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1488-2266.jpg" media="(prefers-color-scheme: dark) and (device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1320-2868.jpg" media="(prefers-color-scheme: dark) and (device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1206-2622.jpg" media="(prefers-color-scheme: dark) and (device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1260-2736.jpg" media="(prefers-color-scheme: dark) and (device-width: 420px) and (device-height: 912px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1290-2796.jpg" media="(prefers-color-scheme: dark) and (device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1179-2556.jpg" media="(prefers-color-scheme: dark) and (device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1170-2532.jpg" media="(prefers-color-scheme: dark) and (device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1284-2778.jpg" media="(prefers-color-scheme: dark) and (device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1125-2436.jpg" media="(prefers-color-scheme: dark) and (device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1242-2688.jpg" media="(prefers-color-scheme: dark) and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-828-1792.jpg" media="(prefers-color-scheme: dark) and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1242-2208.jpg" media="(prefers-color-scheme: dark) and (device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-750-1334.jpg" media="(prefers-color-scheme: dark) and (device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-640-1136.jpg" media="(prefers-color-scheme: dark) and (device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2732-2048.jpg" media="(prefers-color-scheme: dark) and (device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2388-1668.jpg" media="(prefers-color-scheme: dark) and (device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2048-1536.jpg" media="(prefers-color-scheme: dark) and (device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2360-1640.jpg" media="(prefers-color-scheme: dark) and (device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2224-1668.jpg" media="(prefers-color-scheme: dark) and (device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2160-1620.jpg" media="(prefers-color-scheme: dark) and (device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2266-1488.jpg" media="(prefers-color-scheme: dark) and (device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2868-1320.jpg" media="(prefers-color-scheme: dark) and (device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2622-1206.jpg" media="(prefers-color-scheme: dark) and (device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2736-1260.jpg" media="(prefers-color-scheme: dark) and (device-width: 420px) and (device-height: 912px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2796-1290.jpg" media="(prefers-color-scheme: dark) and (device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2556-1179.jpg" media="(prefers-color-scheme: dark) and (device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2532-1170.jpg" media="(prefers-color-scheme: dark) and (device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2778-1284.jpg" media="(prefers-color-scheme: dark) and (device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2436-1125.jpg" media="(prefers-color-scheme: dark) and (device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2688-1242.jpg" media="(prefers-color-scheme: dark) and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1792-828.jpg" media="(prefers-color-scheme: dark) and (device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-2208-1242.jpg" media="(prefers-color-scheme: dark) and (device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1334-750.jpg" media="(prefers-color-scheme: dark) and (device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-dark-1136-640.jpg" media="(prefers-color-scheme: dark) and (device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="manifest" href="<?= $manifestHref; ?>">
  <title><?= ($domain ? "$domain | " : "") . SITE_TITLE ?></title>
  <link rel="stylesheet" href="public/css/global.css">
  <link rel="stylesheet" href="public/css/index.css">
  <?php if ($rdapData): ?>
    <link rel="stylesheet" href="public/css/json-viewer.css">
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght,SOFT,WONK@72,600,50,1&family=JetBrains+Mono&display=swap">
  <?= CUSTOM_HEAD ?>
</head>

<body>
  <div class="root">
    <header>
      <h1>
        <?php if ($domain): ?>
          <a href="<?= BASE; ?>"><?= SITE_TITLE; ?></a>
        <?php else: ?>
          <?= SITE_TITLE; ?>
        <?php endif; ?>
      </h1>
      <form action="" id="form" method="get">
        <div class="input-box">
          <input autocapitalize="off" autocomplete="domain" autocorrect="off" <?= $domain ? "" : "autofocus"; ?> class="input" id="domain" inputmode="url" name="domain" placeholder="Enter a domain" required type="text" value="<?= $domain; ?>">
          <button class="input-clear" id="domain-clear" type="button" aria-label="Clear">
            <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
              <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
            </svg>
          </button>
        </div>
        <div class="toggles">
          <button class="toggle" id="toggle-whois" type="button" aria-active="<?= in_array("whois", $dataSource, true) ? "true" : "false" ?>" data-target-input="input-whois">
            <div class="toggle-indicator">
              <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
                <path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z" />
              </svg>
            </div>
            <span>WHOIS</span>
          </button>
          <button class="toggle" id="toggle-rdap" type="button" aria-active="<?= in_array("rdap", $dataSource, true) ? "true" : "false" ?>" data-target-input="input-rdap">
            <div class="toggle-indicator">
              <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
                <path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z" />
              </svg>
            </div>
            <span>RDAP</span>
          </button>
        </div>
        <button class="primary-button" id="search-button" data-loading="false">
          <span class="primary-button-label">Search</span>
          <span class="primary-button-loader" aria-hidden="true"></span>
        </button>
        <input id="input-whois" name="whois" type="hidden" value="<?= in_array("whois", $dataSource, true) ? "1" : "0" ?>">
        <input id="input-rdap" name="rdap" type="hidden" value="<?= in_array("rdap", $dataSource, true) ? "1" : "0" ?>">
      </form>
    </header>
    <?php if ($domain): ?>
      <main>
        <section class="message" id="message">
          <?php if ($error): ?>
            <span class="message-icon message-icon-error">
              <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
                <path d="M480-429 316-265q-11 11-25 10.5T266-266q-11-11-11-25.5t11-25.5l163-163-164-164q-11-11-10.5-25.5T266-695q11-11 25.5-11t25.5 11l163 164 164-164q11-11 25.5-11t25.5 11q11 11 11 25.5T695-644L531-480l164 164q11 11 11 25t-11 25q-11 11-25.5 11T644-266L480-429Z" />
              </svg>
            </span>
            <span><?= $error; ?></span>
          <?php elseif ($parser->unknown): ?>
            <span class="message-icon message-icon-unknown">
              <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
                <path d="M576-653q0-38-27-62.5T480-740q-26 0-47.5 11.5T395-696q-14 19-35.5 24t-40.5-6q-20-12-24-30.5t10-38.5q29-44 75.5-68.5T480-840q89 0 145 51.5T681-656q0 42-18.5 76.5T596-500q-32 29-43 47t-15 41q-4 23-19.5 37.5T482-360q-22 0-37-14.5T430-409q0-37 17.5-69.5T503-543q43-38 58-60t15-50Zm-96 509q-30 0-51-21t-21-51q0-30 21-51t51-21q30 0 51 21t21 51q0 30-21 51t-51 21Z" />
              </svg>
            </span>
            <span>&#39;<?= $domain; ?>&#39; is unknown.</span>
          <?php elseif ($parser->reserved): ?>
            <span class="message-icon message-icon-reserved">
              <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
                <path d="M480-96q-79 0-149-30t-122.5-82.5Q156-261 126-331T96-480q0-80 30-149.5t82.5-122Q261-804 331-834t149-30q80 0 149.5 30t122 82.5Q804-699 834-629.5T864-480q0 79-30 149t-82.5 122.5Q699-156 629.5-126T480-96Zm0-72q55 0 104-18t89-50L236-673q-32 40-50 89t-18 104q0 130 91 221t221 91Zm244-119q32-40 50-89t18-104q0-130-91-221t-221-91q-55 0-104 18t-89 50l437 437Z" />
              </svg>
            </span>
            <span>&#39;<?= $domain; ?>&#39; is reserved.</span>
          <?php elseif ($parser->registered): ?>
            <span class="message-icon message-icon-registered">
              <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
                <path d="m389-369 299-299q10.91-11 25.45-11Q728-679 739-668t11 25.58q0 14.58-10.61 25.19L415-292q-10.91 11-25.45 11Q375-281 364-292L221-435q-11-11-11-25.5t11-25.5q11-11 25.67-11 14.66 0 25.33 11l117 117Z" />
              </svg>
            </span>
            <span>
              <a href="<?= ($lookup->extension === "iana" ? "https://en.wikipedia.org/wiki/." : "http://") . $domain; ?>" rel="nofollow noopener noreferrer" target="_blank"><?= $domain; ?></a> is registered.
            </span>
            <?php if (
              ($parser->createdAgoSeconds && $parser->createdAgoSeconds < 7 * 24 * 60 * 60) ||
              (($parser->expiresInSeconds ?? -1) >= 0 && $parser->expiresInSeconds < 7 * 24 * 60 * 60) ||
              $parser->pendingDelete ||
              $parser->expiresInSeconds < 0 ||
              $parser->gracePeriod ||
              $parser->redemptionPeriod
            ): ?>
              <div class="message-tags">
                <?php if ($parser->createdAgoSeconds && $parser->createdAgoSeconds < 7 * 24 * 60 * 60): ?>
                  <span class="tag tag-green">New</span>
                <?php endif; ?>
                <?php if (($parser->expiresInSeconds ?? -1) >= 0 && $parser->expiresInSeconds < 7 * 24 * 60 * 60): ?>
                  <span class="tag tag-yellow">Expiring Soon</span>
                <?php endif; ?>
                <?php if ($parser->pendingDelete): ?>
                  <span class="tag tag-red">Pending Delete</span>
                <?php elseif ($parser->expiresInSeconds < 0): ?>
                  <span class="tag tag-red">Expired</span>
                <?php endif; ?>
                <?php if ($parser->gracePeriod): ?>
                  <span class="tag tag-yellow">Grace Period</span>
                <?php elseif ($parser->redemptionPeriod): ?>
                  <span class="tag tag-blue">Redemption Period</span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <span class="message-icon message-icon-unregistered">
              <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
                <path d="M479.79-672Q450-672 429-693.21t-21-51Q408-774 429.21-795t51-21Q510-816 531-794.79t21 51Q552-714 530.79-693t-51 21Zm.21 528q-25 0-42.5-17.5T420-204v-312q0-25 17.5-42.5T480-576q25 0 42.5 17.5T540-516v312q0 25-17.5 42.5T480-144Z" />
              </svg>
            </span>
            <span>&#39;<?= $domain; ?>&#39; is unregistered.</span>
          <?php endif; ?>
        </section>
        <?php if ($parser->registrar || $parser->registrarIANAId || $parser->registrarWHOISServer || $parser->registrarRDAPServer): ?>
          <section class="card">
            <p class="card-title">Registrar</p>
            <div class="card-items">
              <?php if ($parser->registrar): ?>
                <div class="card-item">
                  <div class="card-item-label">Name</div>
                  <div class="card-item-value">
                    <?php if ($parser->registrarURL): ?>
                      <a href="<?= $parser->registrarURL; ?>" rel="nofollow noopener noreferrer" target="_blank">
                        <?= $parser->registrar; ?>
                      </a>
                    <?php else: ?>
                      <?= $parser->registrar; ?>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>
              <?php if ($parser->registrarIANAId): ?>
                <div class="card-item">
                  <div class="card-item-label">IANA ID</div>
                  <div class="card-item-value">
                    <a href="https://client.rdap.org/?type=registrar&object=<?= $parser->registrarIANAId; ?>&follow-referral=0" rel="nofollow noopener noreferrer" target="_blank">
                      <?= $parser->registrarIANAId; ?>
                    </a>
                  </div>
                </div>
              <?php endif; ?>
              <?php if ($parser->registrarWHOISServer): ?>
                <div class="card-item">
                  <div class="card-item-label">WHOIS Server</div>
                  <div class="card-item-value">
                    <?php if ($lookup->extension === "iana"): ?>
                      <?= $parser->registrarWHOISServer; ?>
                    <?php elseif (preg_match("#^https?://#i", $parser->registrarWHOISServer)): ?>
                      <a href="<?= $parser->registrarWHOISServer; ?>" rel="nofollow noopener noreferrer" target="_blank">
                        <?= $parser->registrarWHOISServer; ?>
                      </a>
                    <?php else: ?>
                      <a href="<?= generateRegistrarServerHref("whois", $parser->registrarWHOISServer); ?>">
                        <?= $parser->registrarWHOISServer; ?>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>
              <?php if ($parser->registrarRDAPServer): ?>
                <div class="card-item">
                  <div class="card-item-label">RDAP Server</div>
                  <div class="card-item-value">
                    <?php if ($lookup->extension === "iana"): ?>
                      <a href="<?= $parser->registrarRDAPServer; ?>" rel="nofollow noopener noreferrer" target="_blank">
                        <?= $parser->registrarRDAPServer; ?>
                      </a>
                    <?php else: ?>
                      <a href="<?= generateRegistrarServerHref("rdap", $parser->registrarRDAPServer); ?>">
                        <?= $parser->registrarRDAPServer; ?>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </section>
        <?php endif; ?>
        <?php if ($parser->creationDate || $parser->expirationDate || $parser->updatedDate || $parser->availableDate): ?>
          <section class="card">
            <p class="card-title">Dates</p>
            <div class="card-items">
              <?php if ($parser->creationDate): ?>
                <div class="card-item">
                  <div class="card-item-label">Creation Date</div>
                  <div class="card-item-value">
                    <?php if ($parser->creationDateISO8601 === null): ?>
                      <span><?= $parser->creationDate; ?></span>
                    <?php else: ?>
                      <span id="creation-date" data-iso8601="<?= $parser->creationDateISO8601; ?>">
                        <?= $parser->creationDate; ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <?php if ($parser->createdAgo): ?>
                    <div class="card-item-value-tertiary">
                      <span id="createdAgo" data-seconds="<?= $parser->createdAgoSeconds; ?>">
                        <?= $parser->createdAgo; ?> ago
                      </span>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
              <?php if ($parser->expirationDate): ?>
                <div class="card-item">
                  <div class="card-item-label">Expiration Date</div>
                  <div class="card-item-value">
                    <?php if ($parser->expirationDateISO8601 === null): ?>
                      <span><?= $parser->expirationDate; ?></span>
                    <?php else: ?>
                      <span id="expiration-date" data-iso8601="<?= $parser->expirationDateISO8601; ?>">
                        <?= $parser->expirationDate; ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <?php if ($parser->expiresIn): ?>
                    <div class="card-item-value-tertiary">
                      <span id="expiresIn" data-seconds="<?= $parser->expiresInSeconds; ?>">
                        <?= $parser->expiresIn; ?> remaining
                      </span>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
              <?php if ($parser->updatedDate): ?>
                <div class="card-item">
                  <div class="card-item-label">Updated Date</div>
                  <div class="card-item-value">
                    <?php if ($parser->updatedDateISO8601 === null): ?>
                      <span><?= $parser->updatedDate; ?></span>
                    <?php else: ?>
                      <span id="updated-date" data-iso8601="<?= $parser->updatedDateISO8601; ?>">
                        <?= $parser->updatedDate; ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <?php if ($parser->updatedAgo): ?>
                    <div class="card-item-value-tertiary">
                      <span id="updatedAgo" data-seconds="<?= $parser->updatedAgoSeconds; ?>">
                        <?= $parser->updatedAgo; ?> ago
                      </span>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
              <?php if ($parser->availableDate): ?>
                <div class="card-item">
                  <div class="card-item-label">Available Date</div>
                  <div class="card-item-value">
                    <?php if ($parser->availableDateISO8601 === null): ?>
                      <span><?= $parser->availableDate; ?></span>
                    <?php else: ?>
                      <span id="available-date" data-iso8601="<?= $parser->availableDateISO8601; ?>">
                        <?= $parser->availableDate; ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <?php if ($parser->availableIn): ?>
                    <div class="card-item-value-tertiary">
                      <span id="availableIn" data-seconds="<?= $parser->availableInSeconds; ?>">
                        <?= $parser->availableIn; ?> remaining
                      </span>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </section>
        <?php endif; ?>
        <?php if ($parser->status || $parser->nameServers): ?>
          <section class="card">
            <p class="card-title">Status and DNS</p>
            <div class="card-items">
              <?php if ($parser->status): ?>
                <div class="card-item">
                  <div class="card-item-label">Status</div>
                  <div class="card-item-value card-item-values">
                    <?php foreach ($parser->status as $status): ?>
                      <?php if ($status["url"]): ?>
                        <a class="chip chip-link" href="<?= $status["url"]; ?>" rel="nofollow noopener noreferrer" target="_blank">
                          <?= $status["text"]; ?>
                        </a>
                      <?php else: ?>
                        <span class="chip"><?= $status["text"]; ?></span>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
              <?php if ($parser->nameServers): ?>
                <div class="card-item">
                  <div class="card-item-label">Name Servers</div>
                  <div class="card-item-value card-item-values">
                    <?php foreach ($parser->nameServers as $nameServer): ?>
                      <span class="chip"><?= $nameServer; ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
              <?php if ($parser->dnssecSigned !== null): ?>
                <div class="card-item">
                  <div class="card-item-label">DNSSEC</div>
                  <div class="card-item-value">
                    <?php if ($parser->dnssecSigned): ?>
                      <a href="https://dnsviz.net/d/<?= $domain; ?>/dnssec/" rel="nofollow noopener noreferrer" target="_blank">
                        Signed
                      </a>
                    <?php else: ?>
                      <span>Unsigned</span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </section>
        <?php endif; ?>
        <?php if ($whoisData || $rdapData): ?>
          <section class="card card-raw-data" id="card-raw-data">
            <div class="raw-data-head" id="raw-data-head">
              <?php if ($whoisData && $rdapData): ?>
                <div class="raw-data-tabs">
                  <button class="raw-data-tab raw-data-tab-active" id="raw-data-tab-whois">WHOIS</button>
                  <button class="raw-data-tab" id="raw-data-tab-rdap">RDAP</button>
                </div>
              <?php else: ?>
                <div class="raw-data-head-title"><?= $whoisData ? "WHOIS" : "RDAP"; ?></div>
              <?php endif; ?>
              <button class="copy-button" id="copy-button" aria-label="Copy raw data">
                <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" class="copy-button-icon-copy" aria-hidden="true">
                  <path d="M360-240q-29.7 0-50.85-21.15Q288-282.3 288-312v-480q0-29.7 21.15-50.85Q330.3-864 360-864h384q29.7 0 50.85 21.15Q816-821.7 816-792v480q0 29.7-21.15 50.85Q773.7-240 744-240H360Zm0-72h384v-480H360v480ZM216-96q-29.7 0-50.85-21.15Q144-138.3 144-168v-516q0-15.3 10.29-25.65Q164.58-720 179.79-720t25.71 10.35Q216-699.3 216-684v516h420q15.3 0 25.65 10.29Q672-147.42 672-132.21t-10.35 25.71Q651.3-96 636-96H216Zm144-216v-480 480Z" />
                </svg>
                <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" class="copy-button-icon-check" aria-hidden="true">
                  <path d="m389-369 299-299q10.91-11 25.45-11Q728-679 739-668t11 25.58q0 14.58-10.61 25.19L415-292q-10.91 11-25.45 11Q375-281 364-292L221-435q-11-11-11-25.5t11-25.5q11-11 25.67-11 14.66 0 25.33 11l117 117Z" />
                </svg>
              </button>
            </div>
            <?php if ($whoisData): ?>
              <pre class="raw-data-whois" id="raw-data-whois"><code><?= htmlspecialchars($whoisData, ENT_QUOTES, "UTF-8"); ?></code></pre>
            <?php endif; ?>
            <?php if ($rdapData): ?>
              <pre class="raw-data-rdap" id="raw-data-rdap"><code><?= $rdapData; ?></code></pre>
            <?php endif; ?>
          </section>
        <?php endif; ?>
      </main>
    <?php endif; ?>
    <?php require_once __DIR__ . "/footer.php"; ?>
  </div>
  <button class="back-to-top" id="back-to-top" aria-label="Back to top">
    <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
      <path d="M200-760q-17 0-28.5-11.5T160-800q0-17 11.5-28.5T200-840h560q17 0 28.5 11.5T800-800q0 17-11.5 28.5T760-760H200Zm280 640q-17 0-28.5-11.5T440-160v-368l-76 76q-11 11-28 11t-28-11q-11-11-11-28t11-28l144-144q6-6 13-8.5t15-2.5q8 0 15 2.5t13 8.5l144 144q11 11 11 28t-11 28q-11 11-28 11t-28-11l-76-76v368q0 17-11.5 28.5T480-120Z" />
    </svg>
  </button>
  <script>
    window.addEventListener("DOMContentLoaded", () => {
      const domainElement = document.getElementById("domain");
      const domainClearElement = document.getElementById("domain-clear");

      if (domainElement.value) {
        domainClearElement.classList.add("visible");
      }

      domainElement.addEventListener("input", (e) => {
        if (e.target.value) {
          domainClearElement.classList.add("visible");
        } else {
          domainClearElement.classList.remove("visible");
        }
      });
      domainElement.addEventListener("paste", (e) => {
        try {
          const pasteData = e.clipboardData.getData("text");
          const hostname = new URL(pasteData).hostname;

          e.preventDefault();

          if (document.queryCommandSupported("insertText")) {
            domainElement.select();
            document.execCommand("insertText", false, hostname);
          } else {
            const end = domainElement.value.length;
            domainElement.setRangeText(hostname, 0, end, "end");
            domainElement.dispatchEvent(new Event("input", {
              bubbles: true,
            }));
          }
        } catch {}
      });

      domainClearElement.addEventListener("click", () => {
        domainElement.focus();
        domainElement.select();
        if (document.queryCommandSupported("delete")) {
          document.execCommand("delete", false);
        } else {
          domainElement.setRangeText("");
          domainElement.dispatchEvent(new Event("input", {
            bubbles: true,
          }));
        }
      });

      const toggles = [
        document.getElementById("toggle-whois"),
        document.getElementById("toggle-rdap"),
      ];

      toggles.forEach((toggle) => {
        toggle.addEventListener("click", () => {
          const active = toggle.getAttribute("aria-active") === "true";
          const nextActive = `${!active}`;

          toggle.setAttribute("aria-active", nextActive);

          const targetInput = document.getElementById(toggle.dataset.targetInput);
          targetInput.value = nextActive === "true" ? "1" : "0";
        });
      });

      const searchParams = new URLSearchParams(window.location.search);
      if (searchParams.get("domain")) {
        toggles.forEach((toggle) => {
          const active = toggle.getAttribute("aria-active") === "true";
          localStorage.setItem(toggle.id, `${+active}`);
        });
      } else {
        const whoisValue = localStorage.getItem("toggle-whois") || "0";
        const rdapValue = localStorage.getItem("toggle-rdap") || "0";

        toggles.forEach((toggle) => {
          const targetInput = document.getElementById(toggle.dataset.targetInput);

          if (!+whoisValue && !+rdapValue) {
            toggle.setAttribute("aria-active", "true");
            targetInput.value = "1";
          } else {
            const active = `${!!+localStorage.getItem(toggle.id)}`;

            toggle.setAttribute("aria-active", active);
            targetInput.value = active === "true" ? "1" : "0";
          }
        });
      }

      const form = document.getElementById("form");
      const searchButton = document.getElementById("search-button");

      form.addEventListener("submit", () => {
        searchButton.disabled = true;
        searchButton.dataset.loading = "true";
      });

      window.addEventListener("pageshow", (e) => {
        if (e.persisted) {
          searchButton.disabled = false;
          searchButton.dataset.loading = "false";
        }
      });

      const backToTop = document.getElementById("back-to-top");
      backToTop.addEventListener("click", () => {
        const body = document.body;
        const bodyStyle = window.getComputedStyle(body);
        const scrollElement = bodyStyle.overflow === "auto" ? body : window;

        scrollElement.scrollTo({
          behavior: "smooth",
          top: 0,
        });
      });

      const messageElement = document.getElementById("message");
      if (messageElement) {
        const observer = new IntersectionObserver(([e]) => {
          if (e.boundingClientRect.top < 0) {
            backToTop.classList.add("visible");
          } else {
            backToTop.classList.remove("visible");
          }
        }, {
          threshold: [1],
        });
        observer.observe(messageElement);
      }
    });
  </script>
  <?php if ($whoisData || $rdapData): ?>
    <?php if ($rdapData): ?>
      <script src="public/js/json-viewer.js" defer></script>
    <?php endif; ?>
    <script src="public/js/linkify.min.js" defer></script>
    <script src="public/js/linkify-html.min.js" defer></script>
    <script>
      window.addEventListener("DOMContentLoaded", () => {
        const updateDateElementText = (elementId) => {
          const element = document.getElementById(elementId);
          if (element) {
            const iso8601 = element.dataset.iso8601;
            if (iso8601) {
              if (iso8601.endsWith("Z")) {
                const date = new Date(iso8601);

                const year = date.getFullYear();
                const month = `${date.getMonth() + 1}`.padStart(2, "0");
                const day = `${date.getDate()}`.padStart(2, "0");
                const hours = `${date.getHours()}`.padStart(2, "0");
                const minutes = `${date.getMinutes()}`.padStart(2, "0");
                const seconds = `${date.getSeconds()}`.padStart(2, "0");

                element.innerText = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

                const timezoneOffset = date.getTimezoneOffset();

                const offsetHours = -Math.trunc(timezoneOffset / 60);
                const sign = offsetHours >= 0 ? "+" : "-";
                const offsetMinutes = Math.abs(timezoneOffset % 60);
                const minutesStr = offsetMinutes ? `:${offsetMinutes}` : "";

                const timezoneElement = document.createElement("span");
                timezoneElement.className = "card-item-value-secondary";
                timezoneElement.innerText = `UTC${sign}${Math.abs(offsetHours)}${minutesStr}`;

                element.parentElement.appendChild(timezoneElement);
              } else {
                element.innerText = iso8601;
              }
            }
          }
        };

        updateDateElementText("creation-date");
        updateDateElementText("expiration-date");
        updateDateElementText("updated-date");
        updateDateElementText("available-date");

        const updateDaysElementText = (elementId) => {
          const element = document.getElementById(elementId);
          if (element) {
            const seconds = element.dataset.seconds;
            if (seconds) {
              let days = Math.trunc(seconds / 24 / 60 / 60);
              if (seconds < 0 && days === 0) {
                days = "-0";
              }

              element.innerText = `${element.innerText} (${days} days)`;
            }
          }
        }

        updateDaysElementText("createdAgo");
        updateDaysElementText("expiresIn");
        updateDaysElementText("updatedAgo");
        updateDaysElementText("availableIn");

        const cardRawData = document.getElementById("card-raw-data");
        const rawDataHead = document.getElementById("raw-data-head");
        const rawDataTabWHOIS = document.getElementById("raw-data-tab-whois");
        const rawDataTabRDAP = document.getElementById("raw-data-tab-rdap");
        const rawDataWHOIS = document.getElementById("raw-data-whois");
        const rawDataRDAP = document.getElementById("raw-data-rdap");

        if (rawDataHead) {
          const observer = new IntersectionObserver(([e]) => {
            if (e.boundingClientRect.top < 0) {
              rawDataHead.style.borderRadius = "0";
            } else {
              rawDataHead.style.borderRadius = null;
            }
          }, {
            threshold: [1],
          });
          observer.observe(rawDataHead);
        }

        if (rawDataTabWHOIS && rawDataTabRDAP) {
          rawDataTabWHOIS.addEventListener("click", () => {
            if (!rawDataTabWHOIS.classList.contains("raw-data-tab-active")) {
              rawDataTabWHOIS.classList.add("raw-data-tab-active");
              rawDataWHOIS.style.display = "block";
              rawDataTabRDAP.classList.remove("raw-data-tab-active");
              rawDataRDAP.style.display = "none";
            }

            cardRawData.scrollIntoView({
              behavior: "smooth"
            });
          });
          rawDataTabRDAP.addEventListener("click", () => {
            if (!rawDataTabRDAP.classList.contains("raw-data-tab-active")) {
              rawDataTabWHOIS.classList.remove("raw-data-tab-active");
              rawDataWHOIS.style.display = "none";
              rawDataTabRDAP.classList.add("raw-data-tab-active");
              rawDataRDAP.style.display = "block";
            }

            cardRawData.scrollIntoView({
              behavior: "smooth"
            });
          });
        }

        const copyToClipboard = (data) => {
          if (navigator.clipboard) {
            navigator.clipboard.writeText(data);
          } else {
            const fakeElement = document.createElement("textarea");
            fakeElement.style.border = "0";
            fakeElement.style.fontSize = "12pt";
            fakeElement.style.margin = "0";
            fakeElement.style.padding = "0";
            fakeElement.style.position = "absolute";

            const isRTL = document.documentElement.getAttribute("dir") === "rtl";
            fakeElement.style[isRTL ? "right" : "left"] = "-9999px";
            const yPosition = window.pageYOffset || document.documentElement.scrollTop;
            fakeElement.style.top = `${yPosition}px`;

            fakeElement.setAttribute("readonly", "");
            fakeElement.value = data;

            document.body.appendChild(fakeElement);

            fakeElement.select();
            fakeElement.setSelectionRange(0, fakeElement.value.length);

            document.execCommand("copy");

            fakeElement.remove();
          }
        };

        const copyButton = document.getElementById("copy-button");
        if (copyButton) {
          let timeoutId;

          copyButton.addEventListener("click", () => {
            let data;

            if (rawDataWHOIS && getComputedStyle(rawDataWHOIS).display === "block") {
              data = rawDataWHOIS.innerText;
            } else if (rawDataRDAP && getComputedStyle(rawDataRDAP).display === "block") {
              data = JSON.stringify(JSON.parse(rdapData), null, 2);
            }

            if (!data) {
              return;
            }

            if (timeoutId) {
              clearTimeout(timeoutId);
              timeoutId = null;
            }

            copyToClipboard(data);
            copyButton.dataset.copied = "true";
            timeoutId = setTimeout(() => {
              copyButton.dataset.copied = "false";
            }, 2333);
          });
        }

        const rdapData = <?= json_encode($rdapData); ?>;

        const linkifyRawData = (element) => {
          if (element) {
            element.innerHTML = linkifyHtml(element.innerHTML, {
              rel: "nofollow noopener noreferrer",
              target: "_blank",
              validate: {
                url: (value) => /^https?:\/\//.test(value),
              },
            });
          }
        };

        if (rawDataWHOIS) {
          linkifyRawData(rawDataWHOIS);
        }
        if (rawDataRDAP) {
          setupJSONViewer(rawDataRDAP, rdapData);
          linkifyRawData(rawDataRDAP);
        }
      });
    </script>
  <?php endif; ?>
  <?= CUSTOM_SCRIPT ?>
</body>

</html>