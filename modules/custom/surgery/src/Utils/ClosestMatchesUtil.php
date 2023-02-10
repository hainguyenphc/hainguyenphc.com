<?php

namespace Drupal\surgery\Utils;

class ClosestMatchesUtil {

  public static function getClosestMatches(string $str, array $collection) {
    $matches = [];
    if (!array_key_exists($str, $collection)) {
      foreach ($collection as $key => $value) {
        if (str_contains($key, $str)) {
          $matches[] = $key;
        }
      }        
    }
    else
      $matches[] = $str;

    return $matches;
  }

}