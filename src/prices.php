<?php
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
  http_response_code(405);
  header("Allow: GET");
  die;
}

if (empty($_GET["domain"])) {
  http_response_code(400);
  die;
}

$curl = curl_init("https://api.tian.hu/pricing/" . $_GET["domain"]);

curl_setopt_array($curl, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_TIMEOUT => 10,
  CURLOPT_REFERER => "https://tian.hu/",
  CURLOPT_USERAGENT => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36",
]);

$response = curl_exec($curl);

curl_close($curl);

if ($response === false) {
  http_response_code(400);
} else {
  header("Content-Type: application/json");
  echo $response;
}
