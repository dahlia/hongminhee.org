<?php

function parse_accept_languages(string $http_accept_language): array {
  $accept_languages = array();
  foreach (explode(",", $http_accept_language) as $lang) {
  if (strpos($lang, ";") === false) {
    $param = "";
  } else {
    list($lang, $param) = explode(";", $lang);
  }
  $lang = trim(strtolower($lang));
  $param = trim(strtolower($param));
  if (substr($param, 0, 2) == "q=") {
    $q = trim(substr($param, 2));
    $q = is_numeric($q) ? floatval($q) : 1;
  } else {
    $q = 1;
  }
  $accept_languages[$lang] = $q;
  }
  arsort($accept_languages, SORT_NUMERIC);
  return $accept_languages;
}

function parse_language_tag(string $language): array {
  $tags = explode('-', trim(strtolower($language)));
  $lang = array_shift($tags);
  $script = null;
  $region = null;
  foreach ($tags as $tag) {
    if (strlen($tag) === 4) $script = $tag;
    else if (strlen($tag) === 2) $region = $tag;
  }
  return array("language" => $lang, "script" => $script, "region" => $region);
}

function match_language_tag(string $a, string $b): int {
  $a_tag = parse_language_tag($a);
  $b_tag = parse_language_tag($b);
  if ($a_tag["language"] !== $b_tag["language"]) {
    // FIXME This shouldn't be hard-coded, but coded with a separate table of
    //       rules instead.
    // Assume zh/ja speakers are more familar with hanja:
    $score = 0;
    if ($a_tag["language"] === "zh" || $a_tag["language"] === "ja") {
      if ($b_tag["script"] === "kore") $score++;
    }
    if ($b_tag["language"] === "zh" || $b_tag["language"] === "ja") {
      if ($a_tag["script"] === "kore") $score++;
    }
    return $score;
  }
  $score = 1;
  foreach (array("script", "region") as $key) {
    if ($a_tag[$key] === $b_tag[$key]) $score++;
  }
  return $score;
}

$LANG = "en";

$accept_languages = parse_accept_languages(
  empty($_GET["lang"])
    ? $_SERVER["HTTP_ACCEPT_LANGUAGE"]
    : $_GET["lang"]
);
$available_languages = array();
foreach (glob("index.*.html") as $filename) {
$available_language = substr($filename, 6, -5);
$scores = array();
foreach ($accept_languages as $accept_language => $q) {
  $score = match_language_tag($available_language, $accept_language);
  array_push($scores, $score * $q);
}
$available_languages[$available_language] = max($scores);
}
arsort($available_languages, SORT_NUMERIC);
$LANG = key($available_languages);

include "index.$LANG.html";
