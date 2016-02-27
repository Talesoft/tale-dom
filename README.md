
# Tale Dom
**A Tale Framework Component**

# What is Tale Dom?



# Installation

Install via Composer

```bash
composer require "talesoft/tale-dom:*"
composer install
```

# Usage

## Parsing
```php

use Tale\Dom;

$element = Dom::fromString('<h1>Hello World!</h1>');

var_dump($element->getName()); //h1
var_dump($element->getText()); //Hello World!

```


# Manipulation
```php

use Tale\Dom;

$m = Dom::manipulate('<config />');

$m->append('db')
    ->append('host')->setText('localhost')
    ->after('password')->setText('12345')
    ->parent
  ->after('logging')
    ->append('adapter')->setText('file')
    ->append('path')->setText('./errors.log')
    
echo $m; //<config><db><host>localhost</host><password>12345</password>...</config>

```

...or shorter...
```php

use Tale\Dom;

$m = Dom::manipulate('
<config>
    <db>
        <host />
        <password />
    </db>
    <logging>
        <adapter />
        <path id="logPath" />
    </logging>
</config>');

$m->query('host')->setText('localhost');
$m->query('db > password')->setText('12345');
$m->query('logging adapter')->setText('file');
$m->query('#logPath')->setText('./errors.log');
    
echo $m; //<config><db><host>localhost</host><password>12345</password>...</config>

```

...or even shorter...
```php

use Tale\Dom;

$m = Dom::manipulate([
    'config' => [
        'host' => 'localhost',
        'password' => '12345'
    ],
    'logging' => [
        'adapter' => 'file',
        'path#logPath'
    ]
]);

$m->query('#logPath')->setText('./errors.log');
    
echo $m; //<config><db><host>localhost</host><password>12345</password>...</config>

```


## Dumping
```php

use Tale\Dom;

$element = Dom::fromString([
    'html' => [
        'head' => [
            'meta[charset="utf-8"]',
            'title' => 'My awesome Tale Dom Website!'
        ]
    ]
]);

$prettyFormatter = new Dom\Formatter(['pretty' => true]);
$htmlFormatter = new Dom\Html\Formatter(['pretty' => true]);

echo $element; //<html><head><meta charset="utf-8" /><title>...</html>

echo $element->getString($prettyFormatter);
/*
<html>
  <head>
    <meta charset="utf-8" />
    <title>My awesome Tale Dom Website!</title>
    ...
</html>
*/


echo $element->getString($htmlFormatter);
/*
<html>
  <head>
    <meta charset="utf-8">
    <title>My awesome Tale Dom Website!</title>
    ...
</html>
*/

```
