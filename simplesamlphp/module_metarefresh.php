<?php

require __DIR__ . '/variables.php';

/**
 * @note: 4th file.
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
  // Metadata is split into sets.
  'sets' => [
    // We have only one which we'll call default.
    'default' => [
      // Each set has sources. We'll have only one source.
      'sources' => [
        [
          // This is the IdP metadata URL mentioned way back at the start of
          // this guide. Specifying it as the source here tells simplesamlphp
          // to fetch it automatically.
          // In Auth0, it is the "Identity Provider Metadata: Download" link, it serves an XML file.
          // e.g. https://dev-h3g6rnzyw4uphcnl.us.auth0.com/samlp/metadata/ntMKt8dt2PlBp8ui4stj7Q6GwiNErgUO
          'src' => $remote_idp_url,
        ],
      ],
      // How long to cache metadata. Past this point SimpleSAMLphp will no
      // longer use this metadata. Make sure to set this high enough that it
      // will never expire before a refresh! This sets it to not expire for
      // 10 years and refresh automatically every fifteen minutes or so.
      'expireAfter' => 24 * 60 * 60 * 3650,
      // Output to our metadata directory. You can also export to a
      // subdirectory. This is useful if you have multiple IdPs or more likely
      // if your site is acting as an IdP (not covered in this guide) and has
      // multiple SPs.
      'outputDir' => "$base_private_dir/metadata",
      'outputFormat' => 'flatfile',
      // Automatically refresh metadata when the web cron is run with this tag.
      'cron' => ['frequent'],
    ],
  ],
];
