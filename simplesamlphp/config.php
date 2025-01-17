<?php

require __DIR__ . '/variables.php';

/**
 * @note: 2nd file.
 */

/**
 * @var $admin_pass
 * @var $salt
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

/**
 * Storage backend.
 */

$config = [];
// First we set the storage type.
$config['store.type'] = 'sql';
// Now we supply the DSN (data source name). This is a string like
// mysql:127.0.0.1:3306/drupal.
$config['store.sql.dsn'] = sprintf(
  'mysql:host=%s;port=%s;dbname=%s',
  $db_host,
  $db_port,
  $db_name
);
// We provide the username...
$config['store.sql.username'] = $db_user;
// ...and password separately.
$config['store.sql.password'] = $db_pass;

// If need be you can also pass additional database options, e.g. for loading
// an SSL certificate.
// $config['store.sql.options'] = [
//  \PDO::MYSQL_ATTR_SSL_CA => "/cacert.pem",
//  \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
// ];

// Since we're sharing the database with Drupal we'll prefix the simplesamlphp tables.
$config['store.sql.prefix'] = 'simplesaml_';

/**
 * Directories
 */

$config['tempdir'] = sys_get_temp_dir() . '/simplesamlphp';
$config['metadatadir'] = "$base_private_dir/metadata";
$config['loggingdir'] = "$base_private_dir/log";
$config['logging.handler'] = 'file';
$config['logging.logfile'] = 'simplesamlphp-' . date('Ymd') . '.log';
// This is only needed if simplesamlphp is providing certificates.
// $config['certdir'] = "$base_private_dir/cert";

/**
 * Admin password, secret salt, base URL.
 */

// Plain-text passwords are NOT allowed anymore.
// $config['auth.adminpassword'] = $admin_pass;
// Shell:
// - cd vendor/simplesamlphp/simplesamlphp
// - bin/pwgen.php
$config['auth.adminpassword'] = '$argon2id$v=19$m=64,t=4,p=1$NnhPdkp1MkdiZkljL1hXcQ$Nmb65tGRiycBFKsER2fD0v+PSsoNZPIPVdhkvoCc4H0';
$config['secretsalt'] = $salt;
$config['baseurlpath'] = $base_url;

/**
 * Cookies
 */

$config['session.cookie.secure'] = TRUE;
$config['session.cookie.samesite'] = 'Lax';

/**
 * Modules
 */

$config['module.enable'] = [
  'cron' => TRUE,
  'admin' => TRUE,
  'metarefresh' => TRUE,
];

/**
 * If you're behind a reverse proxy you should add this snippet as well so that HTTPS is handled properly:
 * @note DDEV needs this.
 */

if (isset($_ENV['IS_DDEV_PROJECT'])) {
  $protocol = 'http://';
  $port = ':80';
  if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['SERVER_PORT'] = 443;
    $_SERVER['HTTPS'] = 'true';
    $protocol = 'https://';
    $port = ':' . $_SERVER['SERVER_PORT'];
  }
}
