<span style="color:red">I believe I have brought the library to a usable and critiquable state. As of now, January 3rd of 2019, consider this library development stage grade code.</span>

I created this package because I didn't love the existing libraries out there. Also, this package has only a dependency on the PDO database driver avaialble.

So, this is a low level library designed to:

- Allow you to specify the config parameters via passing them as arguments in the Ehjwt object instantiation
- Allow you to specify a path and config file or use a config file in a default location with a default name of config/ehjwt-conf.php
- Allow you to specify the config parameters with environment variables
- Allow you to additionally specify an environment variable that prevents overriding the environment variable configuration options

- Allow you to rest easy that the algorithm will always be HS256
- Allow you to create a JWT Token string with standard "claims" some prefilled according to the config file
- Allow you to append custom claims
- Allow you to edit custom claims
- Allow you to remove custom claims
- Allow you to retrieve a JWT Token string
- Allow you to read token claims
- Allow you to validate a token
- Allow you to revoke a token

- Allow you to be confident about settings, my library uses your environment variables, config file, or arguments passed to the instantiation constructor in that order for all configurable values

- Allow you to temporarily or permanently revoke all tokens associated with an identified user-- "banning" a user, admittedly, isn't part of the JWT standard

## Step 1 - Install:

You will need a modern version of PHP installed.

**Add via composer or git clone, or wget the class file, or good old cut/paste and require_once()**

```bash
composer require bradchesney79/effortless-hs256-jwt
```

**Supply the necessary particulars; a PDO DB DSN, a PDO DB user, a PDO DB password, and a "system secret"**

You may do this any combination of three ways, they supercede one another in this order:
options passed to the constructor supercedes config file provided options which override environment variable provided options

- Make env vars available to PHP*
- Copy and edit the example config file to the config directory which shares the same parent directory as the composer vendor directory

*--I use composer. But if I didn't, the parent directory of my webroot directory is where I would put it*


*Alternatively, you may skip using env vars or a config file and create the object with parameters as the configs to use as such:*
```php
$jwt = new Ehjwt($secretString, null, $dsn, $dbUser, $dbPassword, $iss, $aud);
```

* Should you want to prevent developers from using a config file or options passed to the constructor, you may set ESJWT_USE_ENV_VARS as true to enforce usage of the environment variables-- it is an option available to you but it isn't fool proof.

**Run the db install script that can be found in the schema directory**

_**I usually do this via a provisioning script that fires off the SQL script after installing MySQL, PHP, composer, and the dependencies installed via composer.**_

## Step 1a

Installation via composer is not required-- I just think it is the best way

```php
require 'path/to/Ehjwt.php';
```

require_once, include, include_once... only you will really know what is best for you.


## Step 2 - Usage:


### Make the code available:


Be sure to do the completely normal PHP require or require_once of vendor/autoload.php

```php
use BradChesney79/EHJWT;
```

*Unwashed heathens that resist using composer will need something like this:*

```php
require_once 'path/to/Ehjwt.php';
```


### Create a token:

```php
$jwtToken = new EHJWT('SuperSecretStringUsedForOneWayEncryption', 'mysql:host=localhost;dbname=ehjwt', 'DBuser', 'DBPassword', 'IssuingWebsite', 'UserType');

// the globally unique ID of this token and its series of potential reissues
$jwtToken->addOrUpdateJtiProperty('1234567890'); // it is a string. nothing more, nothing less.
// issued at
$jwtToken->addOrUpdateIatProperty('305078400'); // my birthday...
// when this incarnation of the jwt will die as a UTC timestamp
$jwtToken->addOrUpdateExpProperty('1887525317'); // when the T-800 comes to kill Sarah Connor
// the subject-- I use this for the publicly facing user ID
$jwtToken->addOrUpdateSubProperty('bradchesney79@gmail.com');
// ...I'll be honest. I don't use the not before field.
// It isn't useful to me in my software designs.
// But, it will throw an exception if you try to use it before allowed.
// $jwtToken->addOrUpdateNbfProperty(0); // January 1st, 1970
// One of many allowable custom, private claims-- but, beware, smaller the better.
$jwtToken->addOrUpdateCustomClaim('key','value');

$jwtToken->createToken();

echo $jwtToken->getToken();
```

### Read the token string:




### Validate a token:


### Revoke a token:


### Read token claims:


### Edit token claims:


### Append/update token claims:


### Remove token claims:


## Step A - Test:

Ensure that the phpdbg and xdebug extensions are avaialble to make developer life easier on yourself

Run the tests with the PHPUnit installed in the dev dependencies

```bash
vendor/bin/phpunit --coverage-clover /tmp/clover/clover.xml --coverage-html /tmp/clover
```

ToDo:

- Write last few tests for token validation

- Make the library available on packagist

- Finish code coverage phpdoc blocks for tests

- Make the README not awful

- As part of making the README not awful, create actual usage instructions

- Use https://gist.github.com/soulmachine/b368ce7292ddd7f91c15accccc02b8df as the basis for instructions on how to use this library


Caveats:

- Use is limited to PHP 7.x platforms

- I am not positive this library is production ready yet

- I have made decisions that force you to use this library in the closest to best practices using a specific secret key as I could manage. Other libraries allow you more freedom-- potentially to shoot yourself in the foot.

- There is no storage of who or what tokens are out there. You cannot see if one exists. You can only validate and leverage tokens that come back to you.

- Banning isn't part of the JWT standard-- but, it seemed like a simple to create and convenient mechanism to expose-- and no one has to use that functionality.