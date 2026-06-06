<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/utils.php";

function redirect()
{
  $redirect = BASE;
  if (isset($_GET["redirect"]) && preg_match("#^" . BASE . ".*$#", $_GET["redirect"])) {
    $redirect = $_GET["redirect"];
  }
  header("Location: $redirect");
  die;
}

$failed = false;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
  if (
    !SITE_PASSWORD ||
    ($_COOKIE["password"] ?? "") === hash("sha256", SITE_PASSWORD)
  ) {
    redirect();
  }
} else if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (!SITE_PASSWORD) {
    http_response_code(500);
    die;
  }

  if (($_POST["password"] ?? "") === SITE_PASSWORD) {
    setcookie("password", hash("sha256", SITE_PASSWORD), [
      "expires" => strtotime("+1 year"),
      "path" => BASE,
      "secure" => true,
      "httponly" => true,
      "samesite" => "Lax",
    ]);
    redirect();
  } else {
    $failed = true;
  }
}

$origin = getProtocol() . $_SERVER["HTTP_HOST"];

$title = "Sign in | " . SITE_TITLE;
$ogUrl = $origin . BASE . "login";
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
  <link rel="manifest" href="manifest">
  <title><?= $title; ?></title>
  <meta name="description" content="<?= SITE_DESCRIPTION; ?>">
  <link rel="canonical" href="<?= $ogUrl; ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= $ogUrl; ?>">
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
  <link rel="stylesheet" href="public/css/login.css?v=<?= VERSION; ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght,SOFT,WONK@72,600,50,1&display=swap">
  <script src="public/js/theme.js?v=<?= VERSION; ?>"></script>
  <?= CUSTOM_HEAD_LOGIN; ?>
</head>

<body>
  <!-- Set app bar color for safari 26+ -->
  <!-- https://github.com/andesco/safari-color-tinting -->
  <!-- https://github.com/klmkyo/ios-safari-restore-meta-theme-color -->
  <div class="safari-26-app-bar-color" aria-hidden="true"></div>
  <div class="root">
    <main>
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
      <img alt="<?= SITE_TITLE; ?>" height="48" loading="lazy" src="public/images/favicon.svg">
      <h1><?= SITE_TITLE; ?></h1>
      <?php if ($failed): ?>
        <div class="error">Incorrect password.</div>
      <?php endif; ?>
      <form action="" id="form" method="post">
        <div class="input-box">
          <input autocomplete="current-password" autofocus class="input" id="password" name="password" placeholder="Password" required type="password">
          <button class="input-clear" id="password-clear" type="button" aria-label="Clear">
            <svg width="1em" height="1em" viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true">
              <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
            </svg>
          </button>
        </div>
        <button class="primary-button" id="sign-in-button" type="submit" data-loading="false">
          <span class="primary-button-label">Sign in</span>
          <span class="loader primary-button-loader" aria-hidden="true"></span>
        </button>
      </form>
    </main>
    <?php require_once __DIR__ . "/footer.php"; ?>
  </div>
  <script src="public/js/popper.min.js?v=<?= VERSION; ?>" defer></script>
  <script src="public/js/tippy.min.js?v=<?= VERSION; ?>" defer></script>
  <script src="public/js/theme-switcher.js?v=<?= VERSION; ?>" defer></script>
  <script>
    window.addEventListener("DOMContentLoaded", () => {
      const passwordElement = document.getElementById("password");
      const passwordClearElement = document.getElementById("password-clear");

      passwordElement.addEventListener("input", (e) => {
        if (e.target.value) {
          passwordClearElement.classList.add("visible");
        } else {
          passwordClearElement.classList.remove("visible");
        }
      });

      passwordClearElement.addEventListener("click", () => {
        passwordElement.focus();
        passwordElement.select();
        if (document.queryCommandSupported("delete")) {
          document.execCommand("delete", false);
        } else {
          passwordElement.setRangeText("");
          passwordElement.dispatchEvent(new Event("input", {
            bubbles: true,
          }));
        }
      });

      const form = document.getElementById("form");
      const signInButton = document.getElementById("sign-in-button");

      form.addEventListener("submit", (e) => {
        signInButton.disabled = true;
        signInButton.dataset.loading = "true";
      });

      window.addEventListener("pageshow", (e) => {
        if (e.persisted && signInButton.disabled === true) {
          signInButton.disabled = false;
          signInButton.dataset.loading = "false";
        }
      });
    });
  </script>
  <?= CUSTOM_SCRIPT_LOGIN; ?>
</body>

</html>