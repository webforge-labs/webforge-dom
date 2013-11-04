<?php

namespace Webforge\DOM;

use BadMethodCallException;
use InvalidArgumentException;
use Webforge\Common\Util as Code;
use Webforge\DOM\XMLUtil as xml;
use DOMDocument;
use DOMElement;
use DOMText;
use Webforge\Common\Preg;

class Query extends \Webforge\Collections\ArrayCollection {

  /**
   * @var int
   */
  public $length = 0;
  
  /**
   * @var string its normalized (converted to css that is understandable by symfony CSSSelector)
   */
  protected $selector;

  /**
   * @var string as typed in
   */
  protected $literalSelector;
  
  /**
   * @var Query
   */
  protected $prevObject;
  
  /**
   * @var DOMDocument
   */
  protected $document;
  
  /**
   * Creates a new DOM Query Object
   *
   * The combination of construct parameters are:
   * 
   * @params $selector, $doc
   * @params $selector, $htmlString
   * @params $selector, $page
   *
   * @param string $selector a Query-Like-Selector (see XMLUtil::query)
   * @param DOMDocument $doc the document to query against
   * @param string $htmlString some HTML Document or HTML Document Part
   *
   * Attention: provide full Documents always as doc. Create the DOMDocument with the XMLUtil::doc or docPart helper
   * Per Default every string is considered as a HTML part and is converted with XMLUtil::docPart to a DOMDocument
   */
  public function __construct() {
    $args = func_get_args();
    $num = count($args);
    
    $constructor = NULL;
    if ($num === 2) {
      list ($arg1,$arg2) = $args;
     
      /* alle mit $selector am Anfang */
      if (is_string($arg1)) {
        if (is_string($arg2)) {
          return $this->constructSelector($arg1, xml::docPart($arg2));
        } elseif ($arg2 instanceof DOMDocument) {
          return $this->constructSelector($arg1, $arg2);
        } elseif ($arg2 instanceof DOMElement) {
          return $this->constructSelector($arg1, xml::docPart($arg2));
        } elseif ($arg2 instanceof \Psc\HTML\Page) {
          return $this->constructSelector($arg1, xml::doc($arg2->html()));
        } elseif( $arg2 instanceof \Psc\HTML\Tag) {
          return $this->constructSelector(
            $arg1, 
            $arg2->getTag() === 'html'
              ? xml::doc($arg2->html())
              : xml::docPart($arg2->html())
          );
        } elseif ($arg2 instanceof \Psc\HTML\HTMLInterface) {
          return $this->constructSelector($arg1, xml::docPart($arg2->html()));
        } elseif ($arg2 instanceof Query) {
          // das ist eher find() und nicht clone wie nur Query args
          return $this->constructSelector($arg1, xml::docPart($arg2->html())); 
        }
      } elseif ($arg1 instanceof Query) {
        return $this->constructQuery($arg1, $arg2);
      }
    } elseif ($num === 1) {
      $arg1 = $args[0];
      
      if ($arg1 instanceof DOMDocument) {
        return $this->constructDOMDocument($arg1);
      } elseif ($arg1 instanceof DOMElement) {
        return $this->constructDOMElement($arg1);
      } elseif ($arg1 instanceof Query) {
        return $this->constructQuery($arg1);
      }
    }
    
    $signatur = array_map(
      function ($arg) {
        return Code::getType($arg);
      }, 
      $args
    );

    throw new BadMethodCallException('Cannot construct Query object. There is no constructor for the parameters: '.implode(', ',$signatur));
  }

  public static function create($arg1, $arg2 = NULL) {
    return new static($arg1, $arg2);
  }

  protected function constructSelector($selector, DOMDocument $doc) {
    $this->setSelector($selector);
    $this->document = $doc;
    
    $elements = $this->match($this->document, $this->selector);
    parent::__construct($elements);
    $this->length = count($this);
  }
  
  protected function constructDOMDocument(DOMDocument $doc) {
    $this->document = $doc;
    $this->setSelector("");
    
    parent::__construct(array($doc));
    $this->length = count($this);
  }

  protected function constructDOMElement(DOMElement $el) {
    $this->document = xml::doc($el);
    $this->setSelector("");
    parent::__construct(array($el));
    $this->length = count($this);
  }

  /**
   * Clones a Query Object
   */
  protected function constructQuery(Query $query, $selector = NULL) {
    if ($selector !== NULL) {
      throw new InvalidArgumentException('You must not provide a selector if query is first argument. Use find for that(!)');
    }
    $this->setSelector($query->getSelector());
    $this->document = $query->getDocument();
    
    parent::__construct($query->toArray());
    $this->length = count($this);
  }

  /**
   * Helper to match a dom against a selector
   * @return array
   */
  protected function match(DOMDocument $doc, $selector) {
    return xml::query($doc, $selector);
  }  

  /**
   * Find an Element in the matched elements of this element
   * 
   * @return Query
   */
  public function find($selector) {
    if (!is_string($selector)) throw new \InvalidArgumentException('Kann bis jetzt nur string als find Parameter');

    $cnt = count($this);
    
    if ($cnt != 1) {
      throw new Exception('Kann bis jetzt nur find() auf Query-Objekten mit genau 1 Element. '.$this->selector.' ('.$cnt.')');
    }

    // erstellt ein Objekt mit dem Document als unser Element
    // mit den matched Elements als das Ergebnis des Selectors in diesem Document
    $Query = new self($selector, $this->getElement());
    $Query->setPrevObject($this);
    $Query->setSelector($this->selector.' '.$selector);
    
    return $Query;
  }

  /**
   * Reduce the set of matched elements to the one at the specified index.
   * 
   * @return Query
   */
  public function eq($index) {
    if ($index < 0) throw new InvalidArgumentException('I cannot do negative indizes!');
    
    return new static($this->get($index));
  }
  
  /**
   * Sets and parses the selector
   * 
   * The result set is not re-callculated
   */
  public function setSelector($selector) {
    
    $this->literalSelector = $selector;

    $this->selector = $selector;
    $this->selector = str_replace(':first', ':nth-of-type(1)', $this->selector);
    // nth-of-type is a NOT nice alias for eq but its 1-based (eq ist 0-based)
    // but its not the same! fix me!
    $this->selector = Preg::replace_callback(
      $this->selector, 
      '/:eq\(([0-9]+)\)/', 
      function ($m) { 
        return sprintf(':nth-of-type(%d)', $m[1]+1); 
      }
    );
    return $this;
  }

  /**
   * @return string
   */
  public function getSelector() {
    return $this->selector;
  }

  /**
   * @return string
   */
  public function getLiteralSelector() {
    return $this->literalSelector;
  }

  /**
   * Sets the object that does a find() before
   */
  public function setPrevObject(self $query) {
    $this->prevObject = $query;
    return $this;
  }

  /**
   * @return DOMDocument
   */
  public function getDocument() {
    return $this->document;
  }

  /**
   * Returns the first matching element (as DOMNode or Query)
   *
   * @TODO fixme: Boolean Trap(!)
   * @return NULL|DOMNode|Query
   */
  public function getElement($asQuery = FALSE) {
    if ($this->containsKey(0)) {
      return $asQuery ? new static($this->get(0)) : $this->get(0);
    }
    
    return NULL;
  }

  /**
   * @return NULL|DOMNode|Query
   */
  public function getQuery() {
    return $this->getElement($asQuery = TRUE);
  }

  /**
   * @TODO tweakme
   * @return bool
   */
  public function hasClass($class) {
    $classes = $this->attr('class');
    return in_array($class, explode(' ',$classes));
  }

  /**
   * @return bool
   */
  public function isChecked() {
    if (($el = $this->getElement(TRUE)) === NULL) return FALSE;
    
    return $el->attr('checked') === 'checked';
  }

  /**
   * @return bool
   */
  public function isSelected() {
    if (($el = $this->getElement(TRUE)) === NULL) return FALSE;
    
    return $el->attr('selected') === 'selected';
  }

  /**
   * Returns or sets the attribute of the first matched Element
   *
   * @param mixed $value if $value is provided the attribute is set to this value
   * @return string|NULL
   */
  public function attr($name, $value = NULL) {
    if (($el = $this->getElement()) === NULL) return NULL;

    if ($el->hasAttribute($name)) {
      if (func_num_args() == 2) {
        $el->setAttribute($name, $value);
      }

      return $el->getAttribute($name);
    }

    return NULL;
  }

  /**
   * Get the HTML -->contents<-- of the first element in the set of matched elements.
   * 
   * @return string
   */
  public function html() {
    if (($el = $this->getElement()) === NULL) return NULL;
    
    return implode('',xml::export($el->childNodes));
  }

  /**
   * Returns the first matched element as text
   * 
   * @return string
   */
  public function text() {
    if (($el = $this->getElement()) === NULL) return NULL;
    
    return $el->nodeValue;
  }

  /**
   * Returns an scalar of readable information about the matched elements
   *
   * use this as debug only, because its slow
   * @return scalar
   */
  public function export() {
    return xml::export(parent::toArray());
  }
}
