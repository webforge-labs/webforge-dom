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

Lets say, we're operating on this html:

```html
<h1>Please Login</h1>

<form class="main" action="" method="POST">
  <fieldset class="user-data group">
    <input type="text" name="email" value="" /><br />
    <br />
    <input type="text" name="name" value="" /><br />
  </fieldset>
  <fieldset class="password group">
    Bitte geben sie ein Passwort ein:<br />
    <input type="password" name="pw1" value="" /><br />
    Bitte best&auml;tigen sie das Passwort:<br />
    <input type="password" name="pw2" value="" /><br />
  </fieldset>
  <input type="hidden" name="submitted" value="true" />
  <input type="submit">
</form>

<p class="pw-reset"><a href="/forgotten">Reset your password</a></p>
```


```php
$hiddenInput = Query::create('form.main', $this->html)->find('input[name="submitted"]');

// returns an instanceof Query with the html: <input type="hidden" name="submitted" value="true" />
```

```php
$fieldsetUserData = Query::create('form.main', $this->html)->find('fieldset')->eq(0);
```
returns an instanceof Query with the html: `<fieldset class="user-data group"><input .. <br /><input ..<br /></fieldset>`

```php
$url = Query::create('a', '<a href="http://www.ps-webforge.com" class="def"></a>')->attr('href');
// 'http://www.ps-webforge.com'
```
works like the jquery `attr`

```php
$innerHtml = Query::create('fieldset:first', $this->html)->html();

// '<input type="text" name="email" value="" /><br /><br /><input type="text" name="name" value="" /><br />'
```
Returns the html from all children combined.

```php
$html = Query::create('fieldset:first [name="email"]', $this->html)->outerHtml();

// '<input type="text" name="email" value="" />'
```
Returns the html from the element and all its children combined.

Note: The output from outerHtml() and html() is not exactly identical to the parts in the original html, because it is reformatted internally by the PHP-DOM functions.

```php
$true = Query::create('fieldset:first', $this->html)->hasClass('user-data');
```

```php
$true = Query::create('fieldset:first', $this->html)->hasClass('group');
```

```php
$false = Query::create('fieldset:first', $this->html)->hasClass('user-data.group');
```
Checks if the element has a specific, single class.

## issues

  - a selector like `something:eq(0)` does not exactly behave like it would be in jquery. Currently it is rewritten to :nth-of-type(0). But this is not exactly the same
  - it's currently not possible to do a find() on a set of matched elements (PR welcome)
  - hasClass does not work with combined classes like: `user-data.group`
