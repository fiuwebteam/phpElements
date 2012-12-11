<?php
//-----------------------------------------------------------------------------------------
class Element {
	//=====================================================================================
	public $type = "";
	public $attributes = array();
	public $children = array();
	public $parent = null;	
	//=====================================================================================
	function __construct($type, $children = null, $attributes = null, $parent = null) {
		$this->setType($type);
		$this->setAttributes($attributes);
		$this->addChild($children);
		$this->setParent($parent);		
	}
	//=====================================================================================
	function setType($type) {
		$this->type = $type;
	}
	//=====================================================================================
	function setAttributes($attributes) {
		if (is_array($attributes)) { $this->attributes = $attributes; }
	}
	//=====================================================================================
	function getAttributes() {
		return $this->attributes;
	}	
	//=====================================================================================
	function addChild($child) {
		if (is_object($child)) { $child->setParent($this); }
		else if (is_string($child)) { $this->children[] = $child;}
		else if (is_array($child)) {
	    	foreach ($child as $value) { $this->addChild($value); }	    	 
	    }
	}
	//=====================================================================================
	function setChildren(array $children) {
		$this->children = array();
		foreach ($children as $child) { $this->addChild($child); }
	}
	//=====================================================================================
	function getChild($x) {
		return $this->children[$x];
	}
	//=====================================================================================
	function getChildren() {
		return $this->children;
	}
	//=====================================================================================
	function setParent($parent) {
		// If this already has a parent, remove the connection
		if (is_object($this->parent)) {
			foreach ($this->parent->children as $key => $child) {
				if ($child == $this) { unset($this->parent->children[$key]); break; }
			}
		}
		$this->parent = $parent;
		$this->parent->children[] = $this;
	}
	//=====================================================================================
	function getParent() {
		return $this->parent;
	}
	//=====================================================================================
	function output() {
		$output = "<$this->type";
		foreach($this->attributes as $key => $value) {
			$value = str_replace("'", "&#39;", $value);
			if (!is_numeric($key)) { $output .= " $key='$value'"; } 
			else { $output .= " $key"; }
		}
		$output .= ">";
		foreach ($this->children as $value) {
			if (is_string($value)) {$output .= $value;}
			else if (is_object($value)) { $output .= $value->output(); }
		}
		$output .= "</$this->type>";
		return $output;
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class ValidElement extends Element {
	//=====================================================================================
	public $name = "";
	public $valid = true; 
	public $errorMessage = "There was an error with this input.";
	//=====================================================================================
	function __construct($name, $type, $children = null, $attributes = null, $parent = null) {		
		$this->name = $name;		
		if (isset($GLOBALS["validation"])) {
			foreach ($GLOBALS["validation"]->errors as $value) {
				foreach ($value as $element => $error) {
					if ($element == $name) {
						$this->setValid($error["valid"]);
						if (isset($error["message"])) { $this->setMessage($error["message"]);}						
						break;
					}
				}
			}
		}	
		parent::__construct($type, $children, $attributes, $parent);		
	}
	//=====================================================================================
	function setValid($valid) {
		$this->valid = $valid;
	}
	//=====================================================================================
	function setMessage($message) {
		$this->errorMessage = $message;
	}
	//=====================================================================================
	function output() {
		if (!$this->valid) {
			$this->addChild(new Div($this->errorMessage, array("class" => "errorMessage")));
		}
		return parent::output();
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class ChildlessElement extends Element {
	
//=====================================================================================
	function __construct($type, $attributes = null, $parent = null) {
		parent::__construct($type, null, $attributes, $parent);		
	}
	//=====================================================================================
	function output() {
		$output = "<$this->type ";
		foreach($this->attributes as $key => $value) {
			$value = str_replace("'", "&#39;", $value);
			if (!is_numeric($key)) { $output .= "$key='$value' "; } 
			else { $output .= "$key "; }
		}		
		$output .= "/>";		
		return $output;
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class A extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("a", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Body extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("body", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Br extends ChildlessElement {
	//=====================================================================================
	function __construct($attributes = null, $parent = null) {
		parent::__construct("br", $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Date extends ValidElement {
	//=====================================================================================
	function __construct($name, $attributes = null, $parent = null) {
		if (is_array($attributes)) {
			if ( !array_key_exists("id", $attributes)) { $attributes["id"] = $name;}
		} else { $attributes = array( "id" => $name ); }
		
		$years = array();
		if (isset($attributes["start"])) { $startYear = $attributes["start"]; } 
		else { $startYear = date("Y"); }
		
		if (isset($attributes["end"])) { $endYear = date("Y", strtotime($attributes["end"])); } 
		else { $endYear = $startYear + 30; }
		
		$years[] = new Option(" ", array("value" => "N/A"));
		for($x = $startYear; $x <= $endYear; $x++) {  $years[] = new Option("$x", array("value" => "$x")); }
		
		$months = array();
		$months[] = new Option(" ", array("value" => "N/A"));
		for($x = 1; $x <= 12; $x++) { $months[] = new Option(date("F", strtotime("2011-$x-01")), 
			array("value" => (($x < 10) ? "0$x" : "$x"))); }
		
		$days = array();
		$days[] = new Option(" ", array("value" => "N/A"));
		for($x = 1; $x <= 31; $x++) { $days[] = new Option("$x", array("value" => (($x < 10) ? "0$x" : "$x"))); }
		
		$children = array(
		
			new Label(isset($attributes["label"]) ? 
				$attributes["label"] : ucwords(strtolower(str_replace("_" , " ", $name))), array("for" => $name . "_month")),
			new Select($months, array("id" => $name . "_month", "name" => $name."[month]" )),
			new Select($days, array("id" => $name . "_day", "name" => $name."[day]" )),
			new Select($years, array("id" => $name . "_year", "name" => $name."[year]" )),
		);
		
		if (isset($attributes["label"])) {unset($attributes["label"]);}
		if (isset($attributes["start"])) {unset($attributes["start"]);}
		if (isset($attributes["end"])) {unset($attributes["end"]);}
		
		parent::__construct($name, "div", $children, $attributes, $parent); 
	}	
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Div extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("div", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Fieldset extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("fieldset", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class File extends Input {
	//=====================================================================================
	function __construct($name, $attributes = null, $parent = null) {
		if ($attributes == null) { $attributes = array("type" => "file"); } 
		else { $attributes["type"] = "file"; }
		parent::__construct($name, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Form extends Element {
	// Default upload file of 500 KB. Can be altered of coarse when this class is called.
	public $maxFileSize = 500;
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("form", $children, $attributes, $parent);		
	}
	//=====================================================================================
	// Checks to see if there's a hidden input stating the MAX_FILE_SIZE
	function hasMaxFileSizeInput($nodes) {
		foreach($nodes as $value) {
			if (is_object($value) &&
				$value->type == "input" &&
				isset($value->attributes["type"]) &&
				 isset($value->attributes["name"]) &&
				$value->attributes["type"] == "hidden" &&
				$value->attributes["name"] == "MAX_FILE_SIZE" ) {
				return true;
			}
			if (!empty($value->children)) {
				return $this->hasMaxFileSizeInput($value->children);
			}
		}
		return false;
	}
	//=====================================================================================
	function output() {
		// If this is form to upload a file and the MAX FILE SIZE hidden field has not been set
		// create one with the preset default size.
		if (isset($this->attributes["enctype"]) && 
			$this->attributes["enctype"] == "multipart/form-data" && 
			!$this->hasMaxFileSizeInput($this->children)) {				
			$this->addChild(
				new INPUT( "MAX_FILE_SIZE", array("type" => "hidden", "value" => $this->maxFileSize, "label" => false) )
			);
		}
		// If id is set somewhere, keep it in a hidden input so as not to lose it.
		if (isset($_REQUEST["id"])) {
			$this->addChild(
				new INPUT("id", array("type" => "hidden", "value" => $_REQUEST["id"]))
			);
		}
		return parent::output();
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class H1 extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("h1", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class H2 extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("h2", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class H3 extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("h3", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class H4 extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("h4", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class H5 extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("h5", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Head extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("head", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Hr extends ChildLessElement {
	//=====================================================================================
	function __construct($attributes = null, $parent = null) {
		parent::__construct("hr", $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class HTML extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("html", $children, $attributes, $parent);		
	}
	//=====================================================================================
	function output() {
		$output = "<!DOCTYPE html>";
		$output .= parent::output();
		return $output;
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Img extends ChildLessElement {
	//=====================================================================================
	function __construct($attributes = null, $parent = null) {
		parent::__construct("img", $attributes, $parent);		
	}	
	//=====================================================================================
}

//-----------------------------------------------------------------------------------------
class Input extends ValidElement {
	//=====================================================================================
	function __construct($name, $attributes = null, $parent = null) {		
		if (is_array($attributes)) {
			if ( array_key_exists("id", $attributes)) { $id = $attributes["id"]; }
			else { $attributes["id"] = $id = $name;}			
			if ( !array_key_exists("name", $attributes)) { 
				$attributes["name"] = $id; 
			}
		} else {
			$attributes = array(
				"id" => $name,
				"name" => $name
			);
		}	
		parent::__construct($name, "input", null, $attributes, $parent);
	}
	//=====================================================================================
	function output() {
		$output = "<$this->type ";
		$check = false;
		$isRadio = false;
		foreach($this->attributes as $key => $value) {
			$value = str_replace("'", "&#39;", $value);
			if ($key == "type" && ($value == "radio" || $value == "checkbox")) { 
				$check = true;
				if ($value == "radio") { $isRadio = true; }
			}
			if (
				$key == "div" ||
				$key == "label" || 
				(
					$key == "value" && 
					isset($this->attributes["name"]) && 
					isset($_REQUEST[$this->attributes["name"]])
				)
				) { continue; } 
			else if (!is_numeric($key)) { $output .= "$key='$value' "; } 
			else { $output .= "$key "; }
		}
		
		if ($check) {
			$output .= "value='".$this->name."' ";
			
			if (isset($this->attributes["name"]) ) {
				if(strpos($this->attributes["name"], "[") !== false) {
					$structure = explode("[", $this->attributes["name"]);
					$whereAmI = $_REQUEST;
					foreach($structure as $key => $value) { 
						$value = str_replace("]", "", $value);
						if (isset($whereAmI[$value])) { $whereAmI = $whereAmI[$value];}
						else {break;}
						if (is_string($whereAmI)) {
							$output .= "checked='checked' ";
							break;
						}
					}
				} else if (
					isset($_REQUEST[$this->attributes["name"]]) &&
					$_REQUEST[$this->attributes["name"]] == $this->name	
				) {
					$output .= "checked='checked' ";
				}
			}	
			
		} else if ( isset($this->attributes["name"]) && 
			isset($_REQUEST[$this->attributes["name"]])) { 
				$output .= "value='".str_replace("'", "&#39;", $_REQUEST[$this->attributes["name"]])."' ";				
		}
		
		if (!array_key_exists("type", $this->attributes)) { $output .= "type='text' "; }
		
		$output .= "/>";

		$label = true;
		
		if (array_key_exists("type", $this->attributes) 
			&& $this->attributes["type"] == "hidden") { $label = false; }
		
		if (array_key_exists("label", $this->attributes) 
			&& $this->attributes["label"] == false) { $label = false; }
		
		if ($label) {
			if (isset($this->attributes["label"])) { $label = $this->attributes["label"]; } 
			else { $label = ucwords(strtolower(str_replace("_" , " ", $this->attributes["id"]))); }
			
			if (!$check) {
				$outputArray = array(
					new Label( $label, array("for" => $this->attributes["id"] ) ),
					" $output"
				);
			} else {
				$outputArray = array(
					"$output ",
					new Label( $label, array("for" => $this->attributes["id"] ) )
				);
			}
			
			if (!$this->valid && !$isRadio) {
				$outputArray[] = new Div($this->errorMessage, array("class" => "errorMessage"));
			}
			$outputDivAttributes = isset($this->attributes["div"]) ? $this->attributes["div"] : array( "id" => ($this->attributes["id"]."Div") );
			$outputDiv = new Div(
				$outputArray,				
				$outputDivAttributes
			);
			return $outputDiv->output();
		} else { return $output; }
		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Label extends Element{
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("label", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Legend extends Element{
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("legend", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Li extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("li", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Link extends ChildlessElement {
	//=====================================================================================
	function __construct($attributes = null, $parent = null) {
		parent::__construct("link", $attributes, $parent);		
	}	
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Meta extends ChildlessElement {
	//=====================================================================================
	function __construct($attributes = null, $parent = null) {
		parent::__construct("meta", $attributes, $parent);		
	}	
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Option extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("option", $children, $attributes, $parent);		
	}
	
	//=====================================================================================
	function output () {
		$output = "<$this->type ";
		
		if (!isset($this->attributes["value"]) && isset($this->children[0]) ) {
			$output .= "value='{$this->children[0]}'";
			$this->attributes["value"] = $this->children[0];
		}
		
		foreach($this->attributes as $key => $value) {
			$value = str_replace("'", "&#39;", $value);
			if (!is_numeric($key)) { $output .= "$key='$value' "; } 
			else { $output .= "$key "; }
		}
		
		if ( 
			isset($_REQUEST[$this->parent->attributes["name"]]) &&
			isset($this->attributes["value"]) &&
			$_REQUEST[$this->parent->attributes["name"]] == $this->attributes["value"] ) {
			$output .= "selected='selected' ";
		}
		
		$output .= ">";
		foreach ($this->children as $value) {
			if (is_string($value)) {$output .= ucwords(strtolower(str_replace("_" , " ", $value))) ;}
			else if (is_object($value)) { $output .= $value->output(); }
		}
		$output .= "</$this->type>";
		
		return $output;
	}	
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class P extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("p", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Radios extends ValidElement {
	//=====================================================================================
	function __construct($name, $options, $attributes = null, $parent = null) {
		if (is_array($attributes)) {
			if (!array_key_exists("id", $attributes)) { $attributes["id"] = $name;}
		} else {
			$attributes = array( "id" => $name );
		}
		
		$children = array();
		if (isset($attributes["label"])) {
			$children[] = new Legend($attributes["label"]);
			unset($attributes["label"]);
		} else {
			$children[] = new Legend( ucwords(strtolower(str_replace("_" , " ", $attributes["id"]))));
		}
		
		foreach ($options as $key => $value) {
			if (is_array($value)) {
				$children[] = new Input($value["id"], 
					array("label" => $value["label"], "type" => "radio", "name" => $name, "div" => array("class" => "radioDiv") ));
			} else if (is_string($value)) {
				$children[] = new Input($value, 
					array("type" => "radio", "name" => $name, "div" => array("class" => "radioDiv") ));
			}			
		}
		parent::__construct($name, "fieldset", $children, $attributes, $parent);
	}
	//=====================================================================================
	function output() {
		if (!$this->valid) {
			$children = $this->children;
			$this->children = array();
			$this->addChild($children[0]);
			$this->addChild(new Div($this->errorMessage, array("class" => "errorMessage")));
			for($x = 1; $x < count($children); $x++) {
				$this->addChild($children[$x]);
			}
		}
		$output = "<$this->type ";
		foreach($this->attributes as $key => $value) {
			$value = str_replace("'", "&#39;", $value);
			if (!is_numeric($key)) { $output .= "$key='$value' "; } 
			else { $output .= "$key "; }
		}
		$output .= ">";
		foreach ($this->children as $value) {
			if (is_string($value)) {$output .= $value;}
			else if (is_object($value)) { $output .= $value->output(); }
		}
		$output .= "</$this->type>";
		return $output;		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Script extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("script", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Select extends ValidElement {
	//=====================================================================================
	function __construct($name, $children = null, $attributes = null, $parent = null) {
		$newChildren = $children;
		if (is_array($children)) {
			$newChildren = array();
			foreach ($children as $key => $value) {
				if (is_string($value)) {
					$newChildren[] = new Option($value, array("value" => $key));
				} else {
					$newChildren[] = $value;
				}
			}
		}
		$children = $newChildren;
		
		if (is_array($attributes)) {
			if ( array_key_exists("id", $attributes)) { $id = $attributes["id"]; }
			else { $attributes["id"] = $id = $name;}			
			if ( !array_key_exists("name", $attributes)) { 
				$attributes["name"] = $id; 
			}
		} else {
			$attributes = array(
				"id" => $name,
				"name" => $name
			);
		}
		parent::__construct($name, "select", $children, $attributes, $parent);
	}
	//=====================================================================================
	function output() {
		$output = parent::output();
		$label = true;
		if (array_key_exists("label", $this->attributes) 
			&& $this->attributes["label"] == false) { $label = false; }
		
		if ($label) {
			if (isset($this->attributes["label"])) { $label = $this->attributes["label"]; } 
			else { $label = ucwords(strtolower(str_replace("_" , " ", $this->attributes["id"]))); }
			
			$outputArray = array(
				new Label( $label, array("for" => $this->attributes["id"] ) ),
				" $output"
			);
			
			if (!$this->valid) {
				$outputArray[] = new Div($this->errorMessage, array("class" => "errorMessage"));
			}
			$outputDivAttributes = isset($this->attributes["div"]) ? $this->attributes["div"] : array( "id" => ($this->attributes["id"]."Div") );
			$outputDiv = new Div(
				$outputArray,				
				$outputDivAttributes
			);
			return $outputDiv->output();
		} else { return $output; }
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Span extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("span", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Submit extends ChildlessElement {
	//=====================================================================================
	function __construct($children, $attributes = null, $parent = null) {
		if (is_array($attributes)) { $attributes["type"] = "submit"; } 
		else { $attributes = array("type" => "submit"); }
		$attributes["value"] = $children;
		
		parent::__construct("input", $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Table extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("table", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Tbody extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("tbody", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Td extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("td", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Textarea extends ValidElement {
	//=====================================================================================	
	function __construct($name, $attributes = null, $parent = null) {
		if (is_array($attributes)) {
			if ( array_key_exists("id", $attributes)) { $id = $attributes["id"]; }
			else { $attributes["id"] = $id = $name;}			
			if (is_array($attributes) && !array_key_exists("name", $attributes)) { 
				$attributes["name"] = $id; 
			}
		} else {
			$attributes = array(
				"id" => $name,
				"name" => $name
			);
		}
		parent::__construct($name, "textarea", null, $attributes, $parent);		
	}
	//=====================================================================================
	function output() {
		$output = "<$this->type ";
		foreach($this->attributes as $key => $value) {
			if (
				$key == "div" ||
				$key == "label" || 
				(
					$key == "value" && 
					isset($this->attributes["name"]) && 
					isset($_REQUEST[$this->attributes["name"]])
				)
				) { continue; } 
			else if (!is_numeric($key)) {
				$value = str_replace("'", "&#39;", $value); 
				$output .= "$key='$value' "; 
			} 
			else { $output .= "$key "; }
		}
		
		$output .= ">";
		
		if (isset($this->attributes["name"]) && 
			isset($_REQUEST[$this->attributes["name"]])) { 
				$output .= $_REQUEST[$this->attributes["name"]];				
		}
		
		$output .= "</$this->type>";
		
		
		$label = true;
		if (array_key_exists("label", $this->attributes) 
			&& $this->attributes["label"] == false) { $label = false; }
		
		if ($label) {
			if (isset($this->attributes["label"])) { $label = $this->attributes["label"]; } 
			else { $label = ucwords(strtolower(str_replace("_" , " ", $this->attributes["id"]))); }
			
			$outputArray = array(
				new Label( $label, array("for" => $this->attributes["id"] ) ),
				" $output"
			);
			if (!$this->valid) {
				$outputArray[] = new Div($this->errorMessage, array("class" => "errorMessage"));
			}
			$outputDivAttributes = isset($this->attributes["div"]) ? $this->attributes["div"] : array( "id" => ($this->attributes["id"]."Div") );
			$outputDiv = new Div(
				$outputArray,				
				$outputDivAttributes
			);
			return $outputDiv->output();
		} else { return $output; }
		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Th extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("th", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Thead extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("thead", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Title extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("title", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Tr extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("tr", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
class Ul extends Element {
	//=====================================================================================
	function __construct($children = null, $attributes = null, $parent = null) {
		parent::__construct("ul", $children, $attributes, $parent);		
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
?>