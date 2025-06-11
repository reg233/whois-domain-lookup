<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../vendor/autoload.php";

spl_autoload_register(function ($class) {
  if (str_starts_with($class, "Parser")) {
    require_once "Parsers/$class.php";
  } else {
    require_once "$class.php";
  }
});

use Pdp\SyntaxError;
use Pdp\UnableToResolveDomain;

$domain = htmlspecialchars($_GET["domain"] ?? "", ENT_QUOTES, "UTF-8");
$domain = preg_replace("/\s+/", "", $domain);

$whoisData = null;
$rdapData = null;
$error = null;
$parser = new Parser("");

if ($domain) {
  try {
    $lookup = new Lookup($domain);
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

  if ($_GET["json"] ?? "") {
    header("Content-Type: application/json");

    if ($error) {
      $value = ["code" => 1, "msg" => $error, "data" => null];
    } else {
      $value = ["code" => 0, "msg" => "Query successful", "data" => $parser];
    }

    echo json_encode($value, JSON_UNESCAPED_UNICODE);
    die;
  }
}
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
  <base href="<?= BASE; ?>" />
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="mobile-web-app-capable" content="yes" />
  <meta name="theme-color" content="#e1f9f9" />
  <meta name="description" content="<?= SITE_DESCRIPTION ?>" />
  <meta name="keywords" content="<?= SITE_KEYWORDS ?>" />
  <link rel="shortcut icon" href="public/favicon.ico" />
  <link rel="icon" href="public/images/favicon.svg" type="image/svg+xml" />
  <link rel="apple-touch-icon" href="public/images/apple-icon-180.png" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2048-2732.jpg" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1668-2388.jpg" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1536-2048.jpg" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1640-2360.jpg" media="(device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1668-2224.jpg" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1620-2160.jpg" media="(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1488-2266.jpg" media="(device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1320-2868.jpg" media="(device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1206-2622.jpg" media="(device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1290-2796.jpg" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1179-2556.jpg" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1170-2532.jpg" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1284-2778.jpg" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1125-2436.jpg" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1242-2688.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-828-1792.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1242-2208.jpg" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-750-1334.jpg" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-640-1136.jpg" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2732-2048.jpg" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2388-1668.jpg" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2048-1536.jpg" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2360-1640.jpg" media="(device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2224-1668.jpg" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2160-1620.jpg" media="(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2266-1488.jpg" media="(device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2868-1320.jpg" media="(device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2622-1206.jpg" media="(device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2796-1290.jpg" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2556-1179.jpg" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2532-1170.jpg" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2778-1284.jpg" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2436-1125.jpg" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2688-1242.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1792-828.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-2208-1242.jpg" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1334-750.jpg" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" />
  <link rel="apple-touch-startup-image" href="public/images/apple-splash-1136-640.jpg" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)" />
  <link rel="manifest" href="manifest<?= $domain ? "?domain=$domain" : "" ?>" />
  <title><?= SITE_TITLE . ($domain ? " - $domain" : "") ?></title>
  <link rel="stylesheet" href="public/css/index.css" />
  <link rel="stylesheet" href="public/css/json.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght,SOFT,WONK@72,600,50,1&display=swap" rel="stylesheet" />
  <?= CUSTOM_HEAD ?>
</head>

<body>
  <main>
    <header>
      <div>
        <h1><a href="<?= BASE; ?>"><?= SITE_TITLE ?></a></h1>
        <form action="" method="get" onsubmit="handleSubmit(event)">
          <div>
            <input
              autocapitalize="off"
              autocomplete="domain"
              autocorrect="off"
              <?= $domain ? "" : 'autofocus="autofocus"'; ?>
              id="domain"
              inputmode="url"
              name="domain"
              placeholder="Enter a domain"
              required="required"
              type="text"
              value="<?= $domain; ?>" />
            <button class="button-clear" id="domain-clear" type="button" aria-label="Clear">
              <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708" />
              </svg>
            </button>
          </div>
          <button class="button-search">
            <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" id="search-icon">
              <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
            </svg>
            <span>Search</span>
          </button>
        </form>
      </div>
    </header>
    <?php if ($domain): ?>
      <section class="messages">
        <div>
          <?php if ($error): ?>
            <div class="message message-negative">
              <div class="message-header">
                <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="message-icon">
                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708" />
                </svg>
                <h2 class="message-title">
                  <?= $error; ?>
                </h2>
              </div>
            </div>
          <?php elseif ($parser->unknown): ?>
            <div class="message message-notice">
              <div class="message-header">
                <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="message-icon">
                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                  <path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286m1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94" />
                </svg>
                <h2 class="message-title">
                  &#39;<?= $domain; ?>&#39; is unknown
                </h2>
              </div>
            </div>
          <?php elseif ($parser->reserved): ?>
            <div class="message message-notice">
              <div class="message-header">
                <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="message-icon">
                  <path d="M15 8a6.97 6.97 0 0 0-1.71-4.584l-9.874 9.875A7 7 0 0 0 15 8M2.71 12.584l9.874-9.875a7 7 0 0 0-9.874 9.874ZM16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0" />
                </svg>
                <h2 class="message-title">
                  &#39;<?= $domain; ?>&#39; has already been reserved
                </h2>
              </div>
            </div>
          <?php elseif ($parser->registered): ?>
            <div class="message message-positive">
              <div class="message-header">
                <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="message-icon">
                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                  <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05" />
                </svg>
                <h2 class="message-title">
                  <a href="http://<?= $domain; ?>" rel="nofollow noopener noreferrer" target="_blank"><?= $domain; ?></a> <?= $parser->domain ? "" : "v_v"; ?> has already been registered
                </h2>
              </div>
              <div class="message-data">
                <?php if ($parser->registrar): ?>
                  <div class="message-label">
                    Registrar
                  </div>
                  <div>
                    <?php if ($parser->registrarURL): ?>
                      <a href="<?= $parser->registrarURL; ?>" rel="nofollow noopener noreferrer" target="_blank"><?= $parser->registrar; ?></a>
                    <?php else: ?>
                      <?= $parser->registrar; ?>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
                <?php if ($parser->creationDate): ?>
                  <div class="message-label">
                    Creation Date
                  </div>
                  <div>
                    <?php if ($parser->creationDateISO8601 === null): ?>
                      <span><?= $parser->creationDate; ?></span>
                    <?php elseif (str_ends_with($parser->creationDateISO8601, "Z")): ?>
                      <button id="creation-date" data-iso8601="<?= $parser->creationDateISO8601; ?>">
                        <?= $parser->creationDate; ?>
                      </button>
                    <?php else: ?>
                      <span id="creation-date" data-iso8601="<?= $parser->creationDateISO8601; ?>">
                        <?= $parser->creationDate; ?>
                      </span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
                <?php if ($parser->expirationDate): ?>
                  <div class="message-label">
                    Expiration Date
                  </div>
                  <div>
                    <?php if ($parser->expirationDateISO8601 === null): ?>
                      <span><?= $parser->expirationDate; ?></span>
                    <?php elseif (str_ends_with($parser->expirationDateISO8601, "Z")): ?>
                      <button id="expiration-date" data-iso8601="<?= $parser->expirationDateISO8601; ?>">
                        <?= $parser->expirationDate; ?>
                      </button>
                    <?php else: ?>
                      <span id="expiration-date" data-iso8601="<?= $parser->expirationDateISO8601; ?>">
                        <?= $parser->expirationDate; ?>
                      </span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
                <?php if ($parser->updatedDate): ?>
                  <div class="message-label">
                    Updated Date
                  </div>
                  <div>
                    <?php if ($parser->updatedDateISO8601 === null): ?>
                      <span><?= $parser->updatedDate; ?></span>
                    <?php elseif (str_ends_with($parser->updatedDateISO8601, "Z")): ?>
                      <button id="updated-date" data-iso8601="<?= $parser->updatedDateISO8601; ?>">
                        <?= $parser->updatedDate; ?>
                      </button>
                    <?php else: ?>
                      <span id="updated-date" data-iso8601="<?= $parser->updatedDateISO8601; ?>">
                        <?= $parser->updatedDate; ?>
                      </span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
                <?php if ($parser->availableDate): ?>
                  <div class="message-label">
                    Available Date
                  </div>
                  <div>
                    <?php if ($parser->availableDateISO8601 === null): ?>
                      <span><?= $parser->availableDate; ?></span>
                    <?php elseif (str_ends_with($parser->availableDateISO8601, "Z")): ?>
                      <button id="available-date" data-iso8601="<?= $parser->availableDateISO8601; ?>">
                        <?= $parser->availableDate; ?>
                      </button>
                    <?php else: ?>
                      <span id="available-date" data-iso8601="<?= $parser->availableDateISO8601; ?>">
                        <?= $parser->availableDate; ?>
                      </span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
                <?php if ($parser->status): ?>
                  <div class="message-label">
                    Status
                  </div>
                  <div class="message-value-status">
                    <?php foreach ($parser->status as $status): ?>
                      <div>
                        <?php if ($status["url"]): ?>
                          <a href="<?= $status["url"]; ?>" rel="nofollow noopener noreferrer" target="_blank"><?= $status["text"]; ?></a>
                        <?php else: ?>
                          <?= $status["text"]; ?>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <?php if ($parser->nameServers): ?>
                  <div class="message-label">
                    Name Servers
                  </div>
                  <div class="message-value-name-servers">
                    <?php foreach ($parser->nameServers as $nameServer): ?>
                      <div>
                        <?= $nameServer; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
              <?php if ($parser->age || $parser->remaining || $parser->pendingDelete || $parser->gracePeriod || $parser->redemptionPeriod): ?>
                <div class="message-tags">
                  <?php if ($parser->age): ?>
                    <button class="message-tag message-tag-gray" id="age" data-seconds="<?= $parser->ageSeconds; ?>">
                      <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z" />
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0" />
                      </svg>
                      <span><?= $parser->age; ?></span>
                    </button>
                  <?php endif; ?>
                  <?php if ($parser->remaining): ?>
                    <button class="message-tag message-tag-gray" id="remaining" data-seconds="<?= $parser->remainingSeconds; ?>">
                      <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M2 1.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1-.5-.5m2.5.5v1a3.5 3.5 0 0 0 1.989 3.158c.533.256 1.011.791 1.011 1.491v.702c0 .7-.478 1.235-1.011 1.491A3.5 3.5 0 0 0 4.5 13v1h7v-1a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351v-.702c0-.7.478-1.235 1.011-1.491A3.5 3.5 0 0 0 11.5 3V2z" />
                      </svg>
                      <span><?= $parser->remaining; ?></span>
                    </button>
                  <?php endif; ?>
                  <?php if ($parser->ageSeconds && $parser->ageSeconds < 7 * 24 * 60 * 60): ?>
                    <span class="message-tag message-tag-green">New</span>
                  <?php endif; ?>
                  <?php if (($parser->remainingSeconds ?? -1) >= 0 && $parser->remainingSeconds < 7 * 24 * 60 * 60): ?>
                    <span class="message-tag message-tag-yellow">Expiring Soon</span>
                  <?php endif; ?>
                  <?php if ($parser->pendingDelete): ?>
                    <span class="message-tag message-tag-red">Pending Delete</span>
                  <?php elseif ($parser->remainingSeconds < 0): ?>
                    <span class="message-tag message-tag-red">Expired</span>
                  <?php endif; ?>
                  <?php if ($parser->gracePeriod): ?>
                    <span class="message-tag message-tag-yellow">Grace Period</span>
                  <?php elseif ($parser->redemptionPeriod): ?>
                    <span class="message-tag message-tag-blue">Redemption Period</span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="message message-informative">
              <div class="message-header">
                <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="message-icon">
                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                  <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0" />
                </svg>
                <h2 class="message-title">
                  &#39;<?= $domain; ?>&#39; does not appear registered yet
                </h2>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </section>
    <?php endif; ?>
    <?php if ($whoisData && $rdapData): ?>
      <section class="data-source">
        <div class="segmented">
          <button class="segmented-item segmented-item-selected" id="data-source-whois" type="button">WHOIS</button>
          <button class="segmented-item" id="data-source-rdap" type="button">RDAP</button>
        </div>
      </section>
    <?php endif; ?>
    <?php if ($whoisData || $rdapData): ?>
      <section class="raw-data">
        <?php if ($whoisData): ?>
          <pre class="raw-data-whois" id="raw-data-whois" tabindex="0"><?= $whoisData; ?></pre>
        <?php endif; ?>
        <?php if ($rdapData): ?>
          <pre class="raw-data-rdap" id="raw-data-rdap"><code class="language-json"><?= $rdapData; ?></code></pre>
        <?php endif; ?>
      </section>
    <?php endif; ?>
    <footer>
      <?php if (HOSTED_ON): ?>
        <div>
          Hosted on
          <?php if (HOSTED_ON_URL): ?>
            <a href="<?= HOSTED_ON_URL; ?>" rel="noopener" target="_blank"><?= HOSTED_ON; ?></a>
          <?php else: ?>
            <?= HOSTED_ON; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <div>
        v0.5.1 <a href="https://github.com/reg233/whois-domain-lookup" rel="noopener" target="_blank">GitHub</a>
      </div>
    </footer>
    <button class="back-to-top" id="back-to-top">
      <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
        <path d="M8 12a.5.5 0 0 0 .5-.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 .5.5" fill-rule="evenodd" />
      </svg>
    </button>
  </main>
  <script>
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
    domainClearElement.addEventListener("click", () => {
      domainElement.focus();
      domainElement.select();
      if (!document.execCommand("delete", false)) {
        domainElement.setRangeText("");
      }
      domainClearElement.classList.remove("visible");
    });

    function handleSubmit(event) {
      document.getElementById("search-icon").classList.add("searching");
    }

    const backToTop = document.getElementById("back-to-top");
    backToTop.addEventListener("click", () => {
      window.scrollTo({
        behavior: "smooth",
        top: 0,
      });
    });

    window.addEventListener("scroll", () => {
      if (document.documentElement.scrollTop > 360) {
        if (!backToTop.classList.contains("visible")) {
          backToTop.classList.add("visible");
        }
      } else {
        if (backToTop.classList.contains("visible")) {
          backToTop.classList.remove("visible");
        }
      }
    });
  </script>
  <?php if ($whoisData || $rdapData): ?>
    <script>
      function updateDateElementText(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
          const iso8601 = element.dataset.iso8601;
          if (iso8601) {
            if (iso8601.endsWith("Z")) {
              const date = new Date(iso8601);

              const year = date.getFullYear();
              const month = String(date.getMonth() + 1).padStart(2, "0");
              const day = String(date.getDate()).padStart(2, "0");
              const hours = String(date.getHours()).padStart(2, "0");
              const minutes = String(date.getMinutes()).padStart(2, "0");
              const seconds = String(date.getSeconds()).padStart(2, "0");

              element.innerText = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

              const offsetMinutes = date.getTimezoneOffset();
              const offsetRemainingMinutes = Math.abs(offsetMinutes % 60);
              const offsetHours = -Math.floor(offsetMinutes / 60);
              const sign = offsetHours >= 0 ? "+" : "-";

              const minutesStr = offsetRemainingMinutes ? `:${offsetRemainingMinutes}` : "";

              element.dataset.offset = `UTC${sign}${Math.abs(offsetHours)}${minutesStr}`;
            } else {
              element.innerText = iso8601;
            }
          }
        }
      }

      updateDateElementText("creation-date");
      updateDateElementText("expiration-date");
      updateDateElementText("updated-date");
      updateDateElementText("available-date");
    </script>
    <script src="public/js/popper.min.js" defer></script>
    <script src="public/js/tippy-bundle.umd.min.js" defer></script>
    <script src="public/js/linkify.min.js" defer></script>
    <script src="public/js/linkify-html.min.js" defer></script>
    <script src="public/js/prism.js" defer></script>
    <script>
      window.addEventListener("load", function() {
        tippy.setDefaultProps({
          arrow: false,
          offset: [0, 8],
        });

        function updateDateElementTooltip(elementId) {
          const element = document.getElementById(elementId);
          if (element) {
            const offset = element.dataset.offset;
            if (offset) {
              tippy(`#${elementId}`, {
                content: offset,
                placement: "right",
              });
            }
          }
        }

        updateDateElementTooltip("creation-date");
        updateDateElementTooltip("expiration-date");
        updateDateElementTooltip("updated-date");
        updateDateElementTooltip("available-date");

        function updateSecondsElementTooltip(elementId, prefix) {
          const element = document.getElementById(elementId);
          if (element) {
            const seconds = element.dataset.seconds;
            if (seconds) {
              let days = seconds / 24 / 60 / 60;
              days = seconds < 0 ? Math.ceil(days) : Math.floor(days);
              if (seconds < 0 && days === 0) {
                days = "-0";
              }
              tippy(`#${elementId}`, {
                content: `${prefix}: ${days} days`,
                placement: "bottom",
              });
            }
          }
        }

        updateSecondsElementTooltip("age", "Age");
        updateSecondsElementTooltip("remaining", "Remaining");

        const dataSourceWHOIS = document.getElementById("data-source-whois");
        const dataSourceRDAP = document.getElementById("data-source-rdap");
        const rawDataWHOIS = document.getElementById("raw-data-whois");
        const rawDataRDAP = document.getElementById("raw-data-rdap");

        if (dataSourceWHOIS && dataSourceRDAP) {
          dataSourceWHOIS.addEventListener("click", () => {
            if (dataSourceWHOIS.classList.contains("segmented-item-selected")) {
              return;
            }

            dataSourceWHOIS.classList.add("segmented-item-selected");
            rawDataWHOIS.style.display = "block";
            dataSourceRDAP.classList.remove("segmented-item-selected");
            rawDataRDAP.style.display = "none";
          });
          dataSourceRDAP.addEventListener("click", () => {
            if (dataSourceRDAP.classList.contains("segmented-item-selected")) {
              return;
            }

            dataSourceWHOIS.classList.remove("segmented-item-selected");
            rawDataWHOIS.style.display = "none";
            dataSourceRDAP.classList.add("segmented-item-selected");
            rawDataRDAP.style.display = "block";
          });
        }

        function linkifyRawData(element) {
          if (element) {
            element.innerHTML = linkifyHtml(element.innerHTML, {
              rel: "nofollow noopener noreferrer",
              target: "_blank",
              validate: {
                url: (value) => /^https?:\/\//.test(value),
              },
            });
          }
        }

        linkifyRawData(rawDataWHOIS);
        linkifyRawData(rawDataRDAP);
      });
    </script>
  <?php endif; ?>
  <?= CUSTOM_SCRIPT ?>
</body>

</html>