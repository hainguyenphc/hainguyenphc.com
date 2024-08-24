<?php

require __DIR__ . '/variables.php';

/**
 * @note: 5th file.
 */

/**
 * @var $cron_key
 * @var $db_host
 * @var $db_port
 * @var $db_name
 * @var $db_user
 * @var $db_pass
 * @var $base_url
 * @var $base_private_dir
 * @var $remote_idp_id
 * @var $remote_idp_url
 */

$config = [
  'key' => $cron_key,
  'sendemail' => FALSE,
  'debug_message' => TRUE,
  'allowed_tags' => ['frequent'],
];
