<?php
define("SITE_TITLE", getenv("SITE_TITLE") ?: "WHOIS Domain Lookup");

define("SITE_SHORT_TITLE", getenv("SITE_SHORT_TITLE") ?: "WHOIS");

define("SITE_DESCRIPTION", getenv("SITE_DESCRIPTION") ?: "A simple WHOIS domain lookup website with strong TLD compatibility.");

define("SITE_KEYWORDS", getenv("SITE_KEYWORDS") ?: "whois, rdap, domain lookup, open source, api, tld, cctld, .com, .net, .org");

define("SITE_PASSWORD", getenv("SITE_PASSWORD") ?: "");

define(
  "CLASSIC_UI",
  filter_var(getenv("CLASSIC_UI") === false ? "0" : getenv("CLASSIC_UI"), FILTER_VALIDATE_BOOL),
);

define("BASE", getenv("BASE") ?: "/");

define("CUSTOM_HEAD", getenv("CUSTOM_HEAD") ?: "");

define("CUSTOM_SCRIPT", getenv("CUSTOM_SCRIPT") ?: "");

define("CUSTOM_HEAD_LOGIN", getenv("CUSTOM_HEAD_LOGIN") ?: "");

define("CUSTOM_SCRIPT_LOGIN", getenv("CUSTOM_SCRIPT_LOGIN") ?: "");
