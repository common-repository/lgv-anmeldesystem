<?php

/**
 * Description of class-LGV_AS_BO_Event
 *
 * @authors Jochen Kalmbach
 */

// Business Object: Event-Additional-Parameter
class LGV_AS_BO_EventAddParaMain {
	public function __construct()
	{
		$this->Parameters = array();
		$this->MaxPerRegister = 0;
		$this->MaxWaitingList = 110;
		$this->WirKommenText = "Wir kommen mit x Personen";
		$this->SavedText = "Ihre Anmeldung wurde gespeichert. Sie bekommen nun eine Bestätigungsmail an Ihre E-Mail Adresse.";
		$this->RegisteredMsg = "Wir freuen uns auf die Begegnung mit Ihnen und wünschen Ihnen viel Vorfreude, eine bewahrte Anreise und einen sehr schönen Tag in Bad Liebenzell.";
		$this->AllgMsg = "Sie können Ihre Anmeldung jederzeit anpassen, indem sie oben auf den [i]Bearbeiten[/i] Link klicken.[br]" .
			"Bitte melden Sie uns alle bei Ihnen eintretenden Veränderungen bis zum Tag vor der Veranstaltung bzw. melden Sie sich ab, wenn Sie nicht bei unserer " .
			"Veranstaltung dabei sein können. Unser Platzangebot ist begrenzt und wir führen deshalb eine Warteliste. " .
			"Jeder Platz, der durch Änderungsmeldungen oder Abmeldungen frei wird, wird automatisch " .
			"Personen angeboten, die sich auf unserer Warteliste eingetragen haben.";
		$this->GrussMsg = "Gott segne und behüte sie!";
		$this->RegWithNames = false;
		$this->RegWithNamesMsg = "<strong>Weitere Personen, die ich mit dieser Anmeldung anmelde:</strong><br/>";
		$this->RegWithNamesMsg .= "Hinweis: Namen und Anzahl der mitangemeldeten Personen können Sie bis zum Tag vor der Veranstaltung unter Verwendung des Bestätigungslinks";
		$this->RegWithNamesMsg .= " (siehe Erklärung weiter unten) anpassen.<br/>";
		$this->RegWithNamesMsg .= "<strong>Für jede mitangemeldete Person bitte auf 'Person hinzufügen' klicken und jeweils 1 separates Feld ausfüllen!</strong>";
		$this->OrderNo = 0;
		$this->HeaderText = "";
		$this->FooterText = "Nach dem Absenden Ihrer Anmeldung erhalten Sie per E-Mail eine Bestätigung mit Ihren bei uns erfassten Anmeldedaten. ";
		$this->FooterText .= "In der E-Mail ist ein Link vorhanden, der es Ihnen ermöglicht, die gemachten Angaben bis zum Tag vor der Veranstaltung zu ändern. ";
		$this->FooterText .= "Bitte drucken Sie sich die Anmeldebestätigung aus, speichern Sie die E-Mail-Nachricht mit dem Link auf Ihrem Computer und melden ";
		$this->FooterText .= "Sie uns die Veränderungen. Sie helfen dadurch entscheidend mit, dass möglichst viele Teilnehmer bei unseren Veranstaltungen dabei sein können.";
		$this->OnlyLoggedInInOverview = false;
		$this->UrlId = "";
		$this->OptZip = 0;
		$this->OptStreet = 0;
		$this->OptCity = 0;
		$this->OptPhone = 0;
		$this->EditMode = 0;
		$this->AutoDeactivateOn = null;
		$this->GroupKey = null;
		$this->FirstNameHint = null;
		$this->FirstNameRegEx = null;
		$this->FirstNameRegExMsg = null;
		$this->LastNameHint = null;
		$this->LastNameRegEx = null;
		$this->LastNameRegExMsg = null;
		$this->PersonHeader = null;
		$this->MultiEmail = null;  // Allow multiple registrations with the same email (set to 1)
	}
	
	public $Parameters;
	
	public $MaxPerRegister;
	public $WirKommenText;
	public $SavedText;
	public $MaxWaitingList;
	public $RegisteredMsg;
	public $AllgMsg;
	public $GrussMsg;
	
	public $OrderNo;
	public $HeaderText;
	public $FooterText;
	
	// Gibt an, ob die Registrierungen über Namen (!= 0) oder Anzahl (0) erfolgen soll
	// Gibt nur, wenn "MaxPerRegister" > 1 ist, da nur dann mehrere angemeldet werden können
	public $RegWithNames;
	public $RegWithNamesMsg;
	
	// Gibt an, ob diese Veranstaltung in der Übersicht nur angezeigt werden soll, wenn ein Benutzer eingeloggt ist
	public $OnlyLoggedInInOverview;
	
	// Definiert optional einen eindeutigen Namen, welcher im parameter "https://test.lgv-neubulach.de/?lgvas_evtid=id" angegeben werden kann
	public $UrlId;
	
	// Wenn != 0, so ist die PLZ optional
	public $OptZip;
	// Wenn != 0, so ist die Strasse optional
	public $OptStreet;
	// Wenn != 0, so ist die Stadt optional
	public $OptCity;
	// Wenn != 0, so ist die Telefonnummer optional
	public $OptPhone;
	
	// 0: Benutzer kann seine Daten ändern und auch löschen; -1: Benutzer darf weder ändern noch löschen!
	public $EditMode;
	
	// Specifies a date/time when the event should be automatically deactivated
	// Format: new DateTime('2011-01-01T15:03:01');
	public $AutoDeactivateOn;
	
	public $GroupKey;
	
	public $FirstNameHint;
	public $FirstNameRegEx;
	public $FirstNameRegExMsg;
	public $LastNameHint;
	public $LastNameRegEx;
	public $LastNameRegExMsg;
	
	// If this is set, then we make a "Header" abouve the person parameters
	public $PersonHeader;
	
	public function fill($jsonData)
	{
		$pArr = $jsonData["Parameters"];
		if (is_array($pArr)) 
		{
			foreach($pArr as $value)
			{
				$pE = new LGV_AS_BO_EventAddPara();
				$pE->fill($value);
				array_push($this->Parameters, $pE);
			}
		}
		$this->MaxPerRegister = intval($jsonData["MaxPerRegister"]);
		if (array_key_exists("MaxWaitingList", $jsonData)) {
			$this->MaxWaitingList = intval($jsonData["MaxWaitingList"]);
		}
		if (array_key_exists("WirKommenText", $jsonData)) {
			$this->WirKommenText = $jsonData["WirKommenText"];
		}
		if (array_key_exists("SavedText", $jsonData)) {
			$this->SavedText = $jsonData["SavedText"];
		}
		if (array_key_exists("RegisteredMsg", $jsonData)) {
			$this->RegisteredMsg = $jsonData["RegisteredMsg"];
		}
		if (array_key_exists("AllgMsg", $jsonData)) {
			$this->AllgMsg = $jsonData["AllgMsg"];
		}
		if (array_key_exists("GrussMsg", $jsonData)) {
			$this->GrussMsg = $jsonData["GrussMsg"];
		}
		if ($this->MaxPerRegister > 0) {
			if (array_key_exists("RegWithNames", $jsonData)) {
				$this->RegWithNames = intval($jsonData["RegWithNames"]) != 0;
			}
		} else {
			$this->RegWithNames = false;
		}
		if (array_key_exists("RegWithNamesMsg", $jsonData)) {
			$data = $jsonData["RegWithNamesMsg"];
			if (!empty($data)) {
				$this->RegWithNamesMsg = $data;
			}
		}
		
		if (array_key_exists("HeaderText", $jsonData)) {
			$this->HeaderText = $jsonData["HeaderText"];
		}
		if (array_key_exists("FooterText", $jsonData)) {
			$data = $jsonData["FooterText"];
			if (!empty($data)) {
				$this->FooterText = $data;
			}
		}
		if (array_key_exists("OrderNo", $jsonData)) {
			$this->OrderNo = intval($jsonData["OrderNo"]);
		}
		if (array_key_exists("OnlyLoggedInInOverview", $jsonData)) {
			$iVal = intval($jsonData["OnlyLoggedInInOverview"]);
			if ($iVal) {
				$this->OnlyLoggedInInOverview = true;
			} else {
				$this->OnlyLoggedInInOverview = false;
			}
		}
		if (array_key_exists("UrlId", $jsonData)) {
			$this->UrlId = $jsonData["UrlId"];
		}

		if (array_key_exists("OptZip", $jsonData)) {
			$this->OptZip = $jsonData["OptZip"];
		}
		if (array_key_exists("OptStreet", $jsonData)) {
			$this->OptStreet = $jsonData["OptStreet"];
		}
		if (array_key_exists("OptCity", $jsonData)) {
			$this->OptCity = $jsonData["OptCity"];
		}
		if (array_key_exists("OptPhone", $jsonData)) {
			$this->OptPhone = $jsonData["OptPhone"];
		}

		if (array_key_exists("EditMode", $jsonData)) {
			$this->EditMode = $jsonData["EditMode"];
		}
		if (array_key_exists("AutoDeactivateOn", $jsonData)) {
			$this->AutoDeactivateOn = $jsonData["AutoDeactivateOn"];
			if(!empty($this->AutoDeactivateOn))
			{
				$tz = get_option('timezone_string');
				if (empty($tz)) {
					$tz = get_option('gmt_offset');
				}
				// Check if this is a datetime;
				try {
				$dt = new DateTime($this->AutoDeactivateOn, new DateTimeZone($tz));
				} catch (Exception $e) {
					throw new Exception("Invalid date/time in 'AutoDeactivateOn': " . $e->getMessage());
				}
			}
		}
		if (array_key_exists("GroupKey", $jsonData)) {
			$this->GroupKey = $jsonData["GroupKey"];
		}
		if (array_key_exists("FirstNameHint", $jsonData)) {
			$this->FirstNameHint = $jsonData["FirstNameHint"];
		}
		if (array_key_exists("FirstNameRegEx", $jsonData)) {
			$this->FirstNameRegEx = $jsonData["FirstNameRegEx"];
		}
		if (array_key_exists("FirstNameRegExMsg", $jsonData)) {
			$this->FirstNameRegExMsg = $jsonData["FirstNameRegExMsg"];
		}
		if (array_key_exists("LastNameHint", $jsonData)) {
			$this->LastNameHint = $jsonData["LastNameHint"];
		}
		if (array_key_exists("LastNameRegEx", $jsonData)) {
			$this->LastNameRegEx = $jsonData["LastNameRegEx"];
		}
		if (array_key_exists("LastNameRegExMsg", $jsonData)) {
			$this->LastNameRegExMsg = $jsonData["LastNameRegExMsg"];
		}
		if (array_key_exists("PersonHeader", $jsonData)) {
			$this->PersonHeader = $jsonData["PersonHeader"];
		}
		if (array_key_exists("MultiEmail", $jsonData)) {
			$this->MultiEmail = $jsonData["MultiEmail"];
		}
	}
}

class LGV_AS_BO_EventAddPara {
	public $Id;
	
	// Header (also nur Text, der bei der Eingabe angezeigt wird)
	// MinToMaxRegistered (also von Min bis maximalen Anzahl der angemeldeten)
	// MinToMax (also von Min bis Max)
	// Text (Freitext)
	// Bool (also CheckBox) / Min/Max wird dabei ignoriert, nur "Def" wird ausgewertet
	public $Typ;

	// Titel für die Anzeige
	public $Title;
	
	// Optional: Will only be displayed in the form below the titel
	public $Hint;
	
	public $Min;
	public $Max;
	public $Def;
	public $Sel;

	// Defines if this value is a required field
	// Currently only valid for "Text"
	public $Req;
	public $ReqText;
	
	// Gibt an, ob dieser AddPara definiert ob der Benutzer angemeldet ist!
	// Dies wird nur ausgewertet, wenn man sich nicht über eine Anzahl anmelden kann (MaxPerRegister)
	public $IsRegInfo;
	
	public $NotInMail;

	public function fill($jsonData)
	{
		$this->Id = $jsonData["Id"];
		$this->Typ = $jsonData["Typ"];
		$this->Title = $jsonData["Title"];
		$this->Min = 0;
		$this->Req = null;
		$this->ReqText = null;
		if (isset($jsonData["Min"])) {
			$this->Min = intval($jsonData["Min"]);
		}
		$this->Max = 0;
		if (isset($jsonData["Max"])) {
			$this->Max = intval($jsonData["Max"]);
		}
		if (isset($jsonData["Def"])) {
			$this->Def = $jsonData["Def"];
		}
		if (isset($jsonData["Sel"])) {
			$this->Sel = $jsonData["Sel"];
		}
		if (isset($jsonData["IsRegInfo"])) {
			$this->IsRegInfo = intval($jsonData["IsRegInfo"]);
		}
		if (isset($jsonData["NotInMail"])) {
			$this->NotInMail = intval($jsonData["NotInMail"]);
		}
		if (isset($jsonData["Req"])) {
			$this->Req = intval($jsonData["Req"]);
		}
		if (isset($jsonData["ReqText"])) {
			$this->ReqText = $jsonData["ReqText"];
		}
		if (isset($jsonData["Hint"])) {
			$this->Hint = $jsonData["Hint"];
		}
	}
	public function getIdName()
	{
		return "lgvas_para_" . $this->Id;
	}

	public function getParaName()
	{
		return "para_" . $this->Id;
	}
	
	public function fillFromPostData(&$regParaArray)
	{
		//print_r($regParaArray);
		$key = $this->getIdName();
		if (!isset($_POST[$key])) {
			return $this->Def;
		}
		$val = intval($_POST[$key]);
		
		switch($this->Typ)
		{
		case "MinToMax":
		case "MinToMaxText":
			$val = max($val, $this->Min);
			$val = min($val, $this->Max);
			$regParaArray[$this->getParaName()] = $val;
			break;
		case "MinToMaxRegistered":
			$val = max($val, $this->Min);
			$regParaArray[$this->getParaName()] = $val;
			break;
		case "Bool":
			if ($val != 0) { $val = 1; } else { $val = 0; }
			$regParaArray[$this->getParaName()] = $val;
			break;
		case "Text":
			$regParaArray[$this->getParaName()] = sanitize_text_field($_POST[$this->getIdName()]);
			break;
		case "Date":
			$val = sanitize_text_field($_POST[$this->getIdName()]);
			$myDate = DateTime::createFromFormat("d.m.Y", $val);
			if (is_object($myDate) == false) {
				$regParaArray[$this->getParaName()] = "";
			} else {
				$yearVal = $myDate->format("Y");
				if (intval($yearVal) < 100) {
					// Convert 2 digit into 4 digits
					$twoDigitYearMax = intval(date("Y")) - (intval(intval(date("Y"))/100)*100);
					if ($yearVal <= $twoDigitYearMax) {
						$myDate->add(new DateInterval("P2000Y"));
					} else {
						$myDate->add(new DateInterval("P1900Y"));
					}
				}
				$regParaArray[$this->getParaName()] = $myDate->format("d.m.Y");
			}
			break;
		case "Sel":
			$regParaArray[$this->getParaName()] = sanitize_text_field($_POST[$this->getIdName()]);
			break;
		case "Payment":
			$opt = LGV_AS_BO::getOptions();
			$opt->PaymentInfos->fillFromPostData($this, $regParaArray);
			break;
		}
	}
	
	public function validate($regParaArray, &$res, $registrations) {
		switch($this->Typ)
		{
			case "Sel":
				if (!LGV_AS_Util::get_sel_hasValidDefault($this->Sel, $this->Def)) {
					$parVal = $regParaArray[$this->getParaName()];
					
					if ($parVal == $this->Def) {
						if (LGV_AS_Util::isEditorOrAdmin() == false) {
							$res[$this->getIdName()] = "Bitte wählen Sie einen Eintrag aus";
						}
					}
				}
				break;
			case "MinToMaxRegistered":
				$val = intval($regParaArray[$this->getParaName()]);
				if ($val > $registrations) {
						$res[$this->getIdName()] = "Bitte geben Sie nur max. so viele Personen an, wie sie anmelden";
				}
			break;
			case "Date":
				$val = $regParaArray[$this->getParaName()];
				$myDate = DateTime::createFromFormat("d.m.Y", $val);
				$actYear = intval(date('Y'));
				if (is_object($myDate) == false) {
					if (LGV_AS_Util::isEditorOrAdmin() == false) {
						$res[$this->getIdName()] = "Bitte geben Sie ein g&uuml;ltiges Datum ein";
					}
				} else {
					$year = intval($myDate->format('Y'));
					if ( ($year < ($actYear-100)) || ($year > $actYear) ) {
						$res[$this->getIdName()] = "Bitte geben Sie ein g&uuml;ltiges Jahr ein";
					}
				}
			break;
			case "Bool":
				if (intval($this->Req) > 0) {
					if (empty($regParaArray[$this->getParaName()])) {
						if (empty($this->ReqText)) {
							$res[$this->getIdName()] = "Sie müssen hier zustimmen";
						} else {
							$res[$this->getIdName()] = esc_html($this->ReqText);
						}
					}
				}
				break;
			case "Text":
				if (intval($this->Req) > 0) {
					if (empty($regParaArray[$this->getParaName()])) {
						if (empty($this->ReqText)) {
							$res[$this->getIdName()] = "Bitte geben Sie ein g&uuml;ltiges Eintrag ein";
						} else {
							$res[$this->getIdName()] = esc_html($this->ReqText);
						}
					}
				}
			break;
		case "Payment":
			$opt = LGV_AS_BO::getOptions();
			$opt->PaymentInfos->validate($this, $regParaArray, $res, $registrations);
			break;
		}
	}
	
	public function getDef($regParaArray)
	{
		//print_r($regParaArray);
		if (empty($regParaArray)) {
			return $this->Def;
		}
		
		$key = $this->getParaName();
		if (isset($regParaArray[$key])) {
			$val = intval($regParaArray[$key]);
		}
		else {
			return $this->Def;
		}
		switch($this->Typ)
		{
		case "MinToMax":
		case "MinToMaxText":
			$val = max($val, $this->Min);
			$val = min($val, $this->Max);
			return $val;
		case "MinToMaxRegistered":
			$val = max($val, $this->Min);
			return $val;
		case "Bool":
			if ($val != 0) { return 1; } else { return 0; }
		case "Text":
			return $regParaArray[$this->getParaName()];
		case "Sel":
			return $regParaArray[$this->getParaName()];
		case "Date":
			return $regParaArray[$this->getParaName()];
		}
		return $this->Def;
	}
}
 
// Business Object: Event
class LGV_AS_BO_Event {
	public function __construct()
	{
		$this->maxRegistrations = 100;
		$this->AddParaMain = new LGV_AS_BO_EventAddParaMain();
		$this->AddParaMain_Error = false;
	}
	public function copy() {
		$newObj = unserialize(serialize($this));
		$newObj->id = null;
		$newObj->state = LGV_AS_BO::Event_State_Inactive;
		return $newObj;
	}
	
	protected $id;
	public function getId() { return $this->id; }
	protected function setId($id) { $this->id = $id; }

	protected $createdDateTime;
	public function getCreatedDateTime() { return $this->createdDateTime; }
	public function setCreatedDateTime($createdDateTime) { $this->createdDateTime = $createdDateTime; }
  
	protected $title;
	public function getTitle() { return $this->title; }
	public function setTitle($title) { $this->title = $title; }
  
	//protected $description;
	//public function getDescription() { return $this->description; }
	//public function setDescription($description) { $this->description = $description; }
  
	protected $state;
	public function getState() { return $this->state; }
	public function setState($state) { $this->state = $state; }
	
	protected $maxRegistrations;
	public function getMaxRegistrations() { return $this->maxRegistrations; }
	public function setMaxRegistrations($maxRegistrations) { $this->maxRegistrations = $maxRegistrations; }	
	
	protected $notConfirmedCnt;
	public function getNotConfirmedCnt() { return $this->notConfirmedCnt; }
	
	protected $registeredCnt;
	public function getRegisteredCnt() { return $this->registeredCnt; }
	
	protected $waitingListCnt;
	public function getWaitingListCnt() { return $this->waitingListCnt; }
	
	protected $deletedCnt;
	public function getDeletedCnt() { return $this->deletedCnt; }
	
	protected $additionalParameters;
	public function getAdditionalParameters() 
	{ 
		//$a = new LGV_AS_BO_EventAddPara();
		//$a->Id = "id1";
		//$a->Title = "Titel";
		//$a->Typ = "Text";
		//array_push($this->AddParaMain->Parameters, $a);
		
		$str =  json_encode($this->AddParaMain, JSON_PRETTY_PRINT);
		
		//echo "<hr/>";
		//print_r($this->AddParaMain);
		//echo "<br/>";
		//print_r($str);
		//echo "<hr/>";
		return $str;
	}
	public function setAdditionalParameters($additionalParameters) 
	{ 
		//print_r($additionalParameters);
	
		$this->AddParaMain_Error = 0;
		$this->additionalParameters = $additionalParameters;
		$jsonData = json_decode($additionalParameters, true);
		if (json_last_error() != JSON_ERROR_NONE) {
			$this->AddParaMain_Error = json_last_error_msg();
		}
		
		//print_r($jsonData);
		
		$this->AddParaMain = new LGV_AS_BO_EventAddParaMain();
		$this->AddParaMain->fill($jsonData);
		
		
		//echo "<hr/>";
		//print_r($additionalParameters);
		//echo "<hr/>";
		//print_r($this->AddParaMain);
		//echo "<hr/>";
	}
	
	public $AddParaMain;
	private $AddParaMain_Error;
	

	public function fillFromPostData() {
		$this->setTitle(sanitize_text_field($_POST['lgvas_title']));
		//$this->setDescription($_POST['lgvas_description']);
		$this->setMaxRegistrations(sanitize_text_field($_POST['lgvas_maxRegistrations']));
		if (!empty($_POST['lgvas_state'])) {
			$state = intval($_POST['lgvas_state']);
		} else {
			$state = LGV_AS_BO::Event_State_Inactive;
		}
		$this->setState($state);
		$this->setAdditionalParameters(stripcslashes(sanitize_textarea_field($_POST['lgvas_additionalParameters'])));
	}
	
	
	public function validate() {
		$res = array();
		if (empty($this->title)) {
			$res["title"] = "Sie müssen einen gültigen Titel eingeben!";
		}
		//if (empty($this->description)) {
		//	$res["description"] = "Sie müssen einen gültigen Beschreibung eingeben!";
		//}
		if (isset($this->maxRegistrations)) {
			//print_r($this->maxRegistrations);
			if ( ($this->maxRegistrations != "") && (intval($this->maxRegistrations) < 0) ) {
				$res["maxRegistrations"] = "Sie müssen eine gültige Anzahl an maximalen Registrierungen eingeben!";
			}
		}
		
		// TODO: Validate "additionalParameters" for JSON conformance!
		if ($this->AddParaMain_Error) {
			$res["additionalParameters"] = "JSON-Data ungültig!" . $this->AddParaMain_Error;
		}
		
		return $res;
	}
	
}
