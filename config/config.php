<?php
define("DATA_SOURCE", getenv("DATA_SOURCE") ?: "all");

define("DEFAULT_EXTENSION", getenv("DEFAULT_EXTENSION") ?: "com");

define("SITE_TITLE", getenv("SITE_TITLE") ?: "WHOIS domain lookup");

define("SITE_DESCRIPTION", getenv("SITE_DESCRIPTION") ?: "A simple WHOIS domain lookup website with strong TLD compatibility.");

define("SITE_KEYWORDS", getenv("SITE_KEYWORDS") ?: "whois, rdap, domain lookup, open source, api, tld, cctld, .com, .net, .org");

define("BASE", getenv("BASE") ?: "/");

define("CUSTOM_HEAD", getenv("CUSTOM_HEAD") ?: "");

define("CUSTOM_SCRIPT", getenv("CUSTOM_SCRIPT") ?: "");

define("HOSTED_ON", getenv("HOSTED_ON") ?: "");

define("HOSTED_ON_URL", getenv("HOSTED_ON_URL") ?: "");
