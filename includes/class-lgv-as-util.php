<?php

/**
 * Description of class-LGV_AS_Backend
 *
 * @authors Jochen Kalmbach
 */

 
class LGV_AS_Util {

	static function setup() {
	}
	
	static function _get_options($min = 0, $max = 20, $sel = 0) {
		$string = "";
		for($i=$min; $i<=$max; $i++) {
			$selected = "";
			if ($i == $sel) {
				$selected = " selected";
			}
			$string .= "\r\n<option value='" . $i . "'" . $selected .">" . $i . "</option>";
		}
		return $string;
	}	

	static function get_sel($selections, $def = null, $realDef = null) {
		$string = "";
		
		// Check if the selection contains the default value:
		if (!self::get_sel_hasValidDefault($selections, $realDef)) {
			$string .= "\r\n<option value='" . $realDef . "'";
			if ($def == $realDef) {
				$string .= " selected";
			}
			$string .= ">" . $realDef . "(bitte auswählen)</option>";
		}
		
		foreach($selections as $value) {
			$selected = "";
			if ($value == $def) {
				$selected = " selected";
			}
			$string .= "\r\n<option value='" . $value . "'" . $selected .">" . $value . "</option>";
		}
		return $string;
	}
	
	// Ermittelt, ob die übergebene Auswahl den Default-Wert enthält
	// Wird bei den Selections verwendet um einen Default-Wert zu setzen, oder die Auswahl auf "(bitte auswählen)" zu setzen
	static function get_sel_hasValidDefault($selections, $def) {
		foreach($selections as $value) {
			if ($value == $def) {
				return true;
			}
		}
		return false;
	}

	static function get_value_as_text($min = 0, $max = 50, $def = "") {
		if (empty($def)) {
			return "";
		}
		$val = intval($def);
		$val = min($def, $max);
		$val = max($def, $min);
		return $val;
	}
	
	
	static function get_checked($value) {
		if (intval($value) != 0) {
			return "checked";
		}
		return "";
	}
	
	static function get_validation_error($validationResult, $key) {
		if (!isset($validationResult[$key])) {
			return "";
		}
		$err = $validationResult[$key];
		if (empty($err)) {
			return "";
		}
		//print_r($validationResult);
		return "<div id='" . $key . "_error' class='lgvas_validation_error'>" . esc_html($err) . "</div>";
	}
	
	static function isAdmin() {
		return current_user_can("manage_options");
	}
	
	static function isEditorOrAdmin() {
		return current_user_can("edit_pages");
	}
	
	// Original from: http://www.laughing-buddha.net/php/password
	static function get_random_passcode($length = 16) {
		// start with a blank password
		$password = "";

		// define possible characters - any character in this string can be
		// picked for use in the password, so if you want to put vowels back in
		// or add special characters such as exclamation marks, this is where
		// you should do it
		$possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";

		// we refer to the length of $possible a few times, so let's grab it now
		$maxlength = strlen($possible);
  
		// check for length overflow and truncate if necessary
		if ($length > $maxlength) {
		$length = $maxlength;
		}
	
		// set up a counter for how many characters are in the password so far
		$i = 0; 
    
		// add random characters to $password until $length is reached
		while ($i < $length) { 

			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, $maxlength-1), 1);
        
			// have we already used this character in $password?
			if (!strstr($password, $char)) { 
			// no, so it's OK to add it onto the end of whatever we've already got...
			$password .= $char;
			// ... and increase the counter by one
			$i++;
			}
		}
		// done!
		return $password;
	}
	
	static function getText($text) {
		$text = str_replace("[br]", "<br/>", $text);
		$text = str_replace("[p]", "<p>", $text);
		$text = str_replace("[/p]", "</p>", $text);
		$text = str_replace("[i]", "<i>", $text);
		$text = str_replace("[/i]", "</i>", $text);
		$text = str_replace("[u]", "<u>", $text);
		$text = str_replace("[/u]", "</u>", $text);
		$text = str_replace("[ol]", "<ol>", $text);
		$text = str_replace("[/ol]", "</ol>", $text);
		$text = str_replace("[ul]", "<ul>", $text);
		$text = str_replace("[/ul]", "</ul>", $text);
		$text = str_replace("[li]", "<li>", $text);
		$text = str_replace("[/li]", "</li>", $text);
		$text = str_replace("[b]", "<strong>", $text);
		$text = str_replace("[/b]", "</strong>", $text);
		$text = str_replace("[hr]", "<hr/>", $text);
		$text = str_replace("[main]", "<a href=''>Anmeldeseite</a>", $text);
		$text = str_replace("[lgv]", "<a href='http://www.lgv.org/'>www.lgv.org</a>", $text);
		$text = str_replace("[monbachtal]", "<a href='http://www.monbachtal.de'>http://www.monbachtal.de</a>", $text);
		$text = str_replace("[monbachtal-email]", "<a href='mailto:info@monbachtal.de'>info(at)monbachtal.de</a>", $text);
		$text = str_replace("[isbb-teilnahmebedingungen]", "<a href='https://isbb.lgv.org/seelsorge-seminare-ausbildung/anmeldung-seelsorge-seminare'>https://isbb.lgv.org/seelsorge-seminare-ausbildung/anmeldung-seelsorge-seminare</a>", $text);
		$text = str_replace("[isbb-email]", "<a href='mailto:info-isbb@lgv.org'>info-isbb(at)lgv.org</a>", $text);
		$text = str_replace("[lgv-email]", "<a href='mailto:info@lgv.org'>info(at)lgv.org</a>", $text);
		$text = preg_replace('/\[(.*?)\]\{(.*?)\}/', "<a href='$2' target='_blank'>$1</a>", $text);
		$text = preg_replace('/\[color:(#?\w+)\]/i', "<span style='color:$1'>", $text);
		$text = str_replace("[/color]", "</span>", $text);
		return $text;
	}
	
	
	// Siehe Kommetare von http://php.net/manual/en/function.fputcsv.php
	static function get_csv_encoded($fields = array(), $delimiter = ';', $enclosure = '"') {
    $str = '';
    $escape_char = '\\';
    foreach ($fields as $value) {
      if (strpos($value, $delimiter) !== false ||
          strpos($value, $enclosure) !== false ||
          strpos($value, "\n") !== false ||
          strpos($value, "\r") !== false ||
          strpos($value, "\t") !== false ||
          strpos($value, ' ') !== false) {
        $str2 = $enclosure;
        $escaped = 0;
        $len = strlen($value);
        for ($i=0;$i<$len;$i++) {
          if ($value[$i] == $escape_char) {
            $escaped = 1;
          } else if (!$escaped && $value[$i] == $enclosure) {
            $str2 .= $enclosure;
          } else {
            $escaped = 0;
          }
          $str2 .= $value[$i];
        }
        $str2 .= $enclosure;
        $str .= $str2.$delimiter;
      } else {
        $str .= $value.$delimiter;
      }
    }
    $str = substr($str,0,-1);
    $str .= "\n";
    return $str;
  }

	
}  // end class LGV_AS_Util


// PHP >= 5.5.0 

if (!function_exists('json_last_error_msg')) {
    function json_last_error_msg() {
        static $errors = array(
            JSON_ERROR_NONE             => null,
            JSON_ERROR_DEPTH            => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH   => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR        => 'Unexpected control character found',
            JSON_ERROR_SYNTAX           => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );
        $error = json_last_error();
        return array_key_exists($error, $errors) ? $errors[$error] : "Unknown error ({$error})";
    }
}