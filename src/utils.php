<?php

declare(strict_types=1);

function isPasswordValid(): bool
{
  if (!SITE_PASSWORD) {
    return true;
  }

  $password = $_COOKIE["password"] ?? null;
  if ($password === hash("sha256", SITE_PASSWORD)) {
    return true;
  }

  $authorization = $_SERVER["HTTP_AUTHORIZATION"] ?? null;
  $bearerPrefix = "Bearer ";
  if ($authorization && str_starts_with($authorization, $bearerPrefix)) {
    $hash = substr($authorization, strlen($bearerPrefix));
    if ($hash === hash("sha256", SITE_PASSWORD)) {
      return true;
    }
  }

  return false;
}

function cleanDomain(string $domain): string
{
  $domain = trim($domain);

  if (!$domain) {
    return "";
  }

  $domain = trim(pregReplace(["/\s+/", "/\.{2,}/"], ["", "."], $domain), ".");

  $parsedUrl = parse_url($domain);
  if ($parsedUrl["host"] ?? "") {
    $domain = $parsedUrl["host"];
  }

  return $domain;
}

function getProtocol(): string
{
  if (!empty($_SERVER["HTTP_X_FORWARDED_PROTO"])) {
    $values = array_map("strtolower", explode(",", $_SERVER["HTTP_X_FORWARDED_PROTO"]));

    if (in_array("https", $values, true)) {
      return "https://";
    }
  }

  if (!empty($_SERVER["HTTP_FORWARDED"])) {
    $values = array_map("strtolower", explode(";", $_SERVER["HTTP_FORWARDED"]));

    if (in_array("proto=https", $values, true)) {
      return "https://";
    }
  }

  if (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) !== "off") {
    return "https://";
  }

  return "http://";
}

/**
 * @param string|string[] $pattern
 * @param string|string[] $replacement
 * @param string|string[] $subject
 * @return ($subject is array ? string[] : string)
 */
function pregReplace(array|string $pattern, array|string $replacement, array|string $subject): array|string
{
  return preg_replace($pattern, $replacement, $subject) ?? $subject;
}

/** @return string[] */
function pregSplit(string $pattern, string $subject, int $limit = -1): array
{
  $substrings = preg_split($pattern, $subject, $limit);

  return $substrings === false ? [$subject] : $substrings;
}
