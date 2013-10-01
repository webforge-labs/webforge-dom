webforge-dom
==========

A very very very very very simple aproach to build an api like jquery for html (testing) in php.
It provides some helper for DOM*** Classes in PHP (that were difficult to debug before).

uses the symfony CSSSelector class

## installation
Use [Composer](http://getcomposer.org) to install.
```
composer require -v --prefer-source webforge/dom:1.0.*
```

to run the tests use:
```
phpunit
```

## usage

```php
$result = Query::create('html body div#wrapper', $html)->find('section.blog');
```

## issues

a selector like `something:eq(0)` does not exactly behave like it would be in jquery. Currently it is rewritten to :nth-of-type(0). But this is not exactly the same
