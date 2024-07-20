
## Set up a cron job in Linux environment to the cron URL

Syntax:

```shell
curl \
--silent \
"<site base url>/simplesaml/module.php/cron/run/frequent/<your cron key>" \
> /dev/null 2>&1
```

For hainguyenphc.com site:

```shell
curl \
--silent \
"https://hainguyenphc.com.ddev.site/simplesaml/module.php/cron/run/frequent/GwlL_2vt3xtbz9-RTBJ4Xb-KnPY_gpW7" \
> /dev/null 2>&1
```

## Authenticate as admin

Go to

```txt
/simplesaml/module.php/admin/test/default-sp
```

## Drupal SP metadata

Go to

```txt
/simplesaml/module.php/saml/sp/metadata.php/default-sp
https://hainguyenphc.com.ddev.site/simplesaml/module.php/saml/sp/metadata.php/default-sp
```

For testing with https://samltest.id you can manually upload your metadata by download the content of that URL and uploading the file at https://samltest.id/upload.php.

## Retrieve IdP metadata

Navigate to 

```txt
/simplesaml/module.php/metarefresh/
```

Or invoke a script, in the scenario

- Your IdP metarefresh URL is https://samltest.id/saml/idp
- You are storing your metadata in sites/default/files/private/simplesamlphp/metadata

```shell
./vendor/simplesamlphp/simplesamlphp/modules/metarefresh/bin/metarefresh.php \
--out-dir=../../../sites/default/files/private/simplesamlphp/metadata \
"https://samltest.id/saml/idp"
```
