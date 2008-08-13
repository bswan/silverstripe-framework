<?php

/**
 * CSSContentParser enables parsing & assertion running of HTML content via CSS selectors.
 * 
 * It works by converting the content to XHTML using tidy, rewriting the CSS selectors as XPath queries, and executing
 * those using SimpeXML.
 * 
 * It was built to facilitate testing using PHPUnit and contains a number of assert methods that will throw PHPUnit
 * assertion exception when applicable.
 * 
 * @package sapphire
 * @subpackage core
 */
class CSSContentParser extends Object {
	protected $simpleXML = null;
	
	function __construct($content) {
		$CLI_content = escapeshellarg($content);
		$tidy = `echo $CLI_content | tidy -n -q -utf8 -asxhtml 2> /dev/null`;
		$tidy = str_replace('xmlns="http://www.w3.org/1999/xhtml"','',$tidy);
		$tidy = str_replace('&#160;','',$tidy);
		$this->simpleXML = new SimpleXMLElement($tidy);
	}
		
	/**
	 * Returns a number of SimpleXML elements that match the given CSS selector.
	 * Currently the selector engine only supports querying by tag, id, and classs
	 */
	function getBySelector($selector) {
		$xpath = $this->selector2xpath($selector);
		return $this->simpleXML->xpath($xpath);
	}
	
	/**
	 * Converts a CSS selector into an equivalent xpath expression.
	 * Currently the selector engine only supports querying by tag, id, and classs
	 */
	function selector2xpath($selector) {
		$parts = preg_split('/\\s+/', $selector);
		$xpath = "";
		foreach($parts as $part) {
			if(preg_match('/^([A-Za-z][A-Za-z0-9]*)/', $part, $matches)) {
				$xpath .= "//$matches[1]";
			} else {
				$xpath .= "//*";
			}
			$xfilters = array();
			if(preg_match('/#([^#.\[]+)/', $part, $matches)) {
				$xfilters[] = "@id='$matches[1]'";
			}
			if(preg_match('/\.([^#.\[]+)/', $part, $matches)) {
				$xfilters[] = "contains(@class,'$matches[1]')";
			}
			if($xfilters) $xpath .= '[' . implode(',', $xfilters) . ']';
		}
		return $xpath;		
	}

}