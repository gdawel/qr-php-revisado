<?php

class xmlParser {
	
	private $dom;
	
	public function __construct($xml) {
		$parser = xml_parser_create();
		if (!xml_parse($parser, $xml)) {
			throw new Exception ("XML parsing error: (". xml_get_error_code($parser) .") " . xml_error_string (xml_get_error_code($parser)));
		} else {
			$this->dom = new DOMDocument();
			$this->dom->loadXml($xml);
		}
	}

	public function getResult($node = null) {
		$result = $this->toArray($this->dom);
		if ($node) {
			if (isset($result[$node])) {
				return $result[$node];
			} else {
				throw new Exception ("XML parsing error: undefined index [$node]");
			}
		} else {
			return $result;
		}
	}
 
	private function toArray($node) {
		$occurance = array();
		if($node->hasChildNodes()) {
			foreach($node->childNodes as $child) {
				if (!isset($occurance[$child->nodeName])) {
					$occurance[$child->nodeName]=null;
				}
				$occurance[$child->nodeName]++;
			}
		}
		if (isset($child)) {
			if($child->nodeName == '#text') {
				$result = html_entity_decode(htmlentities($node->nodeValue, ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'ISO-8859-15');
			}  else {
				if($node->hasChildNodes()) {
					$children = $node->childNodes;
					for($i=0; $i<$children->length; $i++) {
						$child = $children->item($i);
						if($child->nodeName != '#text') {
							if($occurance[$child->nodeName] > 1) {
								$result[$child->nodeName][] = $this->toArray($child);
							} else {
								$result[$child->nodeName] = $this->toArray($child);
							}
						} else if ($child->nodeName == '0') {
							$text = $this->toArray($child);
							if (trim($text) != '') {
								$result[$child->nodeName] = $this->toArray($child);
							}
						}
					}
				} 
				if($node->hasAttributes()) { 
					$attributes = $node->attributes;
					if(!is_null($attributes)) {
						foreach ($attributes as $key => $attr) {
							$result["@".$attr->name] = $attr->value;
						}
					}
				}
			}
			return $result;
		} else {
			return null;
		}
	}
 
}
	

?>