<?php

namespace Drupal\surgery\Constants;

class Constants {

  const TRUE_STRING = "true";

  const FALSE_STRING = "false";

  const INSPECT_COMMAND = "inspect";

  const FIELD_VALUE_COMMAND = "field-value";

  const LIST_FIELDS_COMMAND = "list-fields";

  public static function getAvailableCommands() {
    return [
      self::INSPECT_COMMAND,
      self::FIELD_VALUE_COMMAND,
      self::LIST_FIELDS_COMMAND,
    ];
  }

}