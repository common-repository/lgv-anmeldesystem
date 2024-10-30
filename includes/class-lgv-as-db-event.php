<?php

/**
 * Description of class-LGV_AS_DB_Event
 *
 * @authors Jochen Kalmbach
 */

 
//SELECT 
//  a.id, a.created_date_time, a.title, a.description, a.event_state, a.max_registrations
//  ,b.not_confirmed_cnt, c.registered_cnt,  d.waiting_list_cnt, e.deleted_cnt
//FROM wp_lgvas_event a
//LEFT JOIN (SELECT event_id, SUM(registrations) AS not_confirmed_cnt FROM wp_lgvas_registration WHERE state = 0 GROUP BY event_id) b ON a.id = b.event_id
//LEFT JOIN (SELECT event_id, SUM(registrations) AS registered_cnt FROM wp_lgvas_registration WHERE state = 1 GROUP BY event_id) c ON a.id = c.event_id
//LEFT JOIN (SELECT event_id, SUM(registrations) AS waiting_list_cnt FROM wp_lgvas_registration WHERE state = 2 GROUP BY event_id) d ON a.id = d.event_id
//LEFT JOIN (SELECT event_id, SUM(registrations) AS deleted_cnt FROM wp_lgvas_registration WHERE state = 3 GROUP BY event_id) e ON a.id = e.event_id
//WHERE a.id = 3


 
class LGV_AS_DB_Event extends LGV_AS_BO_Event {
	static function find($id) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}

		$sql = 
		"SELECT a.id, a.created_date_time, a.title, a.description, a.event_state, a.max_registrations, a.additional_parameters " . 
		", b.not_confirmed_cnt " .
		", c.registered_cnt " .
		", d.waiting_list_cnt " .
		", e.deleted_cnt " .
		"FROM " . LGV_AS_DB::getEventTableName() . " a " . 
		"LEFT JOIN (SELECT event_id, SUM(registrations) AS not_confirmed_cnt FROM " . LGV_AS_DB::getRegistrationTableName() ." WHERE reg_state = " . intval(LGV_AS_BO::Registration_State_NotConfirmed) . " GROUP BY event_id) b ON a.id = b.event_id " .
		"LEFT JOIN (SELECT event_id, SUM(registrations) AS registered_cnt FROM " . LGV_AS_DB::getRegistrationTableName() ." WHERE reg_state = " . intval(LGV_AS_BO::Registration_State_Registered) . " GROUP BY event_id) c ON a.id = c.event_id " .
		"LEFT JOIN (SELECT event_id, SUM(registrations) AS waiting_list_cnt FROM " . LGV_AS_DB::getRegistrationTableName() ." WHERE reg_state = " . intval(LGV_AS_BO::Registration_State_WaitList) . " GROUP BY event_id) d ON a.id = d.event_id " .
		"LEFT JOIN (SELECT event_id, SUM(registrations) AS deleted_cnt FROM " . LGV_AS_DB::getRegistrationTableName() ." WHERE reg_state = " . intval(LGV_AS_BO::Registration_State_Deleted) . " GROUP BY event_id) e ON a.id = e.event_id " .
		"WHERE id = " . intval($id) .
		"";
		//print_r($sql);
		
		$evt = $wpdb->get_row($sql);
		if (empty($evt))
			return NULL;
			
		$dataEvt = self::fromDb($evt);
		return $dataEvt;	
	}
	
	static function evtSort($evt1, $evt2) {
		if (empty($evt1->AddParaMain->OrderNo) && empty($evt2->AddParaMain->OrderNo)) {
			return 0;
		}
		if (empty($evt1->AddParaMain->OrderNo)) {
			return 1;
		}
		if (empty($evt2->AddParaMain->OrderNo)) {
			return -1;
		}
		if ($evt1->AddParaMain->OrderNo < $evt2->AddParaMain->OrderNo) {
			return -1;
		}
		if ($evt1->AddParaMain->OrderNo > $evt2->AddParaMain->OrderNo) {
			return 1;
		}
		return 0;
	}
	
	static function all($state = NULL) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}
		
		$sql = 
		"SELECT a.id, a.created_date_time, a.title, a.description, a.event_state, a.max_registrations, a.additional_parameters " . 
		", b.not_confirmed_cnt " .
		", c.registered_cnt " .
		", d.waiting_list_cnt " .
		", e.deleted_cnt " .
		"FROM " . LGV_AS_DB::getEventTableName() . " a " . 
		"LEFT JOIN (SELECT event_id, SUM(registrations) AS not_confirmed_cnt FROM " . LGV_AS_DB::getRegistrationTableName() ." WHERE reg_state = " . intval(LGV_AS_BO::Registration_State_NotConfirmed) . " GROUP BY event_id) b ON a.id = b.event_id " .
		"LEFT JOIN (SELECT event_id, SUM(registrations) AS registered_cnt FROM " . LGV_AS_DB::getRegistrationTableName() ." WHERE reg_state = " . intval(LGV_AS_BO::Registration_State_Registered) . " GROUP BY event_id) c ON a.id = c.event_id " .
		"LEFT JOIN (SELECT event_id, SUM(registrations) AS waiting_list_cnt FROM " . LGV_AS_DB::getRegistrationTableName() ." WHERE reg_state = " . intval(LGV_AS_BO::Registration_State_WaitList) . " GROUP BY event_id) d ON a.id = d.event_id " .
		"LEFT JOIN (SELECT event_id, SUM(registrations) AS deleted_cnt FROM " . LGV_AS_DB::getRegistrationTableName() ." WHERE reg_state = " . intval(LGV_AS_BO::Registration_State_Deleted) . " GROUP BY event_id) e ON a.id = e.event_id " .
		"";
		if (is_int($state)) {
			$sql .= "WHERE event_state = " . intval($state);
		}

		//print_r($sql);
		
		// TODO:
		$wpdb->show_errors     = true;
        $wpdb->suppress_errors = false;
		$allEvents = $wpdb->get_results($sql);

		$res = array();
		foreach ( $allEvents as $evt ) 
		{
			$dataEvt = self::fromDb($evt);
			array_push($res, $dataEvt);
		}
		// Sort events by "$evt->AddParaMain->OrderNo"
		usort($res, 'LGV_AS_DB_Event::evtSort');
		return $res;
	}
	
	function checkAndDeactivateEvt() {
		if (!empty($this->AddParaMain->AutoDeactivateOn)) {
			
			$tz = get_option('timezone_string');
			if (empty($tz)) {
				$tz = get_option('gmt_offset');
			}
			//print_r($tz);
			
			// Check if this is a datetime;
			try {
			$dt = new DateTime($this->AddParaMain->AutoDeactivateOn, new DateTimeZone($tz));
			} catch (Exception $e) {
				// Ignore date/time if it cannot be parsed... the check is done during editing...
				return;
			}
			$current = new DateTime('NOW', new DateTimeZone($tz));
			
			if ($current >= $dt) {
				$this->deactivateEvt();

				$to = LGV_AS_BO::getEmptyEMailReceiver();
				$headers[] = 'From: ' . LGV_AS_BO::getEMailFrom();
				$bcc = LGV_AS_BO::getEMailBcc();
				if (!empty($bcc)) {
					$headers[] = 'Bcc: ' . $bcc;
				}

				$headers[] = 'Content-type: text/html';
				$subject = "Veranstaltung automatisch deaktiviert: " . $this->getTitle();
		
				$message = "<p>";
				$message .= "Die Veranstaltung <strong>" . $this->getTitle() . "</strong> ist abgelaufen.</p>";
				$message .= "<p>Abgelaufen am: " . $dt->format(DATE_ATOM) . "<br/>";
				$message .= "Aktuelle Zeit: " . $current->format(DATE_ATOM) . "<br/></p>";

				wp_mail( $to, $subject, $message, $headers);
			}
		}
	}
	function deactivateEvt() {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}

		$this->setState(LGV_AS_BO::Event_State_Inactive);
		$rows_affected = $wpdb->update( LGV_AS_DB::getEventTableName(), 
			array( 
				'event_state' => $this->getState(),
			),
			array( 'id' => $this->getId())
		);
		//print_r($rows_affected);
	}
	
	static function fromDb($evt) {
		//print_r($evt);
		$dataEvt = new LGV_AS_DB_Event();
		$dataEvt->setId($evt->id);
		$dataEvt->setCreatedDateTime($evt->created_date_time);
		$dataEvt->setTitle($evt->title);
		//$dataEvt->setDescription($evt->description);
		$dataEvt->setState(intval($evt->event_state));
		$dataEvt->setMaxRegistrations(intval($evt->max_registrations));
		$dataEvt->setAdditionalParameters($evt->additional_parameters);
		
		$dataEvt->notConfirmedCnt = intval($evt->not_confirmed_cnt);
		$dataEvt->registeredCnt = intval($evt->registered_cnt);
		$dataEvt->waitingListCnt = intval($evt->waiting_list_cnt);
		$dataEvt->deletedCnt = intval($evt->deleted_cnt);
		return $dataEvt;
	}
	
	static function delete($evt) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$rows_affected = $wpdb->delete( LGV_AS_DB::getEventTableName(), 
				array( 'id' => $evt->getId())
				);
			return $rows_affected;
		}
		return false;
	}
	
	public function store() {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}
		
		//print_r($this);

		if (empty($this->id)) {
			//echo "<h1>Store new record</h1>";
			// Create a new record
			 $rows_affected = $wpdb->insert( LGV_AS_DB::getEventTableName(), array( 
				'title' => $this->getTitle(),
				'created_date_time' => current_time('mysql'), 
				//'description' => $this->getDescription(), 
				'event_state' => $this->getState(),
				'max_registrations' => $this->getMaxRegistrations(),
				'additional_parameters' => $this->getAdditionalParameters()
				) );
			$id = $wpdb->insert_id;
		}
		else {
			// Update the existing record
			//echo "<h1>Store updated record</h1>";
			 $rows_affected = $wpdb->update( LGV_AS_DB::getEventTableName(), 
				array( 
					'title' => $this->getTitle(),
					'created_date_time' => $this->getCreatedDateTime(), 
					//'description' => $this->getDescription(), 
					'event_state' => $this->getState(),
					'max_registrations' => $this->getMaxRegistrations(),
					'additional_parameters' => $this->getAdditionalParameters()
				),
				array( 'id' => $this->getId())
				);
		}
	}
}
