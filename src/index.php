<?php
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
  http_response_code(405);
  header("Allow: GET");
  die;
}

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/utils.php";

spl_autoload_register(function ($class) {
  if (str_starts_with($class, "Parser")) {
    require_once __DIR__ . "/Parsers/$class.php";
  } else {
    require_once __DIR__ . "/$class.php";
  }
});

use Pdp\SyntaxError;
use Pdp\UnableToResolveDomain;

function checkPassword($echoJSON)
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

  if ($echoJSON) {
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
  $domain = trim($_GET["domain"] ?? "");

  if (!$domain) {
    return "";
  }

  $domain = htmlspecialchars($domain, ENT_QUOTES, "UTF-8");
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

function generateServerHref($path, $query, $dataSource, $server)
{
  parse_str($query, $queryParams);

  $queryParams[$dataSource] = 1;
  $queryParams["$dataSource-server"] = $server;

  return $path . "?" . http_build_query($queryParams);
}

$echoJSON = filter_var($_GET["json"] ?? 0, FILTER_VALIDATE_BOOL);
if ($echoJSON) {
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json");
}

checkPassword($echoJSON);

$domain = cleanDomain();
$dataSource = [];

$isIANA = false;
$whoisData = null;
$rdapData = null;
$parser = new Parser("");
$error = null;

if ($domain) {
  $dataSource = getDataSource();

  try {
    $lookup = new Lookup($domain, $dataSource);
    $domain = $lookup->domain;
    $isIANA = $lookup->extension === "iana";
    $whoisData = $lookup->whoisData;
    $rdapData = $lookup->rdapData;
    $parser = $lookup->parser;
  } catch (Exception $e) {
    if ($e instanceof SyntaxError || $e instanceof UnableToResolveDomain) {
      $error = "'$domain' is not a valid domain.";
    } else {
      $error = rtrim($e->getMessage(), ".") . ".";
    }
  }

  if ($echoJSON) {
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
} else if ($echoJSON) {
  echo json_encode(
    ["code" => 1, "msg" => "The 'domain' parameter is required.", "data" => null],
    JSON_UNESCAPED_UNICODE,
  );
  die;
}

$origin = getProtocol() . $_SERVER["HTTP_HOST"];

$parsedUrl = parse_url($_SERVER["REQUEST_URI"]);

$path = $parsedUrl["path"] ?? "";
$query = $parsedUrl["query"] ?? "";

$requestUri = $path;
$manifestHref = "manifest";

if ($query) {
  parse_str($query, $queryParams);

  $filteredParams = array_intersect_key(
    $queryParams,
    array_flip(["domain", "whois", "rdap", "whois-server", "rdap-server"]),
  );

  $query = http_build_query($filteredParams);

  $requestUri .= "?$query";
  $manifestHref .= "?$query";
}

$title = ($domain ? "$domain | " : "") . SITE_TITLE;
$ogUrl = $origin . $requestUri;
$ogImage = $origin . BASE . "public/images/og.png";
?>

<!doctype html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <base href="<?= BASE; ?>">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="mobile-web-app-capable" content="yes">
  <link rel="manifest" href="<?= htmlspecialchars($manifestHref, ENT_QUOTES, "UTF-8"); ?>">
  <title><?= $title; ?></title>
  <meta name="description" content="<?= SITE_DESCRIPTION; ?>">
  <link rel="canonical" href="<?= htmlspecialchars($ogUrl, ENT_QUOTES, "UTF-8"); ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= htmlspecialchars($ogUrl, ENT_QUOTES, "UTF-8"); ?>">
  <meta property="og:title" content="<?= $title; ?>">
  <meta property="og:description" content="<?= SITE_DESCRIPTION; ?>">
  <meta property="og:image" content="<?= $ogImage; ?>">
  <meta property="og:image:alt" content="<?= SITE_TITLE; ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:site_name" content="<?= SITE_TITLE; ?>">
  <meta property="og:locale" content="en_US">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= $title; ?>">
  <meta name="twitter:description" content="<?= SITE_DESCRIPTION; ?>">
  <meta name="twitter:image" content="<?= $ogImage; ?>">
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
  <link rel="stylesheet" href="public/css/global.css?v=<?= VERSION; ?>">
  <link rel="stylesheet" href="public/css/tippy.css?v=<?= VERSION; ?>">
  <link rel="stylesheet" href="public/css/index.css?v=<?= VERSION; ?>">
  <?php if ($rdapData): ?>
    <link rel="stylesheet" href="public/css/json-viewer.css?v=<?= VERSION; ?>">
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght,SOFT,WONK@72,600,50,1&family=JetBrains+Mono&display=swap">
  <script src="public/js/theme.js?v=<?= VERSION; ?>"></script>
  <?= CUSTOM_HEAD; ?>
</head>

<body>
  <!-- Set app bar color for safari 26+ -->
  <!-- https://github.com/andesco/safari-color-tinting -->
  <!-- https://github.com/klmkyo/ios-safari-restore-meta-theme-color -->
  <div class="safari-26-app-bar-color" aria-hidden="true"></div>
  <div class="root">
    <header>
      <div class="theme-switcher-container">
        <button class="theme-switcher" id="theme-switcher" aria-label="Switch theme">
          <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
            <path d="M480-80q-82 0-155-31.5t-127.5-86Q143-252 111.5-325T80-480q0-83 32.5-156t88-127Q256-817 330-848.5T488-880q80 0 151 27.5t124.5 76q53.5 48.5 85 115T880-518q0 115-70 176.5T640-280h-74q-9 0-12.5 5t-3.5 11q0 12 15 34.5t15 51.5q0 50-27.5 74T480-80Zm0-400Zm-220 40q26 0 43-17t17-43q0-26-17-43t-43-17q-26 0-43 17t-17 43q0 26 17 43t43 17Zm120-160q26 0 43-17t17-43q0-26-17-43t-43-17q-26 0-43 17t-17 43q0 26 17 43t43 17Zm200 0q26 0 43-17t17-43q0-26-17-43t-43-17q-26 0-43 17t-17 43q0 26 17 43t43 17Zm120 160q26 0 43-17t17-43q0-26-17-43t-43-17q-26 0-43 17t-17 43q0 26 17 43t43 17ZM480-160q9 0 14.5-5t5.5-13q0-14-15-33t-15-57q0-42 29-67t71-25h70q66 0 113-38.5T800-518q0-121-92.5-201.5T488-800q-136 0-232 93t-96 227q0 133 93.5 226.5T480-160Z" />
          </svg>
        </button>
        <div class="theme-switcher-content" id="theme-switcher-content">
          <button class="theme-button" id="theme-button-automatic">
            <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" class="theme-button-icon" aria-hidden="true">
              <path d="M393.49-398H567l31.02 87.41q3.87 10.8 12.6 16.7Q619.34-288 630-288q19 0 29.5-15.25 10.5-15.24 3.5-31.96L526.96-697.18Q523-708 513.5-714t-20.81-6h-26.38q-11.31 0-20.81 6.23Q436-707.54 432-697L297-335q-6 17 4.09 32t28.34 15q10.57 0 19.57-6.5t12.94-17.28L393.49-398ZM415-461l63.06-179H482l63 179H415Zm65.28 365Q401-96 331-126t-122.5-82.5Q156-261 126-330.96t-30-149.5Q96-560 126-629.5q30-69.5 82.5-122T330.96-834q69.96-30 149.5-30t149.04 30q69.5 30 122 82.5T834-629.28q30 69.73 30 149Q864-401 834-331t-82.5 122.5Q699-156 629.28-126q-69.73 30-149 30ZM480-480Zm.23 312Q610-168 701-259.23t91-221Q792-610 700.77-701t-221-91Q350-792 259-700.77t-91 221Q168-350 259.23-259t221 91Z" />
            </svg>
            <span class="theme-button-label">Automatic</span>
          </button>
          <button class="theme-button" id="theme-button-light" data-theme="light">
            <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" class="theme-button-icon" aria-hidden="true">
              <path d="M480-360q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Zm-.23 72Q400-288 344-344.23q-56-56.22-56-136Q288-560 344.23-616q56.22-56 136-56Q560-672 616-615.77q56 56.22 56 136Q672-400 615.77-344q-56.22 56-136 56ZM84-444q-15.3 0-25.65-10.29Q48-464.58 48-479.79t10.35-25.71Q68.7-516 84-516h96q15.3 0 25.65 10.29Q216-495.42 216-480.21t-10.35 25.71Q195.3-444 180-444H84Zm696 0q-15.3 0-25.65-10.29Q744-464.58 744-479.79t10.35-25.71Q764.7-516 780-516h96q15.3 0 25.65 10.29Q912-495.42 912-480.21t-10.35 25.71Q891.3-444 876-444h-96ZM480.21-744q-15.21 0-25.71-10.35T444-780v-96q0-15.3 10.29-25.65Q464.58-912 479.79-912t25.71 10.35Q516-891.3 516-876v96q0 15.3-10.29 25.65Q495.42-744 480.21-744Zm0 696Q465-48 454.5-58.35T444-84v-96q0-15.3 10.29-25.65Q464.58-216 479.79-216t25.71 10.35Q516-195.3 516-180v96q0 15.3-10.29 25.65Q495.42-48 480.21-48ZM242-667l-50-51q-11-10-11-24.5t11-25.5q10.43-11 25.22-11Q232-779 242-768l51 50q11 10.94 11 25.53 0 14.59-11 25.53Q283-656 268-656t-26-11Zm476 475-51-50q-11-10.67-11-25.33Q656-282 667-293q10-11 25-11t26 11l50 51q11 10 11 24.5T768.48-192Q757-181 743-181t-25-11Zm-51.06-475Q656-677 656-692t11-26l51-50q11-11 25-10.5t25 10.54Q779-757 779-743t-11 25l-50 51q-10.94 11-25.53 11-14.59 0-25.53-11ZM192-192q-11-10.43-11-25.22Q181-232 192-242l50-51q10.67-10 25.33-10Q282-303 293-293q11 11 10.54 25.67-.46 14.66-10.54 25.33l-51 50q-10 11-24.5 11T192-192Zm288-288Z" />
            </svg>
            <span class="theme-button-label">Light</span>
          </button>
          <button class="theme-button" id="theme-button-dark" data-theme="dark">
            <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" class="theme-button-icon" aria-hidden="true">
              <path d="M480-144q-140 0-238-98t-98-238.03Q144-608 228-703t212.1-111.11Q452-816 461.5-810q9.5 6 14.58 15 5.07 9 5.5 21 .42 12-6.58 23-14 23-21.5 49.5T446-648q0 85 58.5 143.5T648-446q26.96 0 53.48-7.5Q728-461 751-475q11-7 22.5-6t20.03 6q9.47 5 15.47 14.5t4 21.5q-14 128-109.5 211.5T480-144Zm-1-72q82.92 0 148.88-46.01 65.97-46 96.12-119.99-20 5-39 8t-38 3q-114.27 0-194.64-80.48Q372-531.96 372-646.39q0-18.61 3-37.61t8-39q-74 30-120 95.93t-46 148.81Q217-369 293.73-292.5 370.46-216 479-216Zm-8-254Z" />
            </svg>
            <span class="theme-button-label">Dark</span>
          </button>
        </div>
      </div>
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
          <button class="toggle" id="toggle-whois" type="button" aria-active="<?= in_array("whois", $dataSource, true) ? "true" : "false"; ?>">
            <div class="toggle-indicator">
              <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
                <path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z" />
              </svg>
            </div>
            <span>WHOIS</span>
          </button>
          <button class="toggle" id="toggle-rdap" type="button" aria-active="<?= in_array("rdap", $dataSource, true) ? "true" : "false"; ?>">
            <div class="toggle-indicator">
              <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
                <path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z" />
              </svg>
            </div>
            <span>RDAP</span>
          </button>
        </div>
        <button class="primary-button" id="search-button" type="submit" data-loading="false">
          <span class="primary-button-label">Search</span>
          <span class="loader primary-button-loader" aria-hidden="true"></span>
        </button>
        <input id="input-whois" name="whois" type="hidden" value="<?= in_array("whois", $dataSource, true) ? "1" : "0"; ?>">
        <input id="input-rdap" name="rdap" type="hidden" value="<?= in_array("rdap", $dataSource, true) ? "1" : "0"; ?>">
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
              <a href="<?= ($isIANA ? "https://en.wikipedia.org/wiki/." : "http://") . $domain; ?>" rel="nofollow noopener noreferrer" target="_blank"><?= $domain; ?></a><?= $parser->domain ? "" : " ❓"; ?> is registered.
            </span>
            <?php if (
              ($parser->createdAgoSeconds && $parser->createdAgoSeconds < 7 * 24 * 60 * 60) ||
              (($parser->expiresInSeconds ?? -1) >= 0 && $parser->expiresInSeconds < 7 * 24 * 60 * 60) ||
              $parser->pendingDelete ||
              $parser->expiresInSeconds < 0 ||
              $parser->gracePeriod ||
              $parser->redemptionPeriod ||
              $parser->hold ||
              $parser->inactive
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
                  <span class="tag tag-red">Redemption Period</span>
                <?php endif; ?>
                <?php if ($parser->hold): ?>
                  <span class="tag tag-gray">Hold</span>
                <?php endif; ?>
                <?php if ($parser->inactive): ?>
                  <span class="tag tag-gray">Inactive</span>
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
        <?php if ($parser->registryWebsite || $parser->registryWHOISServer || $parser->registryRDAPServer): ?>
          <section class="card">
            <p class="card-title">Registry</p>
            <div class="card-items">
              <?php if ($parser->registryWebsite): ?>
                <div class="card-item">
                  <div class="card-item-label">Website</div>
                  <div class="card-item-value break-all">
                    <a href="<?= $parser->registryWebsite; ?>" rel="nofollow noopener noreferrer" target="_blank">
                      <?= $parser->registryWebsite; ?>
                    </a>
                  </div>
                </div>
              <?php endif; ?>
              <?php if ($parser->registryWHOISServer): ?>
                <div class="card-item">
                  <div class="card-item-label">WHOIS Server</div>
                  <div class="card-item-value break-all">
                    <?php if ($isIANA): ?>
                      <?= $parser->registryWHOISServer; ?>
                    <?php elseif (preg_match("#^https?://#i", $parser->registryWHOISServer)): ?>
                      <a href="<?= $parser->registryWHOISServer; ?>" rel="nofollow noopener noreferrer" target="_blank">
                        <?= $parser->registryWHOISServer; ?>
                      </a>
                    <?php else: ?>
                      <a href="<?= generateServerHref($path, $query, "whois", $parser->registryWHOISServer); ?>">
                        <?= $parser->registryWHOISServer; ?>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>
              <?php if ($parser->registryRDAPServer): ?>
                <div class="card-item">
                  <div class="card-item-label">RDAP Server</div>
                  <div class="card-item-value break-all">
                    <?php if ($isIANA): ?>
                      <a href="<?= $parser->registryRDAPServer; ?>" rel="nofollow noopener noreferrer" target="_blank">
                        <?= $parser->registryRDAPServer; ?>
                      </a>
                    <?php else: ?>
                      <a href="<?= generateServerHref($path, $query, "rdap", $parser->registryRDAPServer); ?>">
                        <?= $parser->registryRDAPServer; ?>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </section>
        <?php endif; ?>
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
                  <div class="card-item-value break-all">
                    <?php if (preg_match("#^https?://#i", $parser->registrarWHOISServer)): ?>
                      <a href="<?= $parser->registrarWHOISServer; ?>" rel="nofollow noopener noreferrer" target="_blank">
                        <?= $parser->registrarWHOISServer; ?>
                      </a>
                    <?php else: ?>
                      <a href="<?= generateServerHref($path, $query, "whois", $parser->registrarWHOISServer); ?>">
                        <?= $parser->registrarWHOISServer; ?>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>
              <?php if ($parser->registrarRDAPServer): ?>
                <div class="card-item">
                  <div class="card-item-label">RDAP Server</div>
                  <div class="card-item-value break-all">
                    <a href="<?= generateServerHref($path, $query, "rdap", $parser->registrarRDAPServer); ?>">
                      <?= $parser->registrarRDAPServer; ?>
                    </a>
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
        <?php if ($parser->registered): ?>
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
              <div class="card-item">
                <div class="card-item-label">DNS Records</div>
                <div class="card-item-value">
                  <button class="link-button" id="dns-records-view">View</button>
                </div>
              </div>
            </div>
          </section>
        <?php endif; ?>
        <?php if ($whoisData || $rdapData): ?>
          <section class="card card-raw-data" id="card-raw-data">
            <div class="raw-data-sentinel" id="raw-data-sentinel"></div>
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
  <dialog id="dns-records-dialog" aria-labelledby="dns-records-dialog-title">
    <div class="dialog-head">
      <h2 class="dialog-title" id="dns-records-dialog-title">DNS Records</h2>
      <button class="dialog-close" aria-label="Close">
        <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
          <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
        </svg>
      </button>
    </div>
    <div class="dialog-body">
      <form>
        <div class="subdomain-input-box" dir="auto">
          <input autocapitalize="off" autocomplete="subdomain" autocorrect="off" name="subdomain" type="text">
          <span><?= $domain; ?></span>
        </div>
        <button class="primary-button" type="submit" data-loading="false">
          <span class="primary-button-label">Query</span>
          <span class="loader primary-button-loader" aria-hidden="true"></span>
        </button>
      </form>
      <div class="multi-status" data-status-type="loading">
        <div class="multi-status-container">
          <div class="dns-records-result"></div>
        </div>
        <div class="multi-status-empty">Not found.</div>
        <div class="multi-status-error">Query failed!</div>
        <div class="multi-status-loading">Querying…</div>
      </div>
    </div>
  </dialog>
  <script src="public/js/popper.min.js?v=<?= VERSION; ?>" defer></script>
  <script src="public/js/tippy.min.js?v=<?= VERSION; ?>" defer></script>
  <script src="public/js/theme-switcher.js?v=<?= VERSION; ?>" defer></script>
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

          domainElement.select();
          if (document.queryCommandSupported("insertText")) {
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

      const toggleWHOIS = document.getElementById("toggle-whois");
      const toggleRDAP = document.getElementById("toggle-rdap");
      const inputWHOIS = document.getElementById("input-whois");
      const inputRDAP = document.getElementById("input-rdap");

      const toggles = [toggleWHOIS, toggleRDAP];
      const inputs = [inputWHOIS, inputRDAP];

      toggles.forEach((toggle, index) => {
        toggle.addEventListener("click", () => {
          const active = toggle.getAttribute("aria-active") === "true";
          const nextActive = `${!active}`;

          toggle.setAttribute("aria-active", nextActive);
          inputs[index].value = nextActive === "true" ? "1" : "0";
        });
      });

      if (<?= json_encode($domain); ?>) {
        toggles.forEach((toggle) => {
          const active = toggle.getAttribute("aria-active") === "true";
          localStorage.setItem(toggle.id, `${+active}`);
        });
      } else {
        const whoisValue = localStorage.getItem("toggle-whois") || "0";
        const rdapValue = localStorage.getItem("toggle-rdap") || "0";

        toggles.forEach((toggle, index) => {
          if (!+whoisValue && !+rdapValue) {
            toggle.setAttribute("aria-active", "true");
            inputs[index].value = "1";
          } else {
            const active = `${!!+localStorage.getItem(toggle.id)}`;

            toggle.setAttribute("aria-active", active);
            inputs[index].value = active === "true" ? "1" : "0";
          }
        });
      }

      const form = document.getElementById("form");
      const searchButton = document.getElementById("search-button");

      const oldFormData = Object.fromEntries(new FormData(form).entries());

      form.addEventListener("submit", () => {
        searchButton.disabled = true;
        searchButton.dataset.loading = "true";
      });

      window.addEventListener("pageshow", (e) => {
        if (e.persisted) {
          const {
            domain,
            whois,
            rdap
          } = oldFormData;

          if (domainElement.value !== domain) {
            domainElement.focus();
            domainElement.select();

            if (domain) {
              if (document.queryCommandSupported("insertText")) {
                document.execCommand("insertText", false, domain);
              } else {
                const end = domainElement.value.length;
                domainElement.setRangeText(domain, 0, end, "end");
                domainElement.dispatchEvent(new Event("input", {
                  bubbles: true,
                }));
              }
            } else {
              if (document.queryCommandSupported("delete")) {
                document.execCommand("delete", false);
              } else {
                domainElement.setRangeText("");
                domainElement.dispatchEvent(new Event("input", {
                  bubbles: true,
                }));
              }
            }

            domainElement.blur();
          }

          if (inputWHOIS.value !== whois) {
            toggleWHOIS.setAttribute("aria-active", `${whois === "1"}`);
            inputWHOIS.value = whois;
          }

          if (inputRDAP.value !== rdap) {
            toggleRDAP.setAttribute("aria-active", `${rdap === "1"}`);
            inputRDAP.value = rdap;
          }

          if (searchButton.disabled === true) {
            searchButton.disabled = false;
            searchButton.dataset.loading = "false";
          }
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
          if (e.isIntersecting) {
            backToTop.classList.remove("visible");
          } else {
            backToTop.classList.add("visible");
          }
        }, {
          threshold: 1,
        });
        observer.observe(messageElement);
      }
    });
  </script>
  <?php if ($whoisData || $rdapData): ?>
    <?php if ($rdapData): ?>
      <script src="public/js/json-viewer.js?v=<?= VERSION; ?>" defer></script>
    <?php endif; ?>
    <script src="public/js/linkify.min.js?v=<?= VERSION; ?>" defer></script>
    <script src="public/js/linkify-html.min.js?v=<?= VERSION; ?>" defer></script>
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

        const setupDNSRecords = () => {
          const view = document.getElementById("dns-records-view");
          const dialog = document.getElementById("dns-records-dialog");
          const dialogClose = dialog.querySelector(".dialog-close");
          const form = dialog.querySelector("form");
          const inputBox = form.querySelector(".subdomain-input-box");
          const input = form.querySelector("input");
          const queryButton = form.querySelector("button");
          const multiStatus = dialog.querySelector(".multi-status");
          const result = dialog.querySelector(".dns-records-result");

          if (!view) {
            return;
          }

          view.addEventListener("click", () => {
            dialog.showModal();
            getData();
          });
          dialog.addEventListener("click", (e) => {
            if (e.target === dialog) {
              dialog.close();
            }
          });
          dialog.addEventListener("close", () => {
            input.value = "";
            queryButton.disabled = false;
            queryButton.dataset.loading = "false";

            controller.abort();
            if (timeoutId) {
              clearTimeout(timeoutId);
              timeoutId = undefined;
            }
          });
          dialogClose.addEventListener("click", () => {
            dialog.close();
          });
          form.addEventListener("submit", (e) => {
            e.preventDefault();

            getData(new FormData(form).get("subdomain"));
          });
          inputBox.addEventListener("click", () => {
            input.focus();
          });

          let controller = new AbortController();
          let timeoutId;

          const getData = async (subdomain) => {
            queryButton.disabled = true;
            queryButton.dataset.loading = true;
            multiStatus.dataset.statusType = "loading";

            if (controller.abort) {
              controller = new AbortController();
            }
            if (timeoutId) {
              clearTimeout(timeoutId);
              timeoutId = undefined;
            }

            const startTime = Date.now();

            try {
              const params = new URLSearchParams();
              params.append("domain", <?= json_encode($domain); ?>);
              if (subdomain) {
                params.append("subdomain", subdomain);
              }

              const response = await fetch(`dns-records?${params.toString()}`, {
                signal: controller.signal
              });

              if (!response.ok) {
                throw new Error();
              }

              const {
                domain,
                data,
              } = await response.json();

              let innerHTML = "";

              for (const type in data) {
                const records = data[type];

                innerHTML += `<p class="dns-records-result-type">${type}</p>`;

                innerHTML += `<table class="dns-records-result-table">`;

                innerHTML += "<thead><tr><th>#</th>";
                const keys = Object.keys(records[0]);
                keys.forEach((key) => {
                  innerHTML += `<th>${key}</th>`;
                });
                innerHTML += "</tr></thead>";

                innerHTML += "<tbody>";
                records.forEach((record, index) => {
                  innerHTML += `<tr><td>${index + 1}</td>`;
                  keys.forEach((key) => {
                    let child = record[key];
                    if ((type === "A" || type === "AAAA") && key === "value") {
                      child = `<a href="https://ipinfo.io/${child}" rel="nofollow noopener noreferrer" target="_blank">${child}</a>`;
                    }
                    innerHTML += `<td>${child}</td>`;
                  });
                  innerHTML += `</tr>`;
                });
                innerHTML += "</tbody>";

                innerHTML += "</table>";
              }

              if (innerHTML) {
                innerHTML = `<span class="dns-records-result-title">DNS records for ${domain}</span>${innerHTML}`;
              }

              timeoutId = setTimeout(() => {
                queryButton.disabled = false;
                queryButton.dataset.loading = "false";
                multiStatus.dataset.statusType = innerHTML ? "" : "empty";
                result.innerHTML = innerHTML;
              }, Math.max(0, 500 - (Date.now() - startTime)));
            } catch (error) {
              if (error.name !== "AbortError") {
                timeoutId = setTimeout(() => {
                  queryButton.disabled = false;
                  queryButton.dataset.loading = "false";
                  multiStatus.dataset.statusType = "error";
                }, Math.max(0, 500 - (Date.now() - startTime)));
              }
            }
          };
        };

        setupDNSRecords();

        const cardRawData = document.getElementById("card-raw-data");
        const rawDataSentinel = document.getElementById("raw-data-sentinel");
        const rawDataHead = document.getElementById("raw-data-head");
        const rawDataTabWHOIS = document.getElementById("raw-data-tab-whois");
        const rawDataTabRDAP = document.getElementById("raw-data-tab-rdap");
        const rawDataWHOIS = document.getElementById("raw-data-whois");
        const rawDataRDAP = document.getElementById("raw-data-rdap");

        if (rawDataSentinel && rawDataHead) {
          const observer = new IntersectionObserver(([e]) => {
            if (e.isIntersecting) {
              cardRawData.style.borderRadius = null;
              rawDataHead.style.borderRadius = null;
            } else {
              cardRawData.style.borderRadius = "0 0 1rem 1rem";
              rawDataHead.style.borderRadius = "0";
            }
          }, {
            threshold: 1,
          });
          observer.observe(rawDataSentinel);
        }

        if (rawDataTabWHOIS && rawDataTabRDAP) {
          rawDataTabWHOIS.addEventListener("click", () => {
            if (!rawDataTabWHOIS.classList.contains("raw-data-tab-active")) {
              rawDataTabWHOIS.classList.add("raw-data-tab-active");
              rawDataWHOIS.style.display = "block";
              rawDataTabRDAP.classList.remove("raw-data-tab-active");
              rawDataRDAP.style.display = "none";
            }

            rawDataSentinel.scrollIntoView({
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

            rawDataSentinel.scrollIntoView({
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
                url: (value) => /^https?:\/\//i.test(value),
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
  <?= CUSTOM_SCRIPT; ?>
</body>

</html>