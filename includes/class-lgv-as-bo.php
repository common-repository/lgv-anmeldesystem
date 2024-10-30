<?php

/**
 * Description of class-LGV_AS_BO
 *
 * @authors Jochen Kalmbach
 */
 
require_once LGV_AS_PATH . 'includes/class-lgv-as-bo-payment.php';


class LGV_AS_GlobalOptions {
	public function __construct()
	{
		$this->FromEmail = "please-insert@email-domain.invalid";
		$this->FromName = "XYZ-Anmeldungen";
		$this->EmptyEMailReceiver = "please-insert@email-domain.invalid";
		$string = "";
		$string .= "[p]Herzlich willkommen bei der Anmeldung zu unseren Veranstaltungen.[br]";
		$string .= "Klicken sie auf eine der folgenden Veranstaltungen, um sich dafür zu registrieren.[br]";
		$string .= "Sollte die Veranstaltung bereits ausgebucht sein, bitten wir Sie unsere Anmeldeseite immer wieder zu besuchen.[br]";
		$string .= "In den letzten Tagen vor der Veranstaltung werden durch eingehende Abmeldungen in der Regel Plätze frei.[/p]";
		$this->WelcomeMsg = $string;
		$this->BccEmail = null;
		$this->PaymentInfos = new LGV_AS_PaymentInfos();
		$this->PageName = null;
	}
	
	public $FromEmail;
	public $FromName;
	public $EmptyEMailReceiver;
	public $WelcomeMsg;
	public $BccEmail;
	public $PaymentInfos;
	public $PageName;
	
	public function fill($jsonData)
	{
		if (array_key_exists("FromEmail", $jsonData)) {
			$this->FromEmail = $jsonData["FromEmail"];
		}
		if (array_key_exists("FromName", $jsonData)) {
			$this->FromName = $jsonData["FromName"];
		}
		if (array_key_exists("EmptyEMailReceiver", $jsonData)) {
			$this->EmptyEMailReceiver = $jsonData["EmptyEMailReceiver"];
		}
		if (array_key_exists("WelcomeMsg", $jsonData)) {
			$this->WelcomeMsg = $jsonData["WelcomeMsg"];
		}
		if (array_key_exists("BccEmail", $jsonData)) {
			$this->BccEmail = $jsonData["BccEmail"];
		}
		if (array_key_exists("PaymentInfos", $jsonData)) {
			$this->PaymentInfos->fill($jsonData["PaymentInfos"]);
		}
		if (array_key_exists("PageName", $jsonData)) {
			$this->PageName = $jsonData["PageName"];
		}
	}	
}

class LGV_AS_BO {

	const Event_State_Inactive = 0;
	const Event_State_Active = 1;

	const Registration_State_NotConfirmed = 0;
	const Registration_State_Registered = 1;
	const Registration_State_WaitList = 2;
	const Registration_State_Deleted = 3;
	
	public static function getRegStateName($state) {
		switch($state) {
			case self::Registration_State_NotConfirmed:
				return "Unbestätigt";
			case self::Registration_State_Registered:
				return "Registriert";
			case self::Registration_State_WaitList:
				return "Warteliste";
			case self::Registration_State_Deleted:
				return "Gelöscht";
		}
		return "(unbekannt)";
	}
	
	static function getEditUrl($lgvas_email, $lgvas_passcode) {
		$str = LGV_AS_BO::getPageUrl() . "?lgvas_email=" . $lgvas_email . "&lgvas_passcode=" . $lgvas_passcode;
		return $str;
	}
	
	static function getPageUrl() {
		$opt = LGV_AS_BO::getOptions();
		if (empty($opt->PageName)) {
			return get_home_url();  // we assuse we are the main page, so we always return the main page
		}
		if (empty(get_post())) {  // we are in the backend, so we display the link the admin has provided
			return $opt->PageName;
		}
		return get_page_link();  // return the current page
	}

	static function getEditUrlFromId($lgvas_regid) {
		$str = LGV_AS_BO::getPageUrl() . "?lgvas_regid=" . $lgvas_regid;
		return $str;
	}

	static function getEventUrlFromId($id) {
		$str = LGV_AS_BO::getPageUrl() . "?lgvas_evtid=" . $id;
		return $str;
	}

	static function getEMailFrom() {
		$opt = self::getOptions();
		return $opt->FromName . " <" . $opt->FromEmail . ">";
	}
	static function getEMailBcc() {
		$opt = self::getOptions();
		return $opt->BccEmail;
	}
	
	static function getEMailCc() {
		return self::getEMailFrom();
	}
	
	static function sendCc($moveUp = false, $modified = false, $request = false) {
		return true;
	}
	
	static function getEmptyEMailReceiver() {
		$opt = self::getOptions();
		return $opt->EmptyEMailReceiver;
	}
	
	static $GlobalOptions;
	static function getOptions() {
		if (empty(LGV_AS_BO::$GlobalOptions)) {
			add_option('LGV_AS_GlobalOptions');
			$str = get_option('LGV_AS_GlobalOptions');
			if (!empty($str)) {
				// unserialize...
				$jsonData = json_decode($str, true);
				$opt = new LGV_AS_GlobalOptions();
				$opt->fill($jsonData);
				LGV_AS_BO::$GlobalOptions = $opt;
			}
			else {
				$opt = new LGV_AS_GlobalOptions();
				LGV_AS_BO::setOptions($opt);
			}
		}
		return LGV_AS_BO::$GlobalOptions;
	}
	
	static function setOptions($opt) {
		LGV_AS_BO::$GlobalOptions = $opt;
		$str =  json_encode($opt, JSON_PRETTY_PRINT);
		update_option('LGV_AS_GlobalOptions', $str, true);
	}
}  // end class LGV_AS_BO


require_once LGV_AS_PATH . 'includes/class-lgv-as-bo-event.php';
require_once LGV_AS_PATH . 'includes/class-lgv-as-bo-registration.php';
require_once LGV_AS_PATH . 'includes/class-lgv-as-bo-eventgroup.php';
