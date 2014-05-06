<?php

namespace Webforge\DOM;

use Webforge\DOM\XMLUtil as xml;

class ExamplesTest extends \Webforge\Code\Test\Base {

  protected $html = <<< 'HTML_FORM'
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
HTML_FORM;

  protected $htmlDoc = <<<'HTML'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
  <head>
    <title></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="content-language" content="de" />
  </head>
  <body class="home">
  dies ist der Inhalt der Startseite</body>
</html>
HTML;

  public function testFinding() {
    $hiddenInput = Query::create('form.main', $this->html)->find('input[name="submitted"]');

    $this->assertEquals('submitted', $hiddenInput->attr('name'));
  }

  public function testEqFinding() {
    $fieldsetUserData = Query::create('form.main', $this->html)->find('fieldset')->eq(0);

    $this->assertEquals('<fieldset class="user-data group"><input type="text" name="email" value="" /><br /><br /><input type="text" name="name" value="" /><br /></fieldset>', $fieldsetUserData->outerHtml());
  }
  
  public function testAttribute() {
    $url = Query::create('a', '<a href="http://www.ps-webforge.com" class="def"></a>')->attr('href');

    $this->assertEquals('http://www.ps-webforge.com', $url);
  }

  public function testInnerHTML() {
    $innerHtml = Query::create('fieldset:first', $this->html)->html();

    $this->assertEquals('<input type="text" name="email" value="" /><br /><br /><input type="text" name="name" value="" /><br />', $innerHtml);
  }

  public function testOuterHtml() {
    $html = Query::create('fieldset:first [name="email"]', $this->html)->outerHtml();

    $this->assertEquals('<input type="text" name="email" value="" />', $html);
  }

  public function testCSSClasses() {
    $true = Query::create('fieldset:first', $this->html)->hasClass('user-data');
    $this->assertTrue($true);

    $true = Query::create('fieldset:first', $this->html)->hasClass('group');
    $this->assertTrue($true);

    $false = Query::create('fieldset:first', $this->html)->hasClass('user-data.group');
    $this->assertFalse($false);
  }
}
