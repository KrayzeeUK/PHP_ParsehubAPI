[![Total Downloads](https://img.shields.io/packagist/dt/krayzeeuk/parsehubapi.svg)](https://packagist.org/packages/krayzeeuk/parsehubapi)
[![Latest Stable Version](https://img.shields.io/packagist/v/krayzeeuk/parsehubapi.svg)](https://packagist.org/packages/krayzeeuk/parsehubapi)

# PHP_ParsehubAPI
PHP_ParsehubAPI is a PHP class to allow for easy calling of Parsehub API Commands.

## Requirements
PHP_ParsehubAPI can be run with PHP 7.2 and later

## Installation
PHP_ParsehubAPI can be installed with [Composer](https://getcomposer.org/).

To get the latest stable version of PHP_ParsehubAPI use:
```bash
composer require krayzeeuk/parsehubapi
````

## Basic Usage

```php
<?php

	use KrayZeeUK\API\ParsehubAPI
	
	$parsehubProject = new ParsehubAPI( "your API Key" );

	$projectData =  $parsehubProject->getProject( "Your Project Key" );

	var_dump($projectData);
```