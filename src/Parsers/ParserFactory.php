<?php
class ParserFactory
{
  private static $extensionToClassSuffix = [
    "am" => ["am", "հայ"],
    "ar" => ["ar"],
    "as" => ["as"],
    "at" => ["at"],
    "aw" => ["aw", "nl"],
    "ax" => ["ax"],
    "be" => ["be"],
    "bg" => ["bg", "бг"],
    "bn" => ["bn"],
    "bo" => ["bo"],
    "br" => ["br"],
    "caco" => ["co.ca"],
    "cl" => ["cl"],
    "cn" => ["cn", "中国", "中國"],
    "cr" => ["cr"],
    "cz" => ["cz"],
    "dk" => ["dk"],
    "ee" => ["ee"],
    "eu" => ["eu", "ευ", "ею"],
    "fi" => ["fi"],
    "fr" => ["fr", "pm", "re", "tf", "wf", "yt"],
    "ga" => ["ga", "sn"],
    "gf" => ["gf"],
    "gg" => ["gg"],
    "hk" => ["hk", "香港"],
    "id" => ["id"],
    "il" => ["il", "ישראל"],
    "im" => ["im"],
    "it" => ["it"],
    "je" => ["je"],
    "jp" => ["jp"],
    "kg" => ["kg"],
    "kr" => ["kr", "한국"],
    "kz" => ["kz", "қаз"],
    "ls" => ["ls"],
    "lt" => ["lt"],
    "lu" => ["lu"],
    "lv" => ["lv"],
    "md" => ["md"],
    "mk" => ["mk", "мкд"],
    "mo" => ["mo", "澳門"],
    "mq" => ["mq"],
    "mw" => ["mw"],
    "mx" => ["mx"],
    "nc" => ["nc"],
    "netza" => ["za.net", "za.org"],
    "no" => ["no"],
    "pf" => ["pf"],
    "pl" => ["pl"],
    "plco" => ["co.pl"],
    "pt" => ["pt"],
    "qa" => ["qa", "قطر"],
    "ro" => ["ro", "uz"],
    "rs" => ["rs", "срб"],
    "ru" => ["ru", "su", "рф"],
    "sa" => ["sa", "السعودية"],
    "sk" => ["sk"],
    "sm" => ["sm"],
    "st" => ["st"],
    "tg" => ["tg"],
    "tm" => ["tm"],
    "tn" => ["tn", "تونس"],
    "tr" => ["tr"],
    "tw" => ["tw", "台湾", "台灣"],
    "ua" => ["ua"],
    "ua1" => ["укр"],
    "uk" => ["uk"],
    "ukac" => ["ac.uk"],
    "ve" => ["ve"],
  ];

  public static function create($extension, $data): Parser
  {
    foreach (self::$extensionToClassSuffix as $classSuffix => $extensions) {
      $class = "Parser" . strtoupper($classSuffix);
      if (in_array(strtolower($extension), $extensions) && class_exists($class)) {
        return new $class($data);
      }
    }

    return new Parser($data);
  }
}
