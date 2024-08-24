<?php

require __DIR__ . '/variables.php';

/**
 * @note: 3rd file.
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
  // First, the admin authentication source.
  'admin' => [
    'core:AdminPassword',
  ],
  // Next up is the default-sp auth source. There are multiple ways to do this.
  // E.g. some people will configure an auth source per environment. I like to
  // configure one auth source with a dynamic entity ID since that way it's all
  // managed in one place, and you don't need to change the config on the Drupal
  // side between environments.
  'default-sp' => [
    // First key is the type of SP. That's not a typo - it has no value. This
    // will always be saml:SP assuming you're configuring your site as a SAML
    // SP.
    'saml:SP',
    // The entityID represents your site (SP) expected by the IdP. I usually set this
    // to be the metadata URL. This is convenient because it's
    // universal and carries across environments. You might want to use
    // $_SERVER['HTTP_X_FORWARDED_HOST'] if you're behind a reverse proxy. This
    // solution does have some disadvantages as well. E.g. the ID will change
    // if the site URL changes, such as when updating DNS records for launch.
    // You could avoid this by generating it based on an environment variable
    // value instead. However, metadata will likely need to be re-imported at
    // least either way since various other metadata URLs will change as well.
    // @see https://hainguyenphc.com.ddev.site/simplesaml/module.php/admin/federation
    // e.g. https://hainguyenphc.com.ddev.site/simplesaml/module.php/saml/sp/metadata.php/default-sp
    'entityID'             => $base_url . '/module.php/saml/sp/metadata.php/default-sp',
    // This is the ID of your remote IdP. In most cases this is the same as the
    // URL you are retrieving the metadata from. You can get it by opening
    // the metadata file (you can download it from the IdP URL) and checking
    // for an entityID attribute. You don't technically need this. Without it
    // simplesamlphp will let you choose your IdP. However, this means dropping
    // users on an ugly simplesamlphp page in the middle of their authentication
    // process. I would recommend setting this unless you have multiple IdPs.
    'idp'                  => $remote_idp_id,
    // The rest of these values are optional and not needed for most setups. They may
    // even break some setups (e.g. Azure AD). You should only use these if the IdP
    // requires simplesamlphp to have a certificate (most don't). If you do need
    // them you can usually copy and uncomment the below as-is. If you're curious
    // about what they do you can refer to the SimpleSAMLphp documentation:
    // https://simplesamlphp.org/docs/stable/saml:sp#section_4
    // 'redirect.sign'        => TRUE,
    // 'assertion.encryption' => FALSE,
    // 'sign.logout'          => TRUE,
    // SimpleSAMLphp will look for these in the cert directory configured in
    // config.php.
    // 'privatekey'           => 'saml.pem',
    // 'certificate'          => 'saml.crt',
    // 'signature.algorithm'  => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
  ],
];
