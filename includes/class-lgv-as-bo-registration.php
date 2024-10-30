<?php

/**
 * Description of class-LGV_AS_BO_Registration
 *
 * @authors Jochen Kalmbach
 */

abstract class LGV_AS_BO_Registration {
	public function __construct()
	{
		$this->parameters = array();
	}
	
	protected $id;
	public function getId() { return $this->id; }
	protected function setId($id) { $this->id = $id; }

	protected $eventId;
	public function getEventId() { return $this->eventId; }
	public function setEventId($eventId) { $this->eventId = $eventId; }
	
	protected $createdDateTime;
	public function getCreatedDateTime() { return $this->createdDateTime; }
	public function setCreatedDateTime($createdDateTime) { $this->createdDateTime = $createdDateTime; }
  
	protected $email;
	public function getEmail() { return $this->email; }
	public function setEmail($email) { $this->email = $email; }
	
	protected $emailConfirm;
	public function getEmailConfirm() { return $this->emailConfirm; }
	public function setEmailConfirm($emailConfirm) { $this->emailConfirm = $emailConfirm; }

	protected $modifyPassword;
	public function getModifyPassword() { return $this->modifyPassword; }
	public function setModifyPassword($modifyPassword) { $this->modifyPassword = $modifyPassword; }
	
	protected $firstName;
	public function getFirstName() { return $this->firstName; }
	public function setFirstName($firstName) { $this->firstName = $firstName; }
	
	protected $lastName;
	public function getLastName() { return $this->lastName; }
	public function setLastName($lastName) { $this->lastName = $lastName; }
	  
	protected $state;
	public function getState() { return $this->state; }
	public function setState($state) { $this->state = $state; }
	
	protected $street;
	public function getStreet() { return $this->street; }
	public function setStreet($street) { $this->street = $street; }
	
	protected $zipCode;
	public function getZipCode() { return $this->zipCode; }
	public function setZipCode($zipCode) { $this->zipCode = $zipCode; }
	
	protected $city;
	public function getCity() { return $this->city; }
	public function setCity($city) { $this->city = $city; }
	
	protected $phone;
	public function getPhone() { return $this->phone; }
	public function setPhone($phone) { $this->phone = $phone; }
	
	protected $registrations;
	public function getRegistrations() { return $this->registrations; }
	public function setRegistrations($registrations) { $this->registrations = $registrations; }
	
	protected $waitingListDateTime;
	public function getWaitingListDateTime() { return $this->waitingListDateTime; }
	public function setWaitingListDateTime($waitingListDateTime) { $this->waitingListDateTime = $waitingListDateTime; }
	
	protected $parameters;
	public function getParameters() { return $this->parameters; }
	public function setParameters($parameters) 
	{ 
		//print_r($parameters);
		$this->parameters = array();
		if (is_array($parameters)) 
		{
			$arrKeys = array_keys($parameters);
			foreach($arrKeys as $key)
			{
				$this->parameters[$key] = $parameters[$key];
			}
		}
		//print_r($this->parameters);
	}
	
	public function setPara($key, $value) {
		if (empty($this->parameters)) {
			$this->parameters = array();
		}
		$this->parameters[$key] = $value;
	}
	public function getPara($key) {
		if (empty($this->parameters)) {
			return "";
		}
		if (isset($this->parameters[$key])) {
			return $this->parameters[$key];
		}
		return "";
	}

	// -----------------------------------------------------
	// Infos aus dem Event
	protected $EventTitle;
	
	// -----------------------------------------------------

	public function fillFromPostData($evt) {
		$this->setEventId($evt->getId());
		
		$this->setFirstName(trim($_POST['lgvas_firstName']));
		$this->setLastName(trim($_POST['lgvas_lastName']));
		if ($evt->AddParaMain->OptStreet >= 0) {
			$this->setStreet(trim($_POST['lgvas_street']));
		}
		if ($evt->AddParaMain->OptZip >= 0) {
			$this->setZipCode(trim($_POST['lgvas_zipCode']));
		}
		if ($evt->AddParaMain->OptCity >= 0) {
			$this->setCity(trim($_POST['lgvas_city']));
		}
		if ($evt->AddParaMain->OptPhone == 0) {
			$this->setPhone(trim($_POST['lgvas_phone']));
		}
		if (empty($this->id) || LGV_AS_Util::isEditorOrAdmin()) {
			// Übernehme die E-Mail nur, wenn sie nicht von der Datenbank kommt; also der Datensatz noch nicht angelegt wurde!
			$this->setEmail(trim($_POST['lgvas_email']));
			$this->setEmailConfirm(trim($_POST['lgvas_emailConfirm']));
		}

		if ($evt->AddParaMain->MaxPerRegister > 1) {
			if ($evt->AddParaMain->RegWithNames == false) {
				$this->setRegistrations(intval($_POST['lgvas_para_anzahl']));
			} else {
				// Uebernehme alle Werte für die Anz-Namen
				$regNames = array();
				$regCnt = 0;
				for($i = 1; $i < $evt->AddParaMain->MaxPerRegister; $i++) {
					// Schaue jetzt noch nach, ob dieser Eintrag sichtbar war... falls ja, dann muss da aber etwas drinstehen...
					if (isset($_POST["lgvas_para_anzahl_visible" . $i])) {
						if (intval($_POST["lgvas_para_anzahl_visible" . $i]) > 0) {
							$regCnt++;
							$nam = trim($_POST["lgvas_para_anzahl" . $i]);
							if (!empty($nam)) {
								array_push($regNames, $nam);
							}
						}
					}
				}
				// übernehme alle Werte; und auch die Anzahl
				// Wenn Namen eingeben wurden, dann nimm auf jeden Fall immer diese Zahl; auch wenn die Zahl direkt eingegeben wurde!
				if (LGV_AS_Util::isEditorOrAdmin()) {
					if (count($regNames) == 0) {
						// Wenn ich Editor bin, kann ich die Anzahl auch direkt eingeben... das wird aber nur genommen, wenn ich KEINE Namen eingebe
						if (isset($_POST['lgvas_para_anzahl'])) {
							$regCnt = intval($_POST['lgvas_para_anzahl']) - 1;
						}
					} else {
						// Ansonsten übernehme die Anzahl der Namen
						$regCnt = count($regNames);
					}
				} else {
					// Übernehme immer nur max. die Anzahl der eingegebenen Namen
					$regCnt = count($regNames);
				}

				$this->setRegistrations($regCnt+1);  // Der eigene Anmeldung kommt ja noch dazu...
				if (!empty($regNames)) {
					$this->setPara("regNames", json_encode($regNames));
				}
			}

		} else {
			// Wenn man nicht mehrere Anmelden kann, dann wird immer nur genau einer angemeldet
			$this->setRegistrations(1);
		}
		
		// Nur der Admin darf den Zustand ändern
		if (LGV_AS_Util::isEditorOrAdmin()) {
			if (isset($_POST['lgvas_state'])) {
				$state = intval($_POST['lgvas_state']);
				$this->setState($state);
				//print_r($this);
			}
		}
		
		// Übernehme die AddParas
		foreach($evt->AddParaMain->Parameters as $para)
		{
			//print_r($this);
			$para->fillFromPostData($this->parameters);
		}

		foreach($evt->AddParaMain->Parameters as $para)
		{
			// Wenn wir jetzt einen Parameter haben, der angibt ob man registriert ist (IsRegInfo), dann setze die Anzahl wieder auf 0, falls dies gesetzt ist
			if (intval($para->IsRegInfo) == 1) {
				$val = intval($para->getDef($this->getParameters()));
				if ($val <= 0) {
					$this->setRegistrations(0);
				}
				break; // Wenn ich ein Feld damit gefunden habe, dann suche nicht weiter; das "IsRegInfo" darf nur einmal vorkommen...
			}
		}

		//echo "<hr/><strong>HALLO</strong>";
		//print_r($this->parameters);
	}
	
	abstract protected function getFromEmail($email, $evtId, $selfId = null);
	abstract protected function getAllActiveFromEmail($email);
	
	public function validate($maxAnzahl, $evt) {
		$res = array();
		$checkEmail = true;
		if (empty($this->lastName)) {
			$res["lastName"] = "Sie müssen einen Nachnamen eingeben!";
		} else
		{
			if (!empty($evt->AddParaMain->LastNameRegEx)) {
				if (!preg_match("/" . $evt->AddParaMain->LastNameRegEx . "/", $this->lastName)) {
					if (empty($evt->AddParaMain->LastNameRegExMsg)) {
						$res["lastName"] = "Bitte geben Sie einen gültigen Namen ein";
					} else {
						$res["lastName"] = $evt->AddParaMain->LastNameRegExMsg;
					}
				}
			}
		}
				
		if (empty($this->firstName)) {
			$res["firstName"] = "Sie müssen einen Vornamen eingeben!";
		} else
		{
			if (!empty($evt->AddParaMain->FirstNameRegEx)) {
				if (!preg_match("/" . $evt->AddParaMain->FirstNameRegEx . "/", $this->firstName)) {
					if (empty($evt->AddParaMain->FirstNameRegExMsg)) {
						$res["firstName"] = "Bitte geben Sie einen gültigen Namen ein";
					} else {
						$res["firstName"] = $evt->AddParaMain->FirstNameRegExMsg;
					}
				}
			}
		}
		if (empty($this->street) && ($evt->AddParaMain->OptStreet == 0)) {
			if (LGV_AS_Util::isEditorOrAdmin() == false) {
				$res["street"] = "Sie müssen eine Straße und Hausnummer eingeben!";
			}
		}
		if (empty($this->zipCode) && ($evt->AddParaMain->OptZip == 0)) {
			if (LGV_AS_Util::isEditorOrAdmin() == false) {
				$res["zipCode"] = "Sie müssen eine PLZ eingeben!";
			}
		}
		if (empty($this->city) && ($evt->AddParaMain->OptCity == 0)) {
			if (LGV_AS_Util::isEditorOrAdmin() == false) {
				$res["city"] = "Sie müssen einem Ort eingeben!";
			}
		}
		
		if ($evt->AddParaMain->MaxPerRegister > 1) {
			if ($evt->AddParaMain->RegWithNames == false || LGV_AS_Util::isEditorOrAdmin()) {
				// Max-Registrations!
				if (intval($this->registrations) > intval($maxAnzahl)) {
					$res["registrations"] = "Die Anzahl der registrierten Personen ist zu hoch (maximal " . intval($maxAnzahl) . ")!";
				}
				else if (intval($this->registrations) <= 0 && ($evt->AddParaMain->RegWithNames == false)) {
					$res["registrations"] = "Sie müssen eine Anzahl eingeben, wie viele Personen Sie registrieren möchten!";
				}
			} else {
				// Es werden die einzelnen Werte angegegen.. 
				// Überprüfe ob auch soviel angeben sind, wie im Array drin sind...
				$regNames1 = $this->getPara("regNames");
				if (!empty($regNames1)) {
					$regNames = json_decode($regNames1, true);
					if (count($regNames) < ($this->registrations-1)) {
						// Gebe Fehlermeldungen für die Namen aus...
						for($i=count($regNames); $i<($this->registrations-1); $i++) {
							$res['lgvas_para_anzahl' . ($i+1)] = "Bitte geben Sie einen Namen an";
						}
					}
				}
			}
		}
		
		// Validiere die AddParams
		foreach($evt->AddParaMain->Parameters as $para)
		{
			//print_r($this);
			$para->validate($this->parameters, $res, $this->registrations);
		}
		
		if (empty($this->id) || LGV_AS_Util::isEditorOrAdmin()) {
			// Wenn wir den Datensatz noch nicht gespeichert haben, dann prüfe auf "korrekte" E-Mail Adresse
			// Da im Admin-Mode auch die Adresse eingegeben werden kann, prüfe auch hier
			if (isset($this->email)) {
				$this->email = trim($this->email);  // entferne zuerst mal alle Leerzeichen
			}
			if (empty($this->email)) {
				// Wenn ich angemeldet bin, brauche ich keine E-Mail anzugeben
				if (LGV_AS_Util::isEditorOrAdmin() == false) {
					$res["email"] = "Sie müssen einen E-Mail Adresse eingeben!";
					$checkEmail = false;
				}
				else {
					$checkEmail = false;
				}
			}
			elseif (!is_email($this->email)) {
				$res["email"] = "Sie müssen einen gültigen E-Mail Adresse eingeben!";
			}
			else {
				if (isset($this->emailConfirm)) {
					$this->emailConfirm = trim($this->emailConfirm);
				}
				if (empty($this->emailConfirm)) {
					$res["emailConfirm"] = "Sie müssen die E-Mail Adresse wiederholen!";
				}
				elseif ($this->email != $this->emailConfirm) {
					$res["emailConfirm"] = "Die E-Mail Adresse entspricht nicht der oben eingegebenen!";
				}
			}
				
			if ($checkEmail && (intval($evt->AddParaMain->MultiEmail) != 1)) {
				// Prüfe ob es schon eine Registrierung dieser E-Mail Adresse für diese Veranstaltung gibt!
				$sameReg = $this->getFromEmail($this->email, $this->eventId, $this->id);
				if (!empty($sameReg)) {
					// Link zum wieder "Anfordern" der Daten...
					$resTxt = "Mit dieser E-Mail Adresse gibt es schon eine Registrierung![br]";
					//$resTxt .= "<a href='?req-mail=1'>Bitte fordern Sie Ihre Daten nochmals per E-Mail an.</a>";
					$resTxt .= "Bitte fordern Sie Ihre Daten nochmals per E-Mail an.";
					$res["email"] = $resTxt;
				}
			}
		}
		
		switch($this->getState()) {
			case LGV_AS_BO::Registration_State_NotConfirmed:
			case LGV_AS_BO::Registration_State_Registered:
			case LGV_AS_BO::Registration_State_WaitList:
			case LGV_AS_BO::Registration_State_Deleted:
				break;
			default:
				$res["state"] = "Ungültiger Zustand!";
				break;
		}

		return $res;
	}
	
	// Wird aufgerufen, wenn der Benutzer seine Daten nochmals anfordern will
	public static function sendNotifyFromEmail($email) {
		$email = trim($email);
		if (empty($email)) {
			return FALSE;
		}
		
		$bo = new LGV_AS_DB_Registration();
		$all = $bo->getAllActiveFromEmail($email);
		
		//print_r($all);
		
		if (is_array($all) && (count($all) > 0) )  {
			$all[0]->sendNotifyMail(false, false, true);
			return TRUE;
		}
		return FALSE;
	}
	
	public function getMailText($moveUp = false, $modified = false, $request = false, $oldState = -1, $preview = false) {
		$email = trim($this->email);
		
		// Falls keine E-Mail angegeben ist, so verwende die vorgegebene E-Mail
		$isEmptyMail = false;
		if (empty($email)) {
			$isEmptyMail = true;
		}
		
		if ($preview == false) {
			// Hole auch hier die Daten von einer "Leerern E-Mail"
			$all = $this->getAllActiveFromEmail($email);
		} else {
			$all = array( $this );
		}
		
		//print_r($this);
		
		// We try to re-order the array, so the current registration will be placed first
		$all = array_values($all);  // be sure we have azero based array
		for ($idx = 1; $idx < count($all); $idx++) {
			if ($all[$idx]->getId() == $this->getId()) {
				// now move the found entry to the top ;)
				$tmp = $all[0];  // safe the first entry
				$all[0] = $all[$idx];
				$all[$idx] = $tmp;
				break;
			}
		}
		
		//print_r($all);

		$message = "<h2>Vielen Dank für Ihre Anmeldung!</h2>";
		
		if ($request) {
			$message .= "<p>Ihre Daten wurden von der Anmeldeseite angefordert";
			$message .= " und werden Ihnen hiermit nochmals zugesandt.</p>";
		}
		if ($modified) {
			$message .= "<p>Ihre Daten wurden geändert";
			$message .= " und werden Ihnen hiermit nochmals zugesandt.</p>";
		}
		
		$hasRegisteredEntries = false;
		$waitingListInfoAlreadyVisible = false;
		if ($moveUp) {
			$message .= "<h3>Es sind Plätze frei geworden!</h3>";
			$message .= "<p>";
			$message .= "<strong>Bitte bestätigen Sie nun Ihre Anmeldung</strong>, ";
			$message .= "indem Sie in der unten stehenden Liste bei allen Einträgen, ";
			$message .= "die den Status 'Warten auf Bestätigung' haben, auf den 'Bearbeiten' Link drücken! ";
			$message .= "Dort können Sie Ihre Daten ändern und müssen dann Ihre Anmeldung nochmals bestätigen! ";
			$message .= "Sollten Sie dies nicht innerhalb der nächsten 2 Tage machen, so werden Sie wieder auf die Warteliste verschoben!";
			$message .= "</p>";
		}

		$message .= "<h3>Wir haben aktuell folgende Daten für Sie gespeichert:</h3>";

		$grussMsg = "";
		$activeGroup = null;
		foreach ( $all as $r ) 
		{
			$message .= "<hr />";
			// Hole die Infos für den Event
			$evt = LGV_AS_DB_Event::find($r->getEventId());
			//print_r($r, true);
			
			if (empty($grussMsg) && !empty($evt->AddParaMain->GrussMsg)) {
				$grussMsg = $evt->AddParaMain->GrussMsg;
			}

			// Grouping informtation
			if (!empty($evt->AddParaMain->GroupKey)) {
				if ($evt->AddParaMain->GroupKey !== $activeGroup) {
					$evtGrp = LGV_AS_DB_EventGroup::findByKey($evt->AddParaMain->GroupKey);
					if (!empty($evtGrp)) {
						$message .= "<h2>" . esc_html($evtGrp->AddPara->Title) . "</h2>";
						$activeGroup = $evtGrp->getKey();
					}
					else {
						$activeGroup = null;
					}
				}
			}
			else {
				$activeGroup = null;
			}
			
			$vh = "h2";
			if (!empty($activeGroup)) {
				$vh = "h3";
			}
			
			$message .= "<" . $vh . ">" . esc_html($evt->getTitle()) . "</" . $vh . ">";
			if (empty($evt->AddParaMain->HeaderText) == false) {
				$message .= "<p>";
				$message .= LGV_AS_Util::getText(esc_html($evt->AddParaMain->HeaderText));
				$message .= "</p>";
			}
			
			$message .= "<table border='1'>";
			$message .= "<tr><td>Name:</td><td>" . esc_html($r->getFirstName()) . " " . esc_html($r->getLastName()) . "</td></tr>";
			if (($evt->AddParaMain->OptZip == 0) || ($evt->AddParaMain->OptCity == 0) || ($evt->AddParaMain->OptStreet == 0) ) {
				$message .= "<tr><td>Strasse / Ort</td><td>" . esc_html($r->getStreet()) . " / " . esc_html($r->getZipCode()) . " " . esc_html($r->getCity()) . "</td></tr>";
			}
			$message .= "<tr><td>E-Mail</td><td>" . esc_html($r->getEmail()) . "</td></tr>";
			
			// Gebe die Anzahl nur aus, wenn es auch eine gibt
			$isRegistered = true;
			if ($evt->AddParaMain->MaxPerRegister > 1) {
				$regCnt = $r->getRegistrations();
				if (($r->getState() == LGV_AS_BO::Registration_State_Registered) && ($regCnt <= 0)) { $regCnt = 1; }
				$message .= "<tr><td>Anmeldungen</td><td>" . esc_html($regCnt) . "</td></tr>";
				
				// Gebe die jeweiligen Personen-Name noch aus:
				$regNamesStr = $r->getPara("regNames");
				if (!empty($regNamesStr)) {
					$regNames = json_decode($regNamesStr, true);
					if (count($regNames) > 0) {
						$message .= "<tr><td>Zusätzliche Personen</td><td>";
						$regCnt = 0;
						foreach($regNames as $rn) {
							if ($regCnt > 0) {
								$message .= " / ";
							}
							$message .= esc_html($rn);
							$regCnt++;
						}
						$message .= "</td></tr>";
					}
				}
			} else {
				// Die Veranstaltung hat also keine Anzahl zum Anmelden; also gibt es ein Feld, wo angibt, ob man sich angemeldet hat!
				// Werte dieses aus, so dass wir weiter unten das passende Feld anzeigen können...
				foreach($evt->AddParaMain->Parameters as $para) {
					if (intval($para->IsRegInfo) == 1) {
						//print_r($para);
						//print_r($r);
						$val = intval($para->getDef($r->getParameters()));
						if ($val <= 0) {
							$isRegistered = false;
						}
						break; // Wenn ich ein Feld damit gefunden habe, dann suche nicht weiter; das "IsRegInfo" darf nur einmal vorkommen...
					}
				}
			}
			
			$message .= "<tr><td>Status:</td>";
			switch($r->getState())
			{
				case LGV_AS_BO::Registration_State_Registered:
					if ($evt->AddParaMain->MaxPerRegister <= 1) {
						if ($isRegistered) {
							$hasRegisteredEntries = true;
							$message .= "<td style='color:green'><strong>Registriert (Angemeldet)</strong></td>";
						} else {
							$message .= "<td style='color:red'><strong>Registriert (Abgemeldet)</strong></td>";
						}
					} else {
						$hasRegisteredEntries = true;
						$message .= "<td style='color:green'><strong>Registriert</strong></td>";
					}
					break;
				case LGV_AS_BO::Registration_State_WaitList:
					$message .= "<td style='color:darkblue'><strong>Warteliste</strong></td>";
					break;
				case LGV_AS_BO::Registration_State_NotConfirmed:
					if ($isEmptyMail == false) {
						$message .= "<td><a href='" . LGV_AS_BO::getEditUrl($r->getEmail(), $r->getModifyPassword()) . "'>Warten auf Bestätigung</a></td>";
					} else {
						$message .= "<td><a href='" . LGV_AS_BO::getEditUrlFromId($r->getId()) . "'>Warten auf Bestätigung</a></td>";
					}
					break;
				case LGV_AS_BO::Registration_State_Deleted:
					$message .= "<td style='color:red'><strong>Gelöscht</strong></td>";
					break;
			}
			$message .= "</tr>";
			
			// Füge noch die sonstigen Infos ein für diesen Event
			$addMessage = "";
			//$addMessage .= print_r($r->getParameters(), true);
			//$addMessage = print_r($evt, true);
			foreach($evt->AddParaMain->Parameters as $para) {
				//$addMessage .= print_r($para, true);

				if ( (empty($para->NotInMail) == false) && (intval($para->NotInMail) != 0) ) {
					// Ignore parameter, if it should not be present in the mail
					continue;
				}
				switch($para->Typ)
				{
					case "MinToMax":
					case "MinToMaxRegistered":
					case "Bool":
					case "MinToMaxText";
						$valTxt = $para->getDef($r->getParameters());
						$cnt = intval($valTxt);
						if ($cnt > 0) {
							if (empty($addMessage) == false) {
								$addMessage .= "</br>";
							}
							$addMessage .= LGV_AS_Util::getText(esc_html($para->Title));
							if ($para->Typ != "Bool") {
								$addMessage .= ": " . $cnt;
							}
						}
						break;
					case "Sel":
					case "Date":
					case "Text":
						$valTxt = $para->getDef($r->getParameters());
						if (!empty($valTxt)) {
							if (empty($addMessage) == false) {
								$addMessage .= "</br>";
							}
							$addMessage .= LGV_AS_Util::getText(esc_html($para->Title)) . ": " . $valTxt;
						}
						break;
					case "Payment":
						if (!empty($r->getParameters())) {
							$valTxt = $r->getParameters()[$para->getParaName()];
							$data = LGV_AS_PaymentData::FromJsonString($valTxt);
							$addMessage .= LGV_AS_Util::getText(esc_html($para->Title)) . ": " . $data->ToMailString();
						}
						break;
				}
			}
			if (empty($addMessage) == false) {
				$message .= "<tr><td>Sonstiges</td><td>" . $addMessage . "</td></tr>";
			}
			
			if (intval($evt->AddParaMain->EditMode) == 0) {
				if ($isEmptyMail == false) {
					$message .= "<tr><td colspan='2'><a href='" . LGV_AS_BO::getEditUrl($r->getEmail(), $r->getModifyPassword()) . "'>Bearbeiten</a></td></tr>";
				} else {
					$message .= "<tr><td colspan='2'><a href='" . LGV_AS_BO::getEditUrlFromId($r->getId()) . "'>Bearbeiten</a></td></tr>";
				}
			}

			$message .= "</table>";
			
		// Bei Warteliste:
			if ($r->getState() == LGV_AS_BO::Registration_State_WaitList) {
				$message .= "<p style='color:darkblue'>";
				$message .= "<strong>Status Warteliste:</strong></br>";
				$message .= "Da die Veranstaltung bereits voll belegt ist, haben wir Sie auf der Warteliste notiert. Sobald Plätze frei ";
				$message .= "werden, erhalten Sie von uns eine E-Mail. Falls Sie keine Rückmeldung von uns bekommen, ist leider ";
				$message .= "keine Teilnahme möglich.";
				$message .= "</p>";
			}
			
			// Allgemeine Infos:
			if (!empty($evt->AddParaMain->AllgMsg)) {
				$message .= "<p>";
				$message .=  LGV_AS_Util::getText(esc_html($evt->AddParaMain->AllgMsg));
				$message .= "</p>";
			}

			if ($isRegistered && !empty($evt->AddParaMain->RegisteredMsg)) {
				$message .= "<p>";
				$message .=  LGV_AS_Util::getText(esc_html($evt->AddParaMain->RegisteredMsg));
				$message .= "</p>";
			}
		}

		if (!empty($grussMsg)) {
			$message .= LGV_AS_Util::getText(esc_html($grussMsg));
		}

		return $message;
	}	

	public function sendNotifyMail($moveUp = false, $modified = false, $request = false, $oldState = -1) {
		$email = trim($this->email);
		
		// Falls keine E-Mail angegeben ist, so verwende die vorgegebene E-Mail
		if (empty($email)) {
			$email = LGV_AS_BO::getEmptyEMailReceiver();
		}
		
		$headers[] = 'From: ' . LGV_AS_BO::getEMailFrom();
		
		if (LGV_AS_BO::sendCc($moveUp, $modified, $request)) {
			$headers[] = 'Cc: ' . LGV_AS_BO::getEMailCc();
		}
		
		$bcc = LGV_AS_BO::getEMailBcc();
		if (!empty($bcc)) {
			$headers[] = 'Bcc: ' . $bcc;
		}
		
		$subject = "Ihre Anmeldung zu unseren Veranstaltungen";
		
		if ($moveUp) {
			$subject .= " (Platz frei geworden!)";
		}	

		$to = $email;
		if (empty($to)) {
			return; // Niemand da, der meine Mail will...
		}		

		$message = $this->getMailText($moveUp, $modified, $request, $oldState, false);

		$headers[] = 'Content-type: text/html';
		
		wp_mail( $to, $subject, $message, $headers);
	}
}


