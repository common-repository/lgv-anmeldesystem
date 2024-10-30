<?php

/**
 * Description of class-LGV_AS_DB_EventGroup
 *
 * @authors Jochen Kalmbach
 */

class LGV_AS_DB_EventGroup extends LGV_AS_BO_EventGroup {
	static function find($id) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}

		$sql = 
		"SELECT a.id, a.group_key, a.additional_parameters " .
		"FROM " . LGV_AS_DB::getEventGroupTableName() . " a "  .
		"WHERE id = " . intval($id) .
		"";
		//print_r($sql);
		
		$evt = $wpdb->get_row($sql);
		if (empty($evt))
			return NULL;
			
		$dataEvt = self::fromDb($evt);
		return $dataEvt;	
	}

	static function findByKey($key) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}

		$sql = 
		$sql = $wpdb->prepare(
			"SELECT a.id, a.group_key, a.additional_parameters " .
			"FROM " . LGV_AS_DB::getEventGroupTableName() . " a "  .
			"WHERE a.group_key = %s" .
			"",
			$key
		);
		
		$evt = $wpdb->get_row($sql);
		if (empty($evt))
			return NULL;
			
		$dataEvt = self::fromDb($evt);
		return $dataEvt;	
	}

	static function all() {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$wpdb->show_errors     = true;
			$wpdb->suppress_errors = false;
		}
		
		$sql = 
		"SELECT a.id, a.group_key, a.additional_parameters " . 
		"FROM " . LGV_AS_DB::getEventGroupTableName() . " a " . 
		"ORDER BY a.group_key " .
		"";

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
		return $res;
	}
	
	static function fromDb($evt) {
		//print_r($evt);
		$dataEvt = new LGV_AS_DB_EventGroup();
		$dataEvt->setId($evt->id);
		$dataEvt->setKey($evt->group_key);
		$dataEvt->setAdditionalParameters($evt->additional_parameters);
		return $dataEvt;
	}
	
	static function delete($evt) {
		global $wpdb;
		if (LGV_AS_Util::isAdmin()) {
			$rows_affected = $wpdb->delete( LGV_AS_DB::getEventGroupTableName(), 
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
			 $rows_affected = $wpdb->insert( LGV_AS_DB::getEventGroupTableName(), array( 
				'group_key' => $this->getKey(),
				'additional_parameters' => $this->getAdditionalParameters()
				) );
			$id = $wpdb->insert_id;
		}
		else {
			// Update the existing record
			//echo "<h1>Store updated record</h1>";
			 $rows_affected = $wpdb->update( LGV_AS_DB::getEventGroupTableName(), 
				array( 
					'group_key' => $this->getKey(),
					'additional_parameters' => $this->getAdditionalParameters()
				),
				array( 'id' => $this->getId())
				);
		}
	}
}
