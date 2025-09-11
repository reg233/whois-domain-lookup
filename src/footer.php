<?php
define("VERSION", "v2025.9.11");

require_once __DIR__ . "/../config/config.php";
?>

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
    <a href="https://github.com/reg233/whois-domain-lookup" rel="noopener" target="_blank"><?= VERSION ?></a>
  </div>
</footer>