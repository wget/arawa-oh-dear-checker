# OhDear health check for Arawa

## Prerequisites

* PHP >=8.0
* Composer

## Building

```
composer update
```

## Local testing

```
env oh-dear-health-check-secret="<cf. password manager>" php checker.php
```

## Developping

Replace dummy keys and secrets by a valid ones at the start of the `checker.php` file:
```
$OH_DEAR_SECRET = "<cf. password manager>"
```

After having added a new rule, please check lint your code:
```
php-cs-fixer fix checker.php
```
[src.](https://github.com/FriendsOfPhp/PHP-CS-Fixer)

## Debugging

To debug OhDear headers and display them on the OhDear dashboard, please replace statements like this one:
```
meta: ['dns_spf_is_is_correct' => true]
```
by
```
meta: ['dns_spf_is_is_correct' => true, 'headers' => $_SERVER]
```

## Releasing to production

Connect using ftp on port 21 (attention plain text insecrure connection!) with FileZilla using the password from the password manager:
```
arawafrssa@ftp.cluster006.ovh.net
```

Copy the `checker.php` file and the `vendor` folder to: `/www/oh-dear/`

Check if you're well denied with a 403 by visiting: `https://arawa.fr/oh-dear/checker.php`.
