<?php

namespace Webforge\DOM;

use Webforge\DOM\XMLUtil as xml;

class QueryTest extends \Webforge\Code\Test\Base {

  protected $formHTML = <<< 'HTML_FORM'
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
  
  public function setUp() {
    $this->fqn = 'Webforge\\DOM\\Query';
    parent::setUp();    

    $this->html = $this->formHTML;
    $this->doc = xml::doc($this->html);

    $this->form = new Query('form.main', $this->formHTML);
  }

  // @params $selector, $htmlString
  public function testConstructors_String_String() {
    $this->assertQueryObject(new Query('form.main', $this->formHTML), $this->doc, 'form.main');
  }

  public function testConstructBullShitTHrowsEcxeption() {
    $this->setExpectedException('BadMethodCallException');

    new Query(array(), new \stdClass);
  }

  // @params $selector, $doc
  public function testConstructors_String_Doc() {
    $this->assertQueryObject(new Query('form.main', $this->doc), $this->doc, 'form.main');
  }

  public function testConstructors_OnlyDoc() {
    $this->assertQueryObject(new Query($this->doc), $this->doc, NULL);
  }

  public function testConstructors_OnlyQuery() {
    $query = new Query($this->doc);
    $this->assertQueryObject(new Query($query), $this->doc, NULL);
  }

  public function testConstructors_QueryAndSelector() {
    $this->setExpectedException('InvalidArgumentException');

    $query = new Query($this->doc);
    new Query($query, 'something');
  }

  // @params $selector, Query $query
  public function testConstructors_String_Query() {
    $this->assertQueryObject($query = new Query('form.main', $this->doc), $this->doc, 'form.main');

    $this->assertQueryObject($query = new Query('form.main', $query), $this->doc, 'form.main');

  }

  public function testConstructor_Regression() {
    $form = new Query('form.main', $this->formHTML);
    $fieldset = new Query('fieldset.password.group', $form);
    $input = new Query('input[type="password"][name="pw2"]', $fieldset);
    
    $this->assertTrue($input->getElement()->hasAttribute('value'));
  }

  public function testFindReturnsAQueryAsWell() {
    $inputs = $this->form->find('input');
    $this->assertInstanceof($this->fqn, $inputs);
  }

  public function testCountRepresentsLengthOfResult_ForSingleResult() {
    $this->assertCount(1, $this->form);
  }

  public function testCountRepresentsLengthOfResult_ForMultiResults() {
    $this->assertCount(6, $this->form->find('input'));
  }

  /**
   * @dataProvider provideCSSQueryies
   */
  public function testCSSInputResults($index, $matchedElement, $type, $name) {
    $inputs = $this->form->find('input');
    
    $inputDOMElement = $inputs->get($index);
    $input = new Query($inputDOMElement);

    $this->assertEquals($matchedElement, xml::export($inputDOMElement), 'Input does not match exepcted element for: '.$index);

    $this->assertEquals($name, $input->attr('name'), 'name does not match');
    $this->assertEquals($type, $input->attr('type'), 'type does not match');
  }
  
  public static function provideCSSQueryies() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test(0, '<input type="text" name="email" value="" />', 'text', 'email');
    $test(1, '<input type="text" name="name" value="" />', 'text', 'name');
    $test(2, '<input type="password" name="pw1" value="" />', 'password', 'pw1');
    $test(3, '<input type="password" name="pw2" value="" />', 'password', 'pw2');
    $test(4, '<input type="hidden" name="submitted" value="true" />', 'hidden', 'submitted');
    $test(5, '<input type="submit" />', 'submit', NULL);
  
    return $tests;
  }

  public function testReturnsHTMLContentsOFElements() {
    $query = new Query('fieldset', $this->doc);

    $this->assertEquals(
      '<input type="text" name="email" value=""/><br/>'.
      '<br/>'.
      '<input type="text" name="name" value=""/><br/>',
      $query->html()
    );
  }

  public function testFindWithFullHTMLDocument_UsesHTMLAsFirstSelector() {
    $html = new Query('html', $this->htmlDoc);
    
    $this->assertEquals(1, count($html));
    $this->assertEquals(1, count($html->find('body.home')));
  }

  public function testTextReturnsTheTextValueOfANode() {
    $this->assertEquals('something inner', Query::create('wrapper span:first', '<wrapper><span>something inner</span></wrapper>')->text());
  }

  public function testLiteralSelectorSavesTheLiteralSelector() {
    $firstQuery = Query::create('wrapper span:first', '<wrapper><span>something inner</span></wrapper>');

    $this->assertEquals('wrapper span:first', $firstQuery->getLiteralSelector());
  }

  public function testSelectEQSearchesLikeNthOfTypeButOneBased() {
    $firstInput = new Query('form.main fieldset.user-data.group input:nth-of-type(2)', $this->formHTML);
    $this->assertEquals(array('<input type="text" name="name" value="" />'), $firstInput->export());
    $firstInput = new Query('form.main fieldset.user-data.group input:eq(1)', $this->formHTML);
    $this->assertEquals(array('<input type="text" name="name" value="" />'), $firstInput->export());

    $firstInput = new Query('form.main fieldset.user-data.group input:nth-of-type(1)', $this->formHTML);
    $this->assertEquals(array('<input type="text" name="email" value="" />'), $firstInput->export());
    $firstInput = new Query('form.main fieldset.user-data.group input:eq(0)', $this->formHTML);
    $this->assertEquals(array('<input type="text" name="email" value="" />'), $firstInput->export());
  }

  public function testSelectFirst() {
    $firstInput = new Query('form.main fieldset.user-data.group input:first', $this->formHTML);
    $this->assertEquals(array('<input type="text" name="email" value="" />'), $firstInput->export());
  }

  /*
    @TODO: FIXME:

   :first does return 3 elements here (nth of type ..)
  public function testEQ0ReturnsQueryObjectWithIndex0() {
    $first = $this->form->find('input:first');

    $this->assertEquals(
      $first->export(),
      $this->form->find('input')->eq(0)->export(),
    );
  }
  */
  
  public function testHasClassReturnsIfAttributeHasSomeClass() {
    $form = new Query('form.main', $this->formHTML);
    $this->assertTrue($form->hasClass('main'));
    $this->assertFalse($form->hasClass('blubb'));
    
    $fieldset = $form->find('fieldset.password');
    $this->assertTrue($fieldset->hasClass('password'));
    $this->assertTrue($fieldset->hasClass('group'));
    $this->assertFalse($fieldset->hasClass('password group'));
    $this->assertFalse($fieldset->hasClass('pass'));
  }

  public function testIsCHeckedRepresentsHTMLAttribute() {
    $this->assertTrue(Query::create('input', '<wrapper><input type="checkbox" checked="checked" /></wrapper')->isChecked());
    $this->assertFalse(Query::create('input', '<wrapper><input type="checkbox" /></wrapper')->isChecked());
  }

  public function testIsSelectedRepresentsHTMLAttribute() {
    $this->assertTrue(Query::create('option', '<wrapper><option selected="selected"></option></wrapper')->isSelected());
    $this->assertFalse(Query::create('option', '<wrapper><option></option></wrapper')->isSelected());
  }

  protected function assertQueryObject($query, \DOMDocument $doc, $selector) {
    $this->assertInstanceOf($this->fqn, $query, 'is not a query object');

    $this->assertInstanceOf('DOMDocument', $query->getDocument());
    $this->assertEquals($selector, $query->getLiteralSelector(), 'Selector does not match');
  }

  public function testOuterHTMLReturnsTheFullHTMLOfTheElement() {
    $email = new Query('form.main fieldset.user-data.group input:first', $this->formHTML);
    $this->assertEquals(
      '<input type="text" name="email" value="" />',
      $email->outerHtml()
    );


    $fs = new Query('form.main fieldset:first', $this->formHTML);
    // its not formatted the same (sadly)
    $this->assertEquals(
      '<fieldset class="user-data group"><input type="text" name="email" value="" /><br /><br /><input type="text" name="name" value="" /><br /></fieldset>',
      $fs->outerHtml()
    );
  }
}
