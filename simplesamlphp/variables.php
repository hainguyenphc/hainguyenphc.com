<?php

/**
 * @note: 1st file.
 */

/**
 * Local: Lando
 */
if (getenv('LANDO_INFO')) {
  if ($lando_info = json_decode(getenv('LANDO_INFO'), TRUE)) {
    $db = $lando_info['database'];
    $db_host = $db['internal_connection']['host'];
    $db_port = $db['internal_connection']['port'];
    $db_name = $db['creds']['database'];
    $db_user = $db['creds']['user'];
    $db_pass = $db['creds']['password'];
  }
}

/**
 * Local: DDEV
 */
if (isset($_ENV['IS_DDEV_PROJECT'])) {
  $db_host = 'db';
  $db_port = '3306';
  $db_name = 'db';
  $db_user = 'db';
  $db_pass = 'db';
}
// @note Populate this with a random string.
$salt = 'btqRSEmENY3pCbvP65oLsvdbraVzoz1o';
// @note Populate this with a secure password.
$admin_pass = 'Uu145a-t_HxH_x=yRFBq';
// @note Populate this with a secure password.
$cron_key = 'GwlL_2vt3xtbz9-RTBJ4Xb-KnPY_gpW7';

$base_private_dir = __DIR__ . '/../sites/default/files/private/simplesamlphp';
// @see simplesamlphp/authsources.php
// @see "SAML Metadata URL"
// e.g. https://dev-h3g6rnzyw4uphcnl.us.auth0.com/samlp/metadata/ntMKt8dt2PlBp8ui4stj7Q6GwiNErgUO
// It is a link to download the metadata XML file.
// Open the file and see:
// <EntityDescriptor entityID="urn:dev-h3g6rnzyw4uphcnl.us.auth0.com" xmlns="urn:oasis:names:tc:SAML:2.0:metadata"></EntityDescriptor>
// The urn:dev-h3g6rnzyw4uphcnl.us.auth0.com is the remote IdP ID!
$remote_idp_id = 'urn:dev-h3g6rnzyw4uphcnl.us.auth0.com';
// @see simplesamlphp/module_metarefresh.php
$remote_idp_url = 'https://dev-h3g6rnzyw4uphcnl.us.auth0.com/samlp/metadata/ntMKt8dt2PlBp8ui4stj7Q6GwiNErgUO';
$base_url = 'https://' . $_SERVER['HTTP_HOST'] . '/simplesaml';
