<?php

/**
 * Description of class-LGV_AS_DB_Registration
 *
 * @authors Jochen Kalmbach
 */

class LGV_AS_DB_Registration extends LGV_AS_BO_Registration {
	static function find($id) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}
		
		$sql = "SELECT * 
				FROM ". LGV_AS_DB::getRegistrationTableName() ."
				WHERE id = " . intval($id);
		$evt = $wpdb->get_row($sql);
		if (empty($evt))
			return NULL;
		$dataEvt = self::fromDb($evt);
		return $dataEvt;	
	}

	// Bedingung:
	// - Veranstaltungs-Id passt (evtId)
    // - Status == Warteliste
	static function findFirstFromWaitingList($evtId) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}
		
		$sql = $wpdb->prepare(
				"SELECT * 
				FROM ". LGV_AS_DB::getRegistrationTableName() ."
				WHERE event_id = %d
				AND reg_state = %d
				AND registrations > 0
				ORDER BY 
				ISNULL(waiting_list_date_time) DESC,
				waiting_list_date_time,
				id
				",
				intval($evtId),
				LGV_AS_BO::Registration_State_WaitList
		);
		//$allRegs = $wpdb->get_results($sql);
		//foreach ( $allRegs as $reg ) {
		//	echo "<hr/>";
		//	print_r($reg);
		//}
		//return NULL;
			
		$evt = $wpdb->get_row($sql);
		if (empty($evt))
			return NULL;
		$dataEvt = self::fromDb($evt);
		
		//print_r($dataEvt);
		return $dataEvt;
	}

	static function findFromUserData($email, $passcode) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}
		
		$sql = $wpdb->prepare(
			"SELECT * 
			FROM ". LGV_AS_DB::getRegistrationTableName() ." 
			WHERE email = %s
			AND modify_password = %s
			",
			$email,
			$passcode
		);
		$evt = $wpdb->get_row($sql);
		if (empty($evt))
			return NULL;
		$dataEvt = self::fromDb($evt);
		return $dataEvt;	
	}
	
	static function outputAllToCsv($evtId, $evt, $withBOM = true) {
		$startIdx = 0;
		$pageSize = 500;
		if ($withBOM) {
			// Start a UTF-8 file ;)
			// See: http://www.mwasif.com/2007/5/download-data-csv-using-php/
			// http://stackoverflow.com/questions/5368150/php-header-excel-and-utf-8
			echo pack("CCC",0xef,0xbb,0xbf);
		}
		$csvHeaders = array(
			"Id",
			"Zustand",
			"Nachname", 
			"Vorname",
			"Straße", 
			"PLZ", 
			"Ort", 
			"Telefon privat", 
			"E-Mail privat", 
			"Anzahl",
			"Created",
			"WarteListeStart",
			"Nachgerückt",
			"Log"
			);
			
		if ($evt->AddParaMain->RegWithNames) {
			array_push($csvHeaders, "Weitere Personen");
		}
		// Füge die AddParas dazu..
		foreach($evt->AddParaMain->Parameters as $para1) {
			array_push($csvHeaders, $para1->Title);
		}

		echo LGV_AS_Util::get_csv_encoded($csvHeaders);
		do {
			$allRegs = self::all($evtId, $startIdx, $pageSize);
			if (count($allRegs) <= 0) {
				return;
			}
			foreach ( $allRegs as $reg ) {
				$fields = array(
					$reg->getId(), 
					LGV_AS_BO::getRegStateName($reg->getState()),
					$reg->getLastName(),
					$reg->getFirstName(),
					$reg->getStreet(),
					$reg->getZipCode(),
					$reg->getCity(),
					$reg->getPhone(),
					$reg->getEmail(),
					$reg->getRegistrations(),
					$reg->getCreatedDateTime(),
					$reg->getWaitingListDateTime(),
					$reg->getPara("not_confirmed_start"),
					$reg->getPara("log")
				);
				
				if ($evt->AddParaMain->RegWithNames) {
					$regNamesStr = "";
					$regNamesJson = $reg->getPara("regNames");
					if (empty($regNamesJson) == false) {
						$regNames = json_decode($regNamesJson, true);
						$regCnt = 0;
						foreach($regNames as $rn) {
							if ($regCnt > 0) {
								$regNamesStr .= " / ";
							}
							$regNamesStr .= $rn;
							$regCnt++;
						}
					}
					array_push($fields, $regNamesStr);
				}
			
				// Add all AddParas...
				foreach($evt->AddParaMain->Parameters as $para) {
					$arrVal = "";
					switch($para->Typ)
					{
						case "MinToMax":
						case "MinToMaxRegistered":
						case "Bool":
						case "MinToMaxText";
							$valTxt = $para->getDef($reg->getParameters());
							$cnt = intval($valTxt);
							if ($cnt > 0) {
								$arrVal = $cnt;
							}
							break;
						case "Sel":
						case "Date":
						case "Text":
							$valTxt = $para->getDef($reg->getParameters());
							if (!empty($valTxt)) {
								$arrVal = $valTxt;
							}
							break;
						case "Payment":
							if (!empty($reg->getParameters())) {
								$valTxt = $reg->getParameters()[$para->getParaName()];
								$data = LGV_AS_PaymentData::FromJsonString($valTxt);
								$arrVal = $data->ToCsvString();
							}
							break;
					}
					array_push($fields, $arrVal);
				}
				
				echo LGV_AS_Util::get_csv_encoded($fields);
			}
			
			if (count($allRegs) < $pageSize) {
				// War wohl der letzte Datensatz...
				return;
			}
			$startIdx += count($allRegs);
		} while (true);
	}
	
	static function all($evtId, $startIdx = NULL, $pageSize = 100, $name = NULL, $email = NULL, $plz = NULL,
		$registered = NULL,
		$waitingList = NULL,
		$notConfirmed = NULL,
		$deleted = NULL
	) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}
		
		$sqlStr = "SELECT * 
				FROM ". LGV_AS_DB::getRegistrationTableName() ." 
				WHERE event_id = %d";
		$par = array();
		if (!empty($name)) {
			$sqlStr .= " AND last_name LIKE '%%%s%%'";
			array_push($par, $name);
		}
		if (!empty($email)) {
			$sqlStr .= " AND email LIKE '%%%s%%'";
			array_push($par, $email);
		}
		if (!empty($plz)) {
			$sqlStr .= " AND zip_code LIKE '%%%s%%'";
			array_push($par, $plz);
		}
		
		$sqlState = "";
		if (!empty($registered) && intval($registered) > 0) {
		  if (!empty($sqlState)) { $sqlState .= " OR "; }
		  $sqlState .= "reg_state = %d";
		  array_push($par, LGV_AS_BO::Registration_State_Registered);
		}
		if (!empty($waitingList) && intval($waitingList) > 0) {
		  if (!empty($sqlState)) { $sqlState .= " OR "; }
		  $sqlState .= "reg_state = %d";
		  array_push($par, LGV_AS_BO::Registration_State_WaitList);
		}
		if (!empty($notConfirmed) && intval($notConfirmed) > 0) {
		  if (!empty($sqlState)) { $sqlState .= " OR "; }
		  $sqlState .= "reg_state = %d";
		  array_push($par, LGV_AS_BO::Registration_State_NotConfirmed);
		}
		if (!empty($deleted) && intval($deleted) > 0) {
		  if (!empty($sqlState)) { $sqlState .= " OR "; }
		  $sqlState .= "reg_state = %d";
		  array_push($par, LGV_AS_BO::Registration_State_Deleted);
		}
		
		if (!empty($sqlState)) {
			$sqlStr .= " AND (" . $sqlState . ")";
		}
		
		//while(count($par) < 7) {
		//	array_push($par, "");
		//}
				
		$sqlStr .= " ORDER BY id ";
		if (is_int($startIdx)) {
			$sqlStr .= "LIMIT " . intval($startIdx) . "," . intval($pageSize) . " ";
		}
		
		array_unshift($par , intval($evtId));
		$sql = $wpdb->prepare($sqlStr, $par);
			
		$allRegs = $wpdb->get_results($sql);

		//print_r($allRegs);
		
		$res = array();
		foreach ( $allRegs as $reg ) 
		{
			$dataReg = self::fromDb($reg);
			array_push($res, $dataReg);
		}	
		return $res;
	}
	
	static function countEntries($evtId) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}
		
		$sqlStr = "SELECT COUNT(*) AS cnt
				FROM ". LGV_AS_DB::getRegistrationTableName() ." 
				WHERE event_id = %d";
				
		$sql = $wpdb->prepare(
				$sqlStr,
				intval($evtId)
			);
			
		$row = $wpdb->get_row($sql);
		if (empty($row)) {
			return 0;
		}
		
		if (isset($row->cnt)) {
			return $row->cnt;
		}
		return 0;
	}
	
	static function fromDb($evt) {
		//print_r($evt);
		$dataEvt = new LGV_AS_DB_Registration();
		$dataEvt->setId($evt->id);
		$dataEvt->setEventId($evt->event_id);
		$dataEvt->setCreatedDateTime($evt->created_date_time);
		$dataEvt->setEmail($evt->email);
		$dataEvt->setModifyPassword($evt->modify_password);
		$dataEvt->setFirstName($evt->first_name);
		$dataEvt->setLastName($evt->last_name);
		$dataEvt->setState($evt->reg_state);
		$dataEvt->setStreet($evt->street);
		$dataEvt->setZipCode($evt->zip_code);
		$dataEvt->setCity($evt->city);
		$dataEvt->setPhone($evt->phone);
		$dataEvt->setRegistrations(intval($evt->registrations));
		$dataEvt->setWaitingListDateTime($evt->waiting_list_date_time);
		$p = json_decode($evt->parameters, true);
		if (!empty($p)) {
			//print_r($p);
			$dataEvt->setParameters($p);
		}
		
		// Daten, welche nur gesetzt sind, wenn es über "getAllActiveFromEmail" aufgerufen wird
		if (isset($evt->evt_title)) {
			$dataEvt->EventTitle = $evt->evt_title;
		}
		//print_r($dataEvt);
		return $dataEvt;
	}
	
	static function deleteAll($evt) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$rows_affected = $wpdb->delete( LGV_AS_DB::getRegistrationTableName(), 
				array( 'event_id' => $evt->getId())
				);
			return $rows_affected;
		}
		return 0;
	}
	
	public function delete() {
		global $wpdb;
		if (LGV_AS_Util::isEditorOrAdmin()) {
			$rows_affected = $wpdb->delete( LGV_AS_DB::getRegistrationTableName(), 
				array( 'id' => $this->getId())
				);
			return $rows_affected;
		}
		return 0;
	}
	
	protected function getFromEmail($email, $evtId, $selfId = null) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}
		
		if (empty($selfId)) {
			$sql = $wpdb->prepare(
				"SELECT * 
				FROM ". LGV_AS_DB::getRegistrationTableName() ." 
				WHERE email = %s
				AND event_id = %d
				",
				$email,
				$evtId
			);
		}
		else {
			$sql = $wpdb->prepare(
				"SELECT * 
				FROM ". LGV_AS_DB::getRegistrationTableName() ." 
				WHERE email = %s
				AND event_id = %d
				AND id != %d
				",
				$email,
				$evtId,
				$selfId
			);
		}
		$evt = $wpdb->get_row($sql);
		if (empty($evt))
			return NULL;
		$dataEvt = self::fromDb($evt);
		return $dataEvt;
	}

	// Entscheidung: 
	// Es werden ALLE Einträge gesendet; auch diese, welche vom Benutzer gelöscht wurden!
	// Damit bekommt er auch eine Bestätigung für ein Löschvorgang und kann später diesen Eintrag 
	// auch wieder bearbeiten und somit wieder "aktiveren"...
	protected function getAllActiveFromEmail($email) {
		$isEmptyEmail = false;
		$em = trim($email);
		if (empty($em)) {
			$isEmptyEmail = true;
		}
		
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}
		$sql = "SELECT r.id, r.event_id, r.created_date_time, r.email, r.modify_password,
				r.first_name, r.last_name, r.reg_state, r.street, r.zip_code,
				r.waiting_list_date_time,
				r.city, r.phone, r.registrations, r.parameters, e.title as evt_title
				FROM 
				". LGV_AS_DB::getRegistrationTableName() . " r
				,". LGV_AS_DB::getEventTableName() . " e ";
		if ($isEmptyEmail == false) {
			$sql = $wpdb->prepare(
				$sql . "
				WHERE r.email = %s
				AND r.event_id = e.id
				AND e.event_state = %d
				",	
				$email,
				LGV_AS_BO::Event_State_Active
			);
		} else {
			$sql = $wpdb->prepare(
				$sql . "
				WHERE (r.email IS NULL OR r.email = '')
				AND r.event_id = e.id
				AND e.event_state = %d
				",	
				LGV_AS_BO::Event_State_Active
			);
		}

		//// Sort by id, so the newest will be sent first...
		//$sql .= " ORDER BY r.id DESC";
		
		//print_r($sql);
		
		$allRegs = $wpdb->get_results($sql);
		
		//print_r($allRegs);
		
		$res = array();
		foreach ( $allRegs as $reg ) 
		{
			//print_r($reg);
			$dataReg = self::fromDb($reg);
			//print_r($dataReg);
			array_push($res, $dataReg);
		}	
		return $res;
	}

	public function store($sendMail = true, $moveUp = false, $oldState = -1, $preview = false) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}
		
		//print_r($this);
		
		$modified = false;
		if (empty($this->id)) {
			//echo "<h1>Store new record</h1>";
			// Create a new record
			// First create a new password for this entry...
			$pwd = LGV_AS_Util::get_random_passcode();
			$this->setModifyPassword($pwd);
			
			if ($preview == false) {
				$rows_affected = $wpdb->insert( LGV_AS_DB::getRegistrationTableName(), array( 
					'event_id' => $this->getEventId(),
					'created_date_time' => current_time('mysql'), 
					'email' => $this->getEmail(), 
					'modify_password' => $this->getModifyPassword(), 
					'first_name' => $this->getFirstName(), 
					'last_name' => $this->getLastName(), 
					'reg_state' => intval($this->getState()),
					'street' => $this->getStreet(), 
					'zip_code' => $this->getZipCode(), 
					'city' => $this->getCity(), 
					'phone' => $this->getPhone(), 
					'waiting_list_date_time' => current_time('mysql'), 
					'registrations' => intval($this->getRegistrations()),
					'parameters' => json_encode($this->getParameters())
					) );
				$this->id = $wpdb->insert_id;
			}
		}
		else {
			$modified = true;
			
			if ($preview == false) {
				// Parameters to update:
				$updateArr = array( 
					'first_name' => $this->getFirstName(), 
					'last_name' => $this->getLastName(), 
					'reg_state' => intval($this->getState()),
					'street' => $this->getStreet(), 
					'zip_code' => $this->getZipCode(), 
					'city' => $this->getCity(), 
					'phone' => $this->getPhone(), 
					'registrations' => intval($this->getRegistrations()),
					'parameters' => json_encode($this->getParameters())
					);
			
				// Update the existing record
				if (LGV_AS_Util::isEditorOrAdmin()) {
					// Also update the e-mail if it is comming from the admin
					$updateArr['email'] = $this->getEmail();
				}
			
				if ( ($this->getState() == LGV_AS_BO::Registration_State_WaitList)
					&& ($oldState != LGV_AS_BO::Registration_State_WaitList) )
				{
					// We was switched to "Waiting-List", so set the current date as new position in the waiting list...
					$updateArr['waiting_list_date_time'] = current_time('mysql');
				}
			
				$rows_affected = $wpdb->update( LGV_AS_DB::getRegistrationTableName(), 
					$updateArr,
					array( 'id' => $this->getId())
					);
			}
		}
		
		
		if ($preview) {
			$previewText = $this->getMailText($moveUp, $modified, false, $oldState, true);
			return $previewText;
		}
		else if ($sendMail) {
			$this->sendNotifyMail($moveUp, $modified, false, $oldState);
		}
		
		
		
		return "";
	}
}
