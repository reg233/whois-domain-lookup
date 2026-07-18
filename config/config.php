<?php

declare(strict_types=1);

define("SITE_TITLE", getenv("SITE_TITLE") ?: "WHOIS Domain Lookup");

define("SITE_SHORT_TITLE", getenv("SITE_SHORT_TITLE") ?: "WHOIS");

define("SITE_DESCRIPTION", getenv("SITE_DESCRIPTION") ?: "A simple WHOIS domain lookup website with strong TLD compatibility.");

define("SITE_PASSWORD", getenv("SITE_PASSWORD") ?: "");

define("BASE", getenv("BASE") ?: "/");

define("CUSTOM_HEAD", getenv("CUSTOM_HEAD") ?: "");

define("CUSTOM_SCRIPT", getenv("CUSTOM_SCRIPT") ?: "");

define("CUSTOM_HEAD_LOGIN", getenv("CUSTOM_HEAD_LOGIN") ?: "");

define("CUSTOM_SCRIPT_LOGIN", getenv("CUSTOM_SCRIPT_LOGIN") ?: "");

define("USER_AGENT", getenv("USER_AGENT") ?: "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36");
