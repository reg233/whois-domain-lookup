<?php
function getProtocol()
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
