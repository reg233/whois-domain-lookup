<!doctype html>
<html lang="en-US">

<head>
  <base href="<?= BASE; ?>">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="theme-color" content="#e1f9f9">
  <meta name="description" content="<?= SITE_DESCRIPTION ?>">
  <meta name="keywords" content="<?= SITE_KEYWORDS ?>">
  <link rel="shortcut icon" href="public/classic/favicon.ico">
  <link rel="icon" href="public/classic/images/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="public/classic/images/apple-icon-180.png">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2048-2732.jpg" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1668-2388.jpg" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1536-2048.jpg" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1640-2360.jpg" media="(device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1668-2224.jpg" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1620-2160.jpg" media="(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1488-2266.jpg" media="(device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1320-2868.jpg" media="(device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1206-2622.jpg" media="(device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1260-2736.jpg" media="(device-width: 420px) and (device-height: 912px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1290-2796.jpg" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1179-2556.jpg" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1170-2532.jpg" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1284-2778.jpg" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1125-2436.jpg" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1242-2688.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-828-1792.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1242-2208.jpg" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-750-1334.jpg" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-640-1136.jpg" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2732-2048.jpg" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2388-1668.jpg" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2048-1536.jpg" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2360-1640.jpg" media="(device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2224-1668.jpg" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2160-1620.jpg" media="(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2266-1488.jpg" media="(device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2868-1320.jpg" media="(device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2622-1206.jpg" media="(device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2736-1260.jpg" media="(device-width: 420px) and (device-height: 912px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2796-1290.jpg" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2556-1179.jpg" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2532-1170.jpg" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2778-1284.jpg" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2436-1125.jpg" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2688-1242.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1792-828.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-2208-1242.jpg" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1334-750.jpg" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="apple-touch-startup-image" href="public/classic/images/apple-splash-1136-640.jpg" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
  <link rel="manifest" href="<?= $manifestHref; ?>">
  <title><?= ($domain ? "$domain | " : "") . SITE_TITLE ?></title>
  <link rel="stylesheet" href="public/classic/css/global.css">
  <link rel="stylesheet" href="public/classic/css/index.css">
  <?php if ($rdapData): ?>
    <link rel="stylesheet" href="public/classic/css/json-viewer.css">
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght,SOFT,WONK@72,600,50,1&display=swap">
  <?= CUSTOM_HEAD ?>
</head>

<body>
  <header>
    <div>
      <h1>
        <?php if ($domain): ?>
          <a href="<?= BASE; ?>"><?= SITE_TITLE ?></a>
        <?php else: ?>
          <?= SITE_TITLE ?>
        <?php endif; ?>
      </h1>
      <form action="" id="form" method="get">
        <div class="search-box">
          <input
            autocapitalize="off"
            autocomplete="domain"
            autocorrect="off"
            <?= $domain ? "" : "autofocus"; ?>
            class="input search-input"
            id="domain"
            inputmode="url"
            name="domain"
            placeholder="Enter a domain"
            required
            type="text"
            value="<?= $domain; ?>">
          <button class="search-clear" id="domain-clear" type="button" aria-label="Clear">
            <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
              <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708" />
            </svg>
          </button>
        </div>
        <button class="button search-button">
          <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" id="search-icon">
            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
          </svg>
          <span>Search</span>
        </button>
        <div class="checkboxes">
          <div class="checkbox">
            <input <?= in_array("whois", $dataSource, true) ? "checked" : "" ?> class="checkbox-trigger" id="checkbox-whois" name="whois" type="checkbox" value="1">
            <label class="checkbox-label" for="checkbox-whois">WHOIS</label>
            <div class="checkbox-icon-wrapper">
              <svg class="checkbox-icon checkbox-icon-checkmark" width="50" height="39.69" viewBox="0 0 50 39.69" aria-hidden="true">
                <path d="M43.68 0L16.74 27.051 6.319 16.63l-6.32 6.32 16.742 16.74L50 6.32z" />
              </svg>
            </div>
          </div>
          <div class="checkbox">
            <input <?= in_array("rdap", $dataSource, true) ? "checked" : "" ?> class="checkbox-trigger" id="checkbox-rdap" name="rdap" type="checkbox" value="1">
            <label class="checkbox-label" for="checkbox-rdap">RDAP</label>
            <div class="checkbox-icon-wrapper">
              <svg class="checkbox-icon checkbox-icon-checkmark" width="50" height="39.69" viewBox="0 0 50 39.69" aria-hidden="true">
                <path d="M43.68 0L16.74 27.051 6.319 16.63l-6.32 6.32 16.742 16.74L50 6.32z" />
              </svg>
            </div>
          </div>
        </div>
      </form>
    </div>
  </header>
  <main>
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
                <?php if ($parser->registrarIANAId): ?>
                  <div class="message-label">
                    IANA ID
                  </div>
                  <div>
                    <a href="https://client.rdap.org/?type=registrar&object=<?= $parser->registrarIANAId; ?>&follow-referral=0" rel="nofollow noopener noreferrer" target="_blank"><?= $parser->registrarIANAId; ?></a>
                  </div>
                <?php endif; ?>
                <?php if ($parser->registrarWHOISServer): ?>
                  <div class="message-label">
                    WHOIS Server
                  </div>
                  <div>
                    <?php if ($lookup->extension === "iana"): ?>
                      <?= $parser->registrarWHOISServer; ?>
                    <?php elseif (preg_match("#^https?://#i", $parser->registrarWHOISServer)): ?>
                      <a href="<?= $parser->registrarWHOISServer; ?>" rel="nofollow noopener noreferrer" target="_blank"><?= $parser->registrarWHOISServer; ?></a>
                    <?php else: ?>
                      <a href="<?= generateRegistrarServerHref("whois", $parser->registrarWHOISServer); ?>"><?= $parser->registrarWHOISServer; ?></a>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
                <?php if ($parser->registrarRDAPServer): ?>
                  <div class="message-label">
                    RDAP Server
                  </div>
                  <div>
                    <?php if ($lookup->extension === "iana"): ?>
                      <a href="<?= $parser->registrarRDAPServer; ?>" rel="nofollow noopener noreferrer" target="_blank"><?= $parser->registrarRDAPServer; ?></a>
                    <?php else: ?>
                      <a href="<?= generateRegistrarServerHref("rdap", $parser->registrarRDAPServer); ?>"><?= $parser->registrarRDAPServer; ?></a>
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
                <?php if ($parser->dnssecSigned !== null): ?>
                  <div class="message-label">
                    DNSSEC
                  </div>
                  <div>
                    <?php if ($parser->dnssecSigned): ?>
                      <a href="https://dnsviz.net/d/<?= $domain; ?>/dnssec/" rel="nofollow noopener noreferrer" target="_blank">Signed</a>
                    <?php else: ?>
                      <span>Unsigned</span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
              <?php if ($parser->createdAgo || $parser->expiresIn || $parser->pendingDelete || $parser->gracePeriod || $parser->redemptionPeriod): ?>
                <div class="message-tags">
                  <?php if ($parser->createdAgo): ?>
                    <button class="message-tag message-tag-gray" id="age" data-seconds="<?= $parser->createdAgoSeconds; ?>">
                      <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z" />
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0" />
                      </svg>
                      <span><?= $parser->createdAgo; ?></span>
                    </button>
                  <?php endif; ?>
                  <?php if ($parser->expiresIn): ?>
                    <button class="message-tag message-tag-gray" id="remaining" data-seconds="<?= $parser->expiresInSeconds; ?>">
                      <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M2 1.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1-.5-.5m2.5.5v1a3.5 3.5 0 0 0 1.989 3.158c.533.256 1.011.791 1.011 1.491v.702c0 .7-.478 1.235-1.011 1.491A3.5 3.5 0 0 0 4.5 13v1h7v-1a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351v-.702c0-.7.478-1.235 1.011-1.491A3.5 3.5 0 0 0 11.5 3V2z" />
                      </svg>
                      <span><?= $parser->expiresIn; ?></span>
                    </button>
                  <?php endif; ?>
                  <?php if ($parser->createdAgoSeconds && $parser->createdAgoSeconds < 7 * 24 * 60 * 60): ?>
                    <span class="message-tag message-tag-green">New</span>
                  <?php endif; ?>
                  <?php if (($parser->expiresInSeconds ?? -1) >= 0 && $parser->expiresInSeconds < 7 * 24 * 60 * 60): ?>
                    <span class="message-tag message-tag-yellow">Expiring Soon</span>
                  <?php endif; ?>
                  <?php if ($parser->pendingDelete): ?>
                    <span class="message-tag message-tag-red">Pending Delete</span>
                  <?php elseif ($parser->expiresInSeconds < 0): ?>
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
          <div id="raw-data-whois">
            <button class="copy-button" id="raw-data-whois-copy">
              <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                <path d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z" fill-rule="evenodd" />
              </svg>
            </button>
            <pre class="raw-data-whois" tabindex="0"><code><?= htmlspecialchars($whoisData, ENT_QUOTES, "UTF-8"); ?></code></pre>
          </div>
        <?php endif; ?>
        <?php if ($rdapData): ?>
          <div id="raw-data-rdap">
            <button class="copy-button" id="raw-data-rdap-copy">
              <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                <path d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z" fill-rule="evenodd" />
              </svg>
            </button>
            <pre class="raw-data-rdap" tabindex="0"><code><?= $rdapData; ?></code></pre>
          </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  </main>
  <?php require_once __DIR__ . "/../footer.php"; ?>
  <button class="back-to-top" id="back-to-top">
    <svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
      <path d="M8 12a.5.5 0 0 0 .5-.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 .5.5" fill-rule="evenodd" />
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

      const checkboxNames = ["whois", "rdap"];

      const searchParams = new URLSearchParams(window.location.search);
      if (searchParams.get("domain")) {
        checkboxNames.forEach((name) => {
          const checkbox = document.getElementById(`checkbox-${name}`);
          localStorage.setItem(`checkbox-${name}`, +checkbox.checked);
        });
      } else {
        const whoisValue = localStorage.getItem("checkbox-whois") || "0";
        const rdapValue = localStorage.getItem("checkbox-rdap") || "0";

        checkboxNames.forEach((name) => {
          const checkbox = document.getElementById(`checkbox-${name}`);

          if (!+whoisValue && !+rdapValue) {
            checkbox.checked = true;
          } else {
            checkbox.checked = localStorage.getItem(`checkbox-${name}`) === "1";
          }
        });
      }

      const form = document.getElementById("form");
      const searchIcon = document.getElementById("search-icon");

      form.addEventListener("submit", () => {
        searchIcon.classList.add("searching");
      });

      window.addEventListener("pageshow", (e) => {
        if (e.persisted) {
          searchIcon.classList.remove("searching");
        }
      });

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
    });
  </script>
  <?php if ($whoisData || $rdapData): ?>
    <script src="public/classic/js/popper.min.js" defer></script>
    <script src="public/classic/js/tippy-bundle.umd.min.js" defer></script>
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

                element.dataset.offset = `UTC${sign}${Math.abs(offsetHours)}${minutesStr}`;
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

        tippy.setDefaultProps({
          arrow: false,
          hideOnClick: false,
          offset: [0, 8],
        });

        const updateDateElementTooltip = (elementId) => {
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

        const updateDaysElementTooltip = (elementId, prefix) => {
          const element = document.getElementById(elementId);
          if (element) {
            const seconds = element.dataset.seconds;
            if (seconds) {
              let days = Math.trunc(seconds / 24 / 60 / 60);
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

        updateDaysElementTooltip("age", "Age");
        updateDaysElementTooltip("remaining", "Remaining");

        const dataSourceWHOIS = document.getElementById("data-source-whois");
        const dataSourceRDAP = document.getElementById("data-source-rdap");
        const rawDataWHOIS = document.getElementById("raw-data-whois");
        const rawDataWHOISCopy = document.getElementById("raw-data-whois-copy");
        const rawDataRDAP = document.getElementById("raw-data-rdap");
        const rawDataRDAPCopy = document.getElementById("raw-data-rdap-copy");

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

        const setupCopyButton = (element, copyAction) => {
          if (element) {
            const copyTippy = tippy(element, {
              placement: "bottom",
              onHide: (instance) => {
                if (copyTimeoutId) {
                  clearTimeout(copyTimeoutId);
                  copyTimeoutId = null;
                }
              },
              onShow: (instance) => {
                instance.setContent("Copy to clipboard");
              },
            });

            let copyTimeoutId;

            element.addEventListener("click", () => {
              if (copyTimeoutId) {
                clearTimeout(copyTimeoutId);
                copyTimeoutId = null;
              }

              const valueToCopy = copyAction();
              if (valueToCopy) {
                copyToClipboard(valueToCopy);
                copyTippy.setContent("Copied!");
                copyTimeoutId = setTimeout(() => {
                  copyTippy.setContent("Copy to clipboard");
                }, 2333);
              }
            });
          }
        }

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
        }

        if (rawDataWHOIS) {
          const pre = rawDataWHOIS.querySelector("pre");

          setupCopyButton(rawDataWHOISCopy, () => pre.innerText);
          linkifyRawData(pre);
        }
        if (rawDataRDAP) {
          const pre = rawDataRDAP.querySelector("pre");
          const rdapData = <?= json_encode($rdapData); ?>;

          setupCopyButton(rawDataRDAPCopy, () => JSON.stringify(JSON.parse(rdapData), null, 2));
          setupJSONViewer(pre, rdapData);
          linkifyRawData(pre);
        }
      });
    </script>
  <?php endif; ?>
  <?= CUSTOM_SCRIPT ?>
</body>

</html>