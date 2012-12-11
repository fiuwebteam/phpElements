<?php
//-----------------------------------------------------------------------------------------
class Validation {
	//=====================================================================================
	public $validations = array();
	public $errors = array();
	//=====================================================================================
	function __construct(array $validations) {
		$this->validations = $validations;
		$GLOBALS["validation"] = $this;			
	}
	//=====================================================================================
	function validate() {
		if (empty($_POST) && empty($_GET) ) {
			return false;
		}
		foreach ($this->validations as $element => $rules) {
			foreach ($rules as $rule => $params) {
				$call = $rule;
				$ruleParams = isset($_REQUEST[$element]) ? $_REQUEST[$element] : null;
				if (is_string($params) && is_numeric($rule)) { $call = $params;}			
				else if (isset($params["rule"])) {
					if (is_array($params["rule"])) {
						$call = $params["rule"][0];
						$ruleParams = array($ruleParams, $params["rule"][1]);
					} else { $call = $params["rule"]; }
				}
				$valid = call_user_func(array($this, $call), $ruleParams);
				if (!$valid) {
					$tmp = array( $element => array( "valid" => $valid ) );
					if (is_array($params) 
						&& array_key_exists("message", $params)) { $tmp[$element]["message"] = $params["message"];}
					$this->errors[] = $tmp;
					break;
				}
			}
		}
		if (empty($this->errors)) {return true; }
		else { return false; }
	}
	//=====================================================================================
	function isDate($element) {
		if (!$this->notEmpty($element)) return true; 
		if ($element["year"] == "N/A" || $element["month"] == "N/A" || $element["day"] == "N/A") return false;
		return true; 
	}
	//=====================================================================================
	function isMoney($element) {
		if (!$this->notEmpty($element)) return true;
		return preg_match('/^[0-9]*\.?[0-9]+$/',$element);
	}
	//=====================================================================================
	function isPhone($element) {
		if (!$this->notEmpty($element)) return true;
		return preg_match("/^[\(]?(\d{0,3})[\)]?[\s]?[\-]?[\.]?(\d{3})[\s]?[\-]?[\.]?(\d{4})[\s]?[x]?(\d*)$/", $element);
	}
	//=====================================================================================
	function isNumeric($element) {
		if (!$this->notEmpty($element)) return true;
		return is_numeric($element);
	}
	//=====================================================================================
	function isAlphaNumeric($element) {
		if (!$this->notEmpty($element)) return true;
		return ctype_alnum($element);
	}
	//=====================================================================================
	function notEmpty($element) {
		if ($element == "" || $element == null || empty($element) ) { return false; }
		else { return true; }
	}
	//=====================================================================================
	function isEmail($element) {
		if (!$this->notEmpty($element)) return true;
		return preg_match("/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i", $element);
	}
	//=====================================================================================
	function isUrl($element) {
		if (!$this->notEmpty($element)) return true;
		return preg_match("/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i", $element);
	}
	//=====================================================================================
	function isLength(array $elements) {
		if (!$this->notEmpty($elements)) return true;
		if (strlen($elements[0]) == $elements[1]) { return true; } 
		else { return false; }
	}
	//=====================================================================================
	function minLength(array $elements) {
		if (strlen($elements[0]) >= $elements[1]) { return true; } 
		else { return false; }
	}
	//=====================================================================================
	function maxLength(array $elements) {
		if (!$this->notEmpty($element)) return true;
		if (strlen($elements[0]) <= $elements[1]) { return true; } 
		else { return false; }
	}
	//=====================================================================================
}
//-----------------------------------------------------------------------------------------
?>