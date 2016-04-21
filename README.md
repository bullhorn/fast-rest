# Bullhorn FastREST

[![Build Status](https://travis-ci.org/bullhorn/fast-rest.svg)](https://travis-ci.org/bullhorn/fast-rest)

## Contribute

There are many ways to **[contribute](https://github.com/bullhorn/career-portal/blob/master/CONTRIBUTING.md)** to Bullhorn FastREST.
* **[Submit bugs](https://github.com/bullhorn/fast-rest/issues)** and help us verify fixes as they are checked in.
* Review **[source code changes](https://github.com/bullhorn/fast-rest/pulls)**.
* **[Contribute bug fixes](https://github.com/bullhorn/fast-rest/master/CONTRIBUTING.MD)**.

## (Internal) Before Committing
* Make sure to write unit tests for any new code.
* Run all unit tests, fix any errors.
* Push
* Submit Pull Request

## Documentation
#### Requirements
Prerequisite packages are:
 * Phalcon >2.0


## Examples

 * See https://github.com/bullhorn/fast-rest/tree/master/examples/

## Installation

### Installing via Composer

Install composer in a common location or in your project:

```bash
curl -s http://getcomposer.org/installer | php
```

Create the composer.json file as follows:

```json
{
    "require": {
        "bullhorn/fast-rest": "dev-master"
    }
}
```

Run the composer installer:

```bash
php composer.phar install
```

### Installing via GitHub

Just clone the repository in a common location or inside your project:

```
git clone https://github.com/bullhorn/fast-rest.git
```


## Autoloading from the Incubator

Add or register the following namespace strategy to your Phalcon\Loader in order
to load classes from the incubator repository:

```php

$loader = new Phalcon\Loader();

$loader->registerNamespaces(array(
     'Bullhorn\\FastRest' => '/path/to/bullhorn/fast-rest/Library/'
));

$loader->register();
```