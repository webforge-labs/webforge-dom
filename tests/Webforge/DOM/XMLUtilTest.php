<?php

namespace Webforge\DOM;

use Webforge\DOM\XMLUtil as xml;

class XMLUtilTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Webforge\\DOM\\XMLUtil';
    parent::setUp();

    $docs = self::provideHTMLDocuments();
    $this->html = $docs[0][0];
  }

  /**
   * @dataProvider provideHTMLDocuments
   */
  public function testHTMLDocumentsCreatingWithDoc($html) {
    $this->assertInstanceOf('DOMDocument', xml::doc($html));
  }

  /**
   * @dataProvider provideHTMLSnippets
   */
  public function testHTMLDocumentsCreatingWithDocPart($htmlSnippet) {
    $this->assertInstanceOf('DOMDocument', xml::docPart($htmlSnippet));
  }

  public function testXPATHReturnsAnResult() {
    $this->assertInternalType('array', $result = xml::xpath(xml::doc($this->html), '/html/body/div'));
    $this->assertCount(1, $result);

    $this->assertInstanceOf('DOMElement', $result[0]);
  }

  public function testCSSQueryReturnsAResult() {
    $this->assertInternalType('array', $result = xml::query(xml::doc($this->html), 'html > body > div'));
    $this->assertCount(1, $result);

    $this->assertInstanceOf('DOMElement', $result[0]);
  }
  
  public static function provideHTMLDocuments() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test(<<<'HTML'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <body>
    <div class="wrapper"></div>
  </body>
</html>
HTML
    );
  
    return $tests;
  }

  public static function provideHTMLSnippets() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };

    $test(<<<'HTML'
<div class="wrapper"></div>
HTML
    );

    $test(<<<'HTML'
<form class="main" action="" method="POST"><fieldset class="user-data group"><input type="text" name="email" value="p.scheit@ps-webforge.com"/><br/><br/><input type="text" name="name" value=""/><br/></fieldset><fieldset class="password group">
    Bitte geben sie ein Passwort ein:<br/><input type="password" name="pw1" value=""/><br/>
    Bitte best├ñtigen sie das Passwort:<br/><input type="password" name="pw2" value=""/><br/></fieldset>
  <input type="hidden" name="submitted" value="true"/><input type="submit"/>
</form>
HTML
    );
  
    $test(<<<'HTML'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <body>
    <div class="wrapper"></div>
  </body>
</html>
HTML
    );
  
    return $tests;
  }
}
