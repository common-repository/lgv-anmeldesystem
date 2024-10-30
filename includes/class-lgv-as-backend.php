<?php

/**
 * Description of class-LGV_AS_Backend
 *
 * @authors Jochen Kalmbach
 */

 
class LGV_AS_Backend {

	static function setup() {
		add_action('admin_menu', 'LGV_AS_Backend::mt_add_pages');
		add_action('plugins_loaded', 'LGV_AS_Backend::plugins_loaded');
		
		LGV_AS_Util::setup();
	}
	
	static function mt_add_pages() {
		// Add a new top-level menu (ill-advised):
		add_menu_page('LGV-Anmeldungen', 'LGV-Anmeldungen', 'edit_pages', 'lgv-anmeldesystem/lgv-anmeldesystem.php', 'LGV_AS_Backend::display_options' );
	}
	
	
	static function plugins_loaded() {
		if (isset($_GET['page'])) {
			$reqUri = sanitize_text_field($_GET['page']);
			if ($reqUri == 'lgv-anmeldesystem/lgv-anmeldesystem.php') {
				if (isset($_GET['evt']) && isset($_GET['csv'])) {
					$evtId = intval($_GET['evt']);
					$csv = intval($_GET['csv']);
					if (intval($evtId) <> 0 && intval($csv) > 0) {
						header("Content-type: application/x-msdownload");
						header("Content-Disposition: attachment; filename=data.csv");
						header("Pragma: no-cache");
						header("Expires: 0");
						// Gibt es das Event?
						if ($evtId > 0) {
							$evt = LGV_AS_DB_Event::find($evtId);
							if (!empty($evt)) {
								LGV_AS_DB_Registration::outputAllToCsv($evtId, $evt);
								exit();
							}
						} else {
							echo pack("CCC",0xef,0xbb,0xbf);  // BOM
							$events = LGV_AS_DB_Event::all();
							foreach ( $events as $evt ) {
								echo $evt->getTitle();
								echo LGV_AS_Util::get_csv_encoded();  // add empty line
								LGV_AS_DB_Registration::outputAllToCsv($evt->getId(), $evt, false);
								echo LGV_AS_Util::get_csv_encoded();  // add empty line
							}
							exit();
						}
					}
				}
			}
		}
	}
	
	static function display_all_events() {
		echo "<h2>Übersicht der Veranstaltungen</h2>";
		
		//echo "<p><a href='?page=lgv-anmeldesystem/lgv-anmeldesystem.php&evt=-1&csv=1'>Download CSV (Excel)</a></p>";
		echo "<p><a href='?page=lgv-anmeldesystem/lgv-anmeldesystem.php&evt=-1&csv=1'>Download CSV (Excel)</a></p>";
		
		echo "<table border='1'>";

		echo "<tr>";
		echo "  <th>ID</th>";
		echo "  <th>Zustand</th>";
		echo "  <th>Gruppe</th>";
		echo "  <th>Title</th>";
		echo "  <th>Max. Anmeldungen</th>";
		echo "  <th>Registriert</th>";
		echo "  <th>Warteliste</th>";
		echo "  <th>Unbestätigt</th>";
		echo "  <th>Gelöscht</th>";
		echo "  <th>&nbsp;</th>";
		$colno = 10;
		if (LGV_AS_Util::isAdmin()) {
			echo "  <th>&nbsp;</th>";
			echo "  <th>&nbsp;</th>";
		}
		echo "</tr>";

		$events = LGV_AS_DB_Event::all();
		$activeGroup = null;
		foreach ( $events as $evt ) {
			
			// Check if we use groups:
			if (!empty($evt->AddParaMain->GroupKey)) {
				if ($evt->AddParaMain->GroupKey !== $activeGroup) {
					$evtGrp = LGV_AS_DB_EventGroup::findByKey($evt->AddParaMain->GroupKey);
					if (!empty($evtGrp)) {
						echo "<tr><td colspan='" . $colno . "'><strong>" . esc_html($evtGrp->AddPara->Title) . "</strong></td>";
						if (LGV_AS_Util::isAdmin()) {
							echo "  <td>&nbsp;</td>";
							echo "  <td>&nbsp;</td>";
						}
						"</tr>";
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
			
			echo "<tr>";
			echo "  <td>" . esc_html($evt->getId()) . "</td>";
			echo "  <td>";
			switch($evt->getState()) {
				case LGV_AS_BO::Event_State_Active:
					echo "<a href='" . LGV_AS_BO::getEventUrlFromId($evt->getId()) . "' target='_blank' >";
					echo "Aktiv";
					echo "</a>";
				break;
				default:
					echo "Inaktiv";
				break;
			}
			echo "  </td>";
			echo "  <td>" . esc_html($evt->AddParaMain->GroupKey) . "</td>";
			echo "  <td>" . esc_html($evt->getTitle()) . "</td>";
			if ($evt->getMaxRegistrations() > 0) {
				echo "  <td>" . esc_html($evt->getMaxRegistrations() . " (+" . $evt->AddParaMain->MaxWaitingList . ")") . "</td>";
			} else {
				echo "  <td>-</td>";
			}

			echo "  <td>" . esc_html($evt->getRegisteredCnt()) . "</td>";
			echo "  <td>" . esc_html($evt->getWaitingListCnt()) . "</td>";
			echo "  <td>" . esc_html($evt->getNotConfirmedCnt()) . "</td>";
			echo "  <td>" . esc_html($evt->getDeletedCnt()) . "</td>";
			
			echo "  <td>";
			
			echo '<form action="" method="post">';
			//echo '<form action="" method="GET">';
			wp_nonce_field("display_create_event"); 
			echo "  <input type='hidden' name='lgvas_action' value='display_create_event'></input>";
			echo "  <input type='hidden' name='lgvas_sub_action' value='view'></input>";
			echo "  <input type='hidden' name='lgvas_evtId' value='" .  esc_attr($evt->getId()) . "'></input>";
			//echo "  <input type='hidden' name='evtId' value='" .  intval($evt->getId()) . "'></input>";
			
			echo "  <input type='submit' name='lgvas_submit' value='Ansicht' />";
			echo "</form>";	
			echo "  </td>";
			
			if (LGV_AS_Util::isAdmin()) {
				echo "  <td>";
				echo '<form action="" method="post">';
				wp_nonce_field("display_create_event"); 
				echo "  <input type='hidden' name='lgvas_action' value='display_create_event'></input>";
				echo "  <input type='hidden' name='lgvas_sub_action' value='edit'></input>";
				echo "  <input type='hidden' name='lgvas_evtId' value='" .  esc_attr($evt->getId()) . "'></input>";
				echo "  <input type='submit' name='lgvas_submit' value='Bearbeiten' />";
				echo "</form>";	
				echo "  </td>";
				echo "  <td>";
				echo '<form action="" method="post">';
				wp_nonce_field("display_create_event"); 
				echo "  <input type='hidden' name='lgvas_action' value='display_create_event'></input>";
				echo "  <input type='hidden' name='lgvas_sub_action' value='copy'></input>";
				echo "  <input type='hidden' name='lgvas_evtId' value='" .  esc_attr($evt->getId()) . "'></input>";
				echo "  <input type='submit' name='lgvas_submit' value='Kopieren' />";
				echo "</form>";	
				echo "  </td>";
			}

			echo "</tr>";
		}
		
		echo "</table>";
				
		if (LGV_AS_Util::isAdmin()) {
			echo '<form action="" method="post">';
			wp_nonce_field("display_create_event"); 
			echo "  <input type='hidden' name='lgvas_action' value='display_create_event'></input>";
			echo "  <input type='submit' name='lgvas_submit' value='Neue Veranstaltung anlegen...' />";
			echo "</form>";	
		}
		echo "<hr/>";
	}
	
	static function display_all_event_groups() {
		echo "<h2>Veranstaltungs-Gruppen</h2>";
		
		echo "<table border='1'>";
        
		echo "<tr>";
		echo "  <th>ID</th>";
		echo "  <th>Kürzel</th>";
		echo "  <th>Titel</th>";
		if (LGV_AS_Util::isAdmin()) {
			echo "  <th>&nbsp;</th>";
		}
		echo "</tr>";

		$evtGroups = LGV_AS_DB_EventGroup::all();
		foreach ( $evtGroups as $evtGrp ) {
			echo "<tr>";
			echo "  <td>" . esc_html($evtGrp->getId()) . "</td>";
			echo "  <td>" . esc_html($evtGrp->getKey()) . "</td>";
			echo "  <td>" . esc_html($evtGrp->AddPara->Title) . "</td>";
			
			if (LGV_AS_Util::isAdmin()) {
				echo "  <td>";
				echo '<form action="" method="post">';
				wp_nonce_field("display_create_event_group"); 
				echo "  <input type='hidden' name='lgvas_action' value='display_create_event_group'></input>";
				echo "  <input type='hidden' name='lgvas_sub_action' value='edit'></input>";
				echo "  <input type='hidden' name='lgvas_evtGrpId' value='" .  esc_attr($evtGrp->getId()) . "'></input>";
				echo "  <input type='submit' name='lgvas_submit' value='Bearbeiten' />";
				echo "</form>";	
				echo "  </td>";
			}
			echo "</tr>";
		}
		
		echo "</table>";
		
		
		if (LGV_AS_Util::isAdmin()) {
			echo '<form action="" method="post">';
			wp_nonce_field("display_create_event_group"); 
			echo "  <input type='hidden' name='lgvas_action' value='display_create_event_group'></input>";
			echo "  <input type='submit' name='lgvas_submit' value='Neue Veranstaltungs-Gruppe anlegen...' />";
			echo "</form>";	
		}
		echo "<hr/>";
	}
	
	static function display_event($evtId) {
				$evt = LGV_AS_DB_Event::find($evtId);
				if (!empty($evt)) {
					// Gebe die Infos aus:
					echo "<h1>" . esc_html($evt->getTitle()) . "</h1>";
										
					if (!empty($evt->AddParaMain->GroupKey)) {
						$evtGrp = LGV_AS_DB_EventGroup::findByKey($evt->AddParaMain->GroupKey);
						if (!empty($evtGrp)) {
							echo "<h2>" . esc_html($evtGrp->AddPara->Title) . "</h2>";
						}
					}
										
					// get query data:
					$searchStartIdx = 0;
					$searchPageSize = 500;
					$seachNachname = "";
					$seachEMail = "";
					$searchPLZ = "";
					if (isset($_POST["lgvas_startIdx"])) {
						$searchStartIdx = intval($_POST["lgvas_startIdx"]);
					}
					if (isset($_POST["lgvas_lastName"])) {
						$seachNachname = sanitize_text_field($_POST["lgvas_lastName"]);
					}
					if (isset($_POST["lgvas_email"])) {
						$seachEMail = sanitize_email($_POST["lgvas_email"]);
					}
					if (isset($_POST["lgvas_zip"])) {
						$searchPLZ = sanitize_text_field($_POST["lgvas_zip"]);
					}
					
					$searchRegistered = 0;
					$searchWaitingList = 0;
					$searchNotConfirmed = 0;
					$searchDeleted = 0;
					if (isset($_POST["lgvas_state_registered"])) {
						$searchRegistered = intval($_POST["lgvas_state_registered"]);
					}
					if (isset($_POST["lgvas_state_waitinglist"])) {
						$searchWaitingList = sanitize_text_field($_POST["lgvas_state_waitinglist"]);
					}
					if (isset($_POST["lgvas_state_notConfimed"])) {
						$searchNotConfirmed = sanitize_text_field($_POST["lgvas_state_notConfimed"]);
					}
					if (isset($_POST["lgvas_state_deleted"])) {
						$searchDeleted = sanitize_text_field($_POST["lgvas_state_deleted"]);
					}
					
					// Wenn alle 4 deaktiviert sind, dann mache alle an...
					if ($searchRegistered == 0 && $searchWaitingList == 0 && $searchNotConfirmed == 0 && $searchDeleted == 0) {
						$searchRegistered = 1;
						$searchWaitingList = 1;
						$searchNotConfirmed = 1;
						$searchDeleted = 1;
					}
					
					
					// Force Download from backend:
					// http://wordpress.stackexchange.com/questions/3480/how-can-i-force-a-file-download-in-the-wordpress-backend
					// Alternativ: http://codex.wordpress.org/AJAX_in_Plugins
					
					echo '<form action="" method="post">';
					echo "<h3>Filter</h3>";
					wp_nonce_field("display_create_event"); 
					echo "  <input type='hidden' name='lgvas_action' value='display_create_event'></input>";
					echo "  <input type='hidden' name='lgvas_sub_action' value='view'></input>";
					echo "  <input type='hidden' name='lgvas_evtId' value='" .  esc_attr($evt->getId()) . "'></input>";
					echo "  <input type='hidden' name='lgvas_startIdx' value='" .  esc_attr($searchStartIdx) . "'></input>";
					echo "<table>";
					echo "<tr>";
					echo "<td><label for='lgvas_lastName'>Nachname</label></td>";
					echo "<td><input type='text' id='lgvas_lastName' name='lgvas_lastName' value='" . esc_attr($seachNachname) . "' /></td>";
					echo "</tr>";
					echo "<tr>";
					echo "<td><label for='lgvas_email'>E-Mail</label></td>";
					echo "<td><input type='text' id='lgvas_email' name='lgvas_email' value='" . esc_attr($seachEMail) . "'/></td>";
					echo "</tr>";
					echo "<tr>";
					echo "<td><label for='lgvas_zip'>PLZ</label></td>";
					echo "<td><input type='text' id='lgvas_zip' name='lgvas_zip' value='" . esc_attr($searchPLZ) . "'/></td>";
					echo "</tr>";
					
					echo "<tr>";
					echo "<td><label for='lgvas_state'>Zustand</label></td>";
					echo "<td>"; 
					
					// TODO: checked passend übernehmen!
					echo "<input type='checkbox' value='1' id='lgvas_state_registered' name='lgvas_state_registered' " . LGV_AS_Util::get_checked($searchRegistered) . " >Registrierte</input>";
					echo "<br/>";
					echo "<input type='checkbox' value='1' id='lgvas_state_waitinglist' name='lgvas_state_waitinglist' " . LGV_AS_Util::get_checked($searchWaitingList) . " >Warteliste</input>";
					echo "<br/>";
					echo "<input type='checkbox' value='1' id='lgvas_state_notConfimed' name='lgvas_state_notConfimed' " . LGV_AS_Util::get_checked($searchNotConfirmed) . " >Unbestätigt</input>";
					echo "<br/>";
					echo "<input type='checkbox' value='1' id='lgvas_state_deleted' name='lgvas_state_deleted' " . LGV_AS_Util::get_checked($searchDeleted) . " >Gelöscht</input>";
					echo "</td>";
					echo "</tr>";
					
					echo "</table>";
					echo "<input type='submit' id='lgvas_submit' value='Suchen' />";
					echo "<a href='?page=lgv-anmeldesystem/lgv-anmeldesystem.php&evt=" . intval($evt->getId()) . "&csv=1'>Download CSV (Excel)</a>";
					
					//echo "<input type='submit' id='lgvas_submit' value='Excel-Export' />";
					echo "</form>";
					
					echo "<hr/>";
					
					echo "<table>";

					echo "<tr>";
					echo "  <th>ID</th>";
					echo "  <th>Zustand</th>";
					echo "  <th>Nachname</th>";
					echo "  <th>Vorname</th>";
					echo "  <th>Anzahl</th>";
					echo "  <th>E-Mail</th>";
					echo "  <th>PLZ/Ort</th>";
					if ($evt->AddParaMain->OptPhone == 0) {
						echo "  <th>Telefon</th>";
					}
					echo "  <th>&nbsp;</th>";
					echo "</tr>";

					// Ermittle die Anzahl der Regisrierungs-Einträge (für das Paging)
					$entriesCnt = LGV_AS_DB_Registration::countEntries($evt->getId());

					
					$regs = LGV_AS_DB_Registration::all($evt->getId(), $searchStartIdx, $searchPageSize,
						$seachNachname, $seachEMail, $searchPLZ,
						$searchRegistered,
						$searchWaitingList,
						$searchNotConfirmed,
						$searchDeleted
					);
					foreach ( $regs as $reg ) {
						echo "<tr>";
						echo "  <td>" . esc_html($reg->getId()) . "</td>";
						echo "  <td>" . esc_html(LGV_AS_BO::getRegStateName($reg->getState())) . "  </td>";
						echo "  <td>" . esc_html($reg->getLastName()) . "</td>";
						echo "  <td>" . esc_html($reg->getFirstName()) . "</td>";

						echo "  <td>" . esc_html($reg->getRegistrations()) . "</td>";
						echo "  <td>" . esc_html($reg->getEmail()) . "</td>";
						echo "  <td>" . esc_html($reg->getZipCode()) . " " . esc_html($reg->getCity()) . "</td>";
						if ($evt->AddParaMain->OptPhone == 0) {
							echo "  <td>" . esc_html($reg->getPhone()) . "</td>";
						}
						echo "  <td>";
						//print_r($reg);
						echo "<a href='" . LGV_AS_BO::getEditUrlFromId($reg->getId()) . "' target='_blank'>Bearbeiten</a>";
						//echo '<form action="" method="post">';
						//wp_nonce_field("display_create_event"); 
						//echo "  <input type='hidden' name='lgvas_action' value='display_create_event'></input>";
						//echo "  <input type='hidden' name='lgvas_sub_action' value='edit'></input>";
						//echo "  <input type='hidden' name='lgvas_evtId' value='" .  esc_attr($evt->getId()) . "'></input>";
						//echo "  <input type='submit' name='lgvas_submit' value='Bearbeiten' />";
						//echo "</form>";	
						echo "  </td>";

						echo "</tr>";
					}
					
					echo "</table>";
					
					$maxThisPage = min($searchPageSize, $entriesCnt);
					
					echo "<strong>" . ($searchStartIdx+1) . " - " . ($searchStartIdx + $maxThisPage) . " / " . $entriesCnt . "</strong>";

					// TODO:
					if ($searchStartIdx > 0) {
						// Blende Anfang Button ein:
			}
		}
			else {
				wp_die("Ungültige event id!");
			}
	}

	static function display_create_event() {
		// Check if we were called to display or create a new event...
		$subAction = "";
		if ( isset($_POST['lgvas_sub_action'])) {
			$subAction = sanitize_text_field($_POST['lgvas_sub_action']);
		}
		
		if ($subAction == "view") {
			$evtId = intval($_POST['lgvas_evtId']);
			if (!empty($evtId)) {
				self::display_event($evtId);
			}
			return;
		}
		
		$headline = "Neue Veranstaltung anlegen";
		$buttonText = "Veranstaltung anlegen";
		$savedText = "Veranstaltung erfolgreich angelegt.";
		
		// empty default
		$evt = new LGV_AS_DB_Event();

		$evtId = 0;
		if (isset($_POST['lgvas_evtId'])) {
			$evtId = intval($_POST['lgvas_evtId']);
		}
		if (!empty($evtId)) {
			$evtOld = LGV_AS_DB_Event::find($evtId);
			if (!empty($evtOld)) {
				$evt = $evtOld;
			}
		}
		
		$evtId = $evt->getId();
		if ( !empty($evtId) ) {
			if ($subAction == "copy") {
				// Copy event
				$evt = $evt->copy();
				$evtId = $evt->getId();
			} else {
				$headline = "Veranstaltung bearbeiten";
				$buttonText = "Veranstaltung ändern";
				$savedText = "Veranstaltung erfolgreich geändert.";
			}
		}
		
		if ($subAction == 'doDelete') {
			$regAffected = LGV_AS_DB_Registration::deleteAll($evt);
			$evtAffected = LGV_AS_DB_Event::delete($evt);
			echo "Veranstaltung wurde gelöscht!";
			echo "Reg: " . $regAffected . ", Evt: " . $evtAffected;
			return true;
		}
		
		$validationResult  = array(); 
		if ($subAction == 'doCreate') {
			$evt->fillFromPostData();
			
			$validationResult = $evt->validate();
			if (empty($validationResult)) {
				$evt->store();

				echo "<hr/>";
				echo $savedText;
				return true;
			}
		}
	
		echo "<h2>" . $headline . "</h2>";
		
		echo '<form action="" method="post">';
		wp_nonce_field("display_create_event"); 
		echo "  <input type='hidden' name='lgvas_action' value='display_create_event'></input>";
		echo "  <input type='hidden' name='lgvas_sub_action' value='doCreate'></input>";

		echo "  <input type='hidden' name='lgvas_evtId' value='" .  esc_attr($evt->getId()) . "'></input>";
		
		echo "<table>";
		
		echo "<tr>";
		echo "  <td><label for='lgvas_title'>Titel</label>" . LGV_AS_Util::get_validation_error($validationResult, "title") . "</td>";
		echo "  <td><input type='text' id='lgvas_title' name='lgvas_title' value='". esc_attr($evt->getTitle()) . "' style='width: 600px;' /></td>";
		echo "</tr>";
		
		//echo "<tr>";
		//echo "  <td><label for='lgvas_description'>Beschreibung</label>" . LGV_AS_Util::get_validation_error($validationResult, "description") . "</td>";
		//echo "  <td><textarea id='lgvas_description' name='lgvas_description'>". esc_attr($evt->getDescription()) . "</textarea>";
		//echo "</tr>";
		
		echo "<tr>";
		echo "  <td><label for='lgvas_maxRegistrations'>Maximale Anmeldungen</label>" . LGV_AS_Util::get_validation_error($validationResult, "maxRegistrations") . "</td>";
		echo "  <td><input type='text' id='lgvas_maxRegistrations' name='lgvas_maxRegistrations' value='". esc_attr($evt->getMaxRegistrations()) . "' />";
		echo "</tr>";

		echo "<tr>";
		echo "  <td><label for='lgvas_additionalParameters'>Zusatzinfos (JSON)</label>" . LGV_AS_Util::get_validation_error($validationResult, "additionalParameters") . "</td>";
		echo "  <td><textarea rows='35' cols='80' id='lgvas_additionalParameters' name='lgvas_additionalParameters'>". esc_html($evt->getAdditionalParameters()) . "</textarea>";
		echo "</tr>";
		
		if ( !empty($evtId) ) {
			// Zeige den Zustand an.... erlaube auch diesen zu ändern...
			echo "<tr>";
			echo "  <td><label for='lgvas_state'>Zustand</label>" . LGV_AS_Util::get_validation_error($validationResult, "state") . "</td>";
			echo "  <td><select id='lgvas_state' name='lgvas_state' >";
			$selectedVal = "";
			if ($evt->getState() == LGV_AS_BO::Event_State_Inactive) {
				$selectedVal = " selected";
			}
			echo "<option value='" . LGV_AS_BO::Event_State_Inactive . "'" . $selectedVal . ">Inaktiv</option>";
			$selectedVal = "";
			if ($evt->getState() == LGV_AS_BO::Event_State_Active) {
				$selectedVal = " selected";
			}
			echo "<option value='" . LGV_AS_BO::Event_State_Active . "'" . $selectedVal . ">Aktiv</option>";
			echo "  </select></td>";
			echo "</tr>";
		}

		echo "</table>";
		
		echo "<input type='submit' id='lgvas_submit' value='" . $buttonText . "' />";
		
		echo "</form>";	
		
		if ( !empty($evtId) ) {
			if (LGV_AS_Util::isAdmin()) {
				echo '<form action="" method="post">';
				wp_nonce_field("display_create_event"); 
				echo "  <input type='hidden' name='lgvas_action' value='display_create_event'></input>";
				echo "  <input type='hidden' name='lgvas_sub_action' value='doDelete'></input>";		
				echo "  <input type='hidden' name='lgvas_evtId' value='" .  esc_attr($evt->getId()) . "'></input>";	
				if ($evt->getState() == LGV_AS_BO::Event_State_Inactive) {
					echo "<input style='color:red' type='submit' name='lgvas_submit_del' value='Veranstaltung LÖSCHEN!' onclick=\"return confirm('Wollen Sie diese Veranstaltung wirklich löschen?')\" />";
				}
				echo "</form>";	
			}
		}
		
		return false;  // do not continue
	}
	
	static function display_create_event_group() {
		// Check if we were called to display or create a new event group...
		$subAction = "";
		if ( isset($_POST['lgvas_sub_action'])) {
			$subAction = sanitize_text_field($_POST['lgvas_sub_action']);
		}
		
		$headline = "Neue Veranstaltungs-Gruppe anlegen";
		$buttonText = "Veranstaltungs-Gruppe anlegen";
		$savedText = "Veranstaltungs-Gruppe erfolgreich angelegt.";
		
		// empty default
		$evtGrp = new LGV_AS_DB_EventGroup();

		// Create or edit?
		$evtGrpId = 0;
		if (isset($_POST['lgvas_evtGrpId'])) {
			$evtGrpId = intval($_POST['lgvas_evtGrpId']);
		}
		if (!empty($evtGrpId)) {
			$evtOld = LGV_AS_DB_EventGroup::find($evtGrpId);
			if (!empty($evtOld)) {
				$evtGrp = $evtOld;
			}
		}
		
		$evtGrpId = $evtGrp->getId();
		if ( !empty($evtGrpId) ) {
			$headline = "Veranstaltungs-Gruppe bearbeiten";
			$buttonText = "Veranstaltungs-Gruppe ändern";
			$savedText = "Veranstaltungs-Gruppe erfolgreich geändert.";
		}
		
		if ($subAction == 'doDelete') {
			$evtAffected = LGV_AS_DB_EventGroup::delete($evtGrp);
			echo "Veranstaltungs-Gruppe wurde gelöscht!";
			return true;
		}
		
		$validationResult  = array(); 
		if ($subAction == 'doCreate') {
			$evtGrp->fillFromPostData();
			
			$validationResult = $evtGrp->validate();
			if (empty($validationResult)) {
				$evtGrp->store();

				echo "<hr/>";
				echo $savedText;
				return true;
			}
		}
	
		echo "<h2>" . $headline . "</h2>";
		
		echo '<form action="" method="post">';
		wp_nonce_field("display_create_event_group"); 
		echo "  <input type='hidden' name='lgvas_action' value='display_create_event_group'></input>";
		echo "  <input type='hidden' name='lgvas_sub_action' value='doCreate'></input>";

		echo "  <input type='hidden' name='lgvas_evtGrpId' value='" .  esc_attr($evtGrp->getId()) . "'></input>";
		
		echo "<table>";
		
		echo "<tr>";
		echo "  <td><label for='lgvas_key'>Gruppen-Key</label>" . LGV_AS_Util::get_validation_error($validationResult, "key") . "</td>";
		echo "  <td><input type='text' id='lgvas_key' name='lgvas_key' value='". esc_attr($evtGrp->getKey()) . "' style='width: 600px;' /></td>";
		echo "</tr>";
		
		echo "<tr>";
		echo "  <td><label for='lgvas_AddPara_Title'>Titel</label>" . LGV_AS_Util::get_validation_error($validationResult, "AddPara_Title") . "</td>";
		echo "  <td><input type='text' id='lgvas_AddPara_Title' name='lgvas_AddPara_Title' value='". esc_attr($evtGrp->AddPara->Title) . "' style='width: 600px;' /></td>";
		echo "</tr>";

		//echo "<tr>";
		//echo "  <td><label for='lgvas_additionalParameters'>Zusatzinfos (JSON)</label>" . LGV_AS_Util::get_validation_error($validationResult, "additionalParameters") . "</td>";
		//echo "  <td><textarea rows='35' cols='80' id='lgvas_additionalParameters' name='lgvas_additionalParameters'>". esc_html($evtGrp->getAdditionalParameters()) . "</textarea>";
		//echo "</tr>";
		
		echo "</table>";
		
		echo "<input type='submit' id='lgvas_submit' value='" . $buttonText . "' />";
		
		echo "</form>";	
		
		if ( !empty($evtGrpId) ) {
			if (LGV_AS_Util::isAdmin()) {
				echo '<form action="" method="post">';
				wp_nonce_field("display_create_event_group"); 
				echo "  <input type='hidden' name='lgvas_action' value='display_create_event_group'></input>";
				echo "  <input type='hidden' name='lgvas_sub_action' value='doDelete'></input>";		
				echo "  <input type='hidden' name='lgvas_evtGrpId' value='" .  esc_attr($evtGrp->getId()) . "'></input>";	
				//if ($evt->getState() == LGV_AS_BO::Event_State_Inactive) {
					echo "<input style='color:red' type='submit' name='lgvas_submit_del' value='Veranstaltungs-Gruppe LÖSCHEN!' onclick=\"return confirm('Wollen Sie diese Veranstaltungs-Gruppe wirklich löschen?')\" />";
				//}
				echo "</form>";	
			}
		}
		
		return false;  // do not continue
	}	
	
	static function display_global_settings() {
		if (LGV_AS_Util::isAdmin()) {
			echo '<form action="" method="post">';
			wp_nonce_field("edit_global_settings"); 
			echo "  <input type='hidden' name='lgvas_action' value='edit_global_settings'></input>";
			echo "  <input type='submit' name='lgvas_submit' value='Globale Einstellungen bearbeiten' />";
			echo "</form>";	
			echo "<hr/>";
		}
	}
	static function edit_global_settings() {
		// Check if we were called to display or create a new event group...
		$subAction = "";
		if ( isset($_POST['lgvas_sub_action'])) {
			$subAction = sanitize_text_field($_POST['lgvas_sub_action']);
		}

		if ($subAction == 'doModify') {
			$opt = new LGV_AS_GlobalOptions();
			$opt->FromEmail = trim(sanitize_text_field($_POST['lgvas_FromEmail']));
			$opt->FromName = trim(sanitize_text_field($_POST['lgvas_FromName']));
			$opt->EmptyEMailReceiver = trim(sanitize_text_field($_POST['lgvas_EmptyEMailReceiver']));
			$opt->WelcomeMsg = trim(sanitize_textarea_field($_POST['lgvas_WelcomeMsg']));
			$opt->BccEmail = trim(sanitize_text_field($_POST['lgvas_BccEmail']));
			$opt->PaymentInfos->fillFromBackendPostData(stripcslashes(sanitize_textarea_field($_POST['lgvas_PaymentInfos'])));
			$opt->PageName = trim(sanitize_text_field($_POST['lgvas_PageName']));
			
			LGV_AS_BO::setOptions($opt);

			echo "<hr/>";
			echo "Globale Einstellungen gespeichert";
			return true;
		}
		
		echo '<form action="" method="post">';
		wp_nonce_field("edit_global_settings"); 
		echo "  <input type='hidden' name='lgvas_action' value='edit_global_settings'></input>";
		echo "  <input type='hidden' name='lgvas_sub_action' value='doModify'></input>";
		
		//echo "  <p>";
		//echo "    <textarea rows='35' cols='80' id='lgvas_globalParam' name='lgvas_globalParam'>". esc_html(json_encode(LGV_AS_BO::getOptions(), JSON_PRETTY_PRINT)) . "</textarea>";
		//echo "  </p>";
		
		$opt = LGV_AS_BO::getOptions();
		echo "<table>";
		echo "<tr>";
		echo "  <td><label for='lgvas_FromEmail'>Absender E-Mail Adresse</label></td>";
		echo "  <td><input type='text' id='lgvas_FromEmail' name='lgvas_FromEmail' value='". esc_attr($opt->FromEmail) . "' style='width: 600px;' /></td>";
		echo "</tr>";
		echo "<tr>";
		echo "  <td><label for='lgvas_FromName'>Absender Name</label></td>";
		echo "  <td><input type='text' id='lgvas_FromName' name='lgvas_FromName' value='". esc_attr($opt->FromName) . "' style='width: 600px;' /></td>";
		echo "</tr>";
		echo "<tr>";
		echo "  <td><label for='lgvas_EmptyEMailReceiver'>Empfänger für Anmeldungen ohne E-Mail</label></td>";
		echo "  <td><input type='text' id='lgvas_EmptyEMailReceiver' name='lgvas_EmptyEMailReceiver' value='". esc_attr($opt->EmptyEMailReceiver) . "' style='width: 600px;' /></td>";
		echo "</tr>";
		echo "<tr>";
		echo "  <td><label for='lgvas_BccEmail'>BCC E-Mail Empfänger</label></td>";
		echo "  <td><input type='text' id='lgvas_BccEmail' name='lgvas_BccEmail' value='". esc_attr($opt->BccEmail) . "' style='width: 600px;' /></td>";
		echo "</tr>";
		echo "<tr>";
		echo "  <td><label for='lgvas_WelcomeMsg'>Willkommensnachricht</label></td>";
		echo "  <td><textarea rows='8' style='width: 600px;' id='lgvas_WelcomeMsg' name='lgvas_WelcomeMsg'>". esc_attr($opt->WelcomeMsg) . "</textarea>";
		echo "</tr>";
		echo "<tr>";
		echo "  <td><label for='lgvas_PaymentInfos'>Payment Infos</label></td>";
		echo "  <td><textarea rows='12' style='width: 600px;' id='lgvas_PaymentInfos' name='lgvas_PaymentInfos'>". esc_html(json_encode($opt->PaymentInfos, JSON_PRETTY_PRINT)) . "</textarea>";
		echo "</tr>";
		echo "<tr>";
		echo "  <td><label for='lgvas_PageName'>Pfad Zur Seite (wenn nicht Hauptseite)</label></td>";
		echo "  <td><input type='text' id='lgvas_PageName' name='lgvas_PageName' value='". esc_attr($opt->PageName) . "' style='width: 600px;' /></td>";
		echo "</tr>";
		echo "</table>";
		echo "<input type='submit' id='lgvas_submit' value='Ändern' />";
		
		echo "</form>";	
	}
	
	static function display_options() {

		//must check that the user has the required capability 
		if (!LGV_AS_Util::isEditorOrAdmin())
		{
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		$action = NULL;
		if ( isset($_POST['lgvas_action'])) {
			$action = sanitize_text_field($_POST['lgvas_action']);
		}
		
		// if this fails, check_admin_referer() will automatically print a "failed" page and die.
		if ( ! empty( $_POST ) && !check_admin_referer($action) ) {
			wp_die("Not a valid post request");
		}
		
		echo "<div class='lgvas_links'><p><a href='?page=lgv-anmeldesystem/lgv-anmeldesystem.php')'>Übersicht</a></p></div>";
		
		
		$continue = true;
		if ($action == 'display_create_event') {
			$continue = self::display_create_event();
		}

		if ($action == 'display_create_event_group') {
			$continue = self::display_create_event_group();
		}
		
		if ($action == 'edit_global_settings') {
			$continue = self::edit_global_settings();
		}
		
		if ($continue) {
			self::display_all_events();
			self::display_all_event_groups();
			self::display_global_settings();
		}
	}	

}  // end class LGV_AS_Backend
