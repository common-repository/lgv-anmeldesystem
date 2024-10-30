<?php

/**
 * Description of class-LGV_AS_Frontend
 *
 * @authors Jochen Kalmbach
 */

class LGV_AS_Frontend {

	static function setup() {
		add_shortcode('lgv_anmeldesystem', 'LGV_AS_Frontend::process_short_code', 1);
		add_shortcode('lgv-anmeldesystem', 'LGV_AS_Frontend::process_short_code', 1);

		add_action( 'wp_enqueue_scripts', 'LGV_AS_Frontend::add_scripts' );

		// Remove some unneeded header infos:
		remove_action( 'wp_head', 'feed_links_extra', 3 ); // Display the links to the extra feeds such as category feeds
		remove_action( 'wp_head', 'feed_links', 2 ); // Display the links to the general feeds: Post and Comment Feed
		remove_action( 'wp_head', 'rsd_link' ); // Display the link to the Really Simple Discovery service endpoint, EditURI link
		remove_action( 'wp_head', 'wlwmanifest_link' ); // Display the link to the Windows Live Writer manifest file.
		remove_action( 'wp_head', 'index_rel_link' ); // index link
		remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 ); // prev link
		remove_action( 'wp_head', 'start_post_rel_link', 10, 0 ); // start link
		remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 ); // Display relational links for the posts adjacent to the current post.
		remove_action( 'wp_head', 'wp_generator' ); // Display the XHTML generator that is generated on the wp_head hook, WP version		
	}
	
	static function add_scripts() {
		wp_enqueue_style(  'lgv-anmeldesystem-style',  plugins_url('lgv-anmeldesystem/includes/styles.css') );
		wp_enqueue_script( 'lgv-anmeldesystem-script', plugins_url('lgv-anmeldesystem/includes/scripts.js') );
	}
	
	static function _check_for_free_places_and_inform_waiting_list($evtId) {
	
		// 2015-01-08: Vorerst wird die automatische Nachruecken deaktiviert, da man lieber selber das Nachruecken Steuerung und priorisieren will!
		return;
	
		/*$cnt = 50;  // so viele, wie pro registrierung maximal wegfallen können...
		do {
			// Frage die Daten jedes Mal neu ab! Sonst werden plötzlich alle als Unbestätigt gemeldet!
			$evt = LGV_AS_DB_Event::find($evtId);
			if (empty($evt)) {
				return;
			}
		
			if ($evt->getState() != LGV_AS_BO::Event_State_Active) {
				return;
			}

			if ($cnt <= 0) {
				return;  // breche ab, falls was schief geht...
			}
			$cnt--;
			// Gibt es immer noch freie Plätze?
			$registered = $evt->getRegisteredCnt();
			$unconfirmed = $evt->getNotConfirmedCnt();
			$maxCount = $evt->getMaxRegistrations();
			$freeCount = $maxCount - ($registered+$unconfirmed);
			if (($evt->getMaxRegistrations() > 0) && ($freeCount > 0) ) {
				// Es gibt wieder freie Plätze!
				// Finde den ersten auf der Warteliste
				$wl = LGV_AS_DB_Registration::findFirstFromWaitingList($evt->getId());
				if (empty($wl)) {
					return;
				}
				// Schaue nach, ob auch genügend Plätze für den nächsten Warteliste-Eintrag frei sind:
				if ($wl->getRegistrations() > $freeCount) {
					return;  // noch nicht genügend frei, also breche hier ab...
				}
				
				// Wir haben jemanden gefunden
				// Setze jetzt den Zustand "NotConfirmed":
				$wl->setState(LGV_AS_BO::Registration_State_NotConfirmed);
				// Logge diese aktion mit
				$log = $wl->getPara("log");
				$log .= current_time('mysql') . ": Automatisches Nachrücken von Warteliste (nach Unbestätigt)" . "\r\n";
				$wl->setPara("log", $log);
				
				// Speichere und sende Mail...
				$wl->store(true, true);
			}
			else {
				return;
			}
		} while(true);*/
	}
	
	static function _check_and_display_email_request_form() {
		$string = "";
		
		// Check if we have a POST from the request:
		$email = "";
		$wasPost = false;
		if (isset($_POST['req-mail-addr'])) {
			$email = $_POST['req-mail-addr'];
			$wasPost = true;
		}
		$email = trim($email);
		if (!empty($email)) {
			// Now try to find the events from the e-mail:
			if (LGV_AS_BO_Registration::sendNotifyFromEmail($email)) {
				$string .= "<p>Ihre Daten wurden nochmals an Ihre E-Mail Adresse gesendet.</p>";
				return $string;
			}
			else {
				$string .= "<h3 class='lgvas_validation_error'>Wir konnten Ihre E-Mail Adresse nicht finden!</h3>";
				$wasPost = false;
			}
		}
	
		if (isset($_GET['req-mail'])) {
			if (intval($_GET['req-mail']) > 0) {
				$string .= '<h2>Daten nochmals per E-Mail anfordern</h2>';
				$string .= '<form action="" method="post">';

				$string .= "<p><label for='req-mail-addr'>Ihre E-Mail Adresse:</label>";
				$string .= "<input type='text' id='req-mail-addr' name='req-mail-addr' />";
				if ($wasPost) {
					$string .= "<div class='lgvas_validation_error'>Bitte geben Sie eine gültige E-Mail Adresse ein</div>";
				}
				$string .= '</p>';

				$string .= "<input type='submit' name='lgvas_submit' value='Daten per E-Mail anfordern' />";
			
				$string .= '</form>';
			
				return $string;
			}
		}

		return $string;
	}
	
	static function _display_all_events() {
		$string = "";
		$string .= self::_check_and_display_email_request_form();
		if (!empty($string)) {
			return $string;
		}
	
	
		$opt = LGV_AS_BO::getOptions();
		
		$string .= LGV_AS_Util::getText($opt->WelcomeMsg);
	
		$events = LGV_AS_DB_Event::all(LGV_AS_BO::Event_State_Active);
		
		$hiddenEvents = false;
		$activeGroup = null;
		foreach ( $events as $evt ) {

			$evt->checkAndDeactivateEvt($evt);
			
			if ($evt->getState() != LGV_AS_BO::Event_State_Active) {
				continue;
			}
		
			if (empty($evt->AddParaMain->UrlId) == false) {
				$hiddenEvents = true;
			}
			
			if ($evt->AddParaMain->OnlyLoggedInInOverview) {
				if (is_user_logged_in() == false) {
					continue;
				}
			}
			
			// Check if we use groups:
			if (!empty($evt->AddParaMain->GroupKey)) {
				if ($evt->AddParaMain->GroupKey !== $activeGroup) {
					$evtGrp = LGV_AS_DB_EventGroup::findByKey($evt->AddParaMain->GroupKey);
					if (!empty($evtGrp)) {
						$string .= "<h2>" . esc_html($evtGrp->AddPara->Title) . "</h2>";
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
			$string .= "<" .$vh . "><a href='?lgvas_evtid=" . esc_html($evt->getId()) .  "'>";
			//$string .= apply_filters( 'the_content', $evt->getTitle() );
			$string .= esc_html($evt->getTitle());
			$string .= "</a>";
			
			$maxCount = $evt->getMaxRegistrations();
			if ($maxCount > 0) {
				$registered = $evt->getRegisteredCnt();
				$unconfirmed = $evt->getNotConfirmedCnt();
				$waitingList = $evt->getWaitingListCnt();
				$maxMaxCount = $maxCount + $evt->AddParaMain->MaxWaitingList;
				
				$overfullCnt = ($registered + $unconfirmed + $waitingList) - $maxCount;
				$overfullPercent = 0;
				if ($evt->AddParaMain->MaxWaitingList > 0) {
					$overfullPercent = ($overfullCnt*100) / $evt->AddParaMain->MaxWaitingList;
				}
				
				$color = "green";
				$percent = (($registered + $unconfirmed + $waitingList)*100) / $maxCount;
				$hundredPercentWidth = 300;
				$waitingListPercentWidth = ($evt->AddParaMain->MaxWaitingList / $maxCount) * $hundredPercentWidth;
				if ($percent >= 100) {
					$color = "orange";
					$percent = 100;
				}
				else {
					// Es gibt noch keine Warteliste, also zeige diese gar nicht an...
					$waitingListPercentWidth = 0;
				}
				$widthVal = ($percent / 100) * $hundredPercentWidth;
				$widthWaitingListVal = ($overfullPercent / 100) * $waitingListPercentWidth;
				$string .= "<br/>";

				$string .= "<div style='border: 1px solid; width: " . intval($hundredPercentWidth + $waitingListPercentWidth) . "px; height: 3px; position: relative; '>";
				$string .= "  <div style='position: absolute; background-color: " . $color . "; width: ".intval($widthVal)."px; height: 3px'></div>";
				if ($waitingListPercentWidth > 0) {
					$string .= "  <div style='position: absolute; background-color: red; left: ".intval($hundredPercentWidth)."px; width: ".intval($widthWaitingListVal)."px; height: 3px;'></div>";
				}
				$string .= "</div>";
				
				if ($maxMaxCount <= ($registered+$unconfirmed+$waitingList)) {
					$string .= " <div class='lgvas_event_full'>(Ausgebucht)</div>";
				}
				else if ($maxCount <= ($registered+$unconfirmed+$waitingList)) {
					$string .= " <div class='lgvas_event_waitinglist'>(Warteliste)</div>";
				}
			}  // $maxCount > 0
			
			$string .="</" . $vh . ">";
		}
		
		// Daten vergessen... nochmals anfordern!?
		$string .= "<div>Sie haben sich schon angemeldet, aber Ihre Daten verloren?<br/>";
		$string .= "<a href='?req-mail=1'>Hier können Sie Ihre Daten nochmals per E-Mail anfordern.</a></div>";

		if ($hiddenEvents) {
			$string .= "<hr/>";
			$string .= "<form action='' method='POST'>";
			$string .= "Veranstaltungscode eingeben: ";
			$string .= "<input type='text' name='evt' />";
			$string .= "<input type='submit' value='Gehe zu' />";
			$string .= "</form>";
		}
		
		return $string;	
	}
	
	static function process_short_code($atts) {
		$string = "";
		
		$savedText = "Ihre Anmeldung wurde gespeichert. Sie bekommen nun eine Bestätigungsmail an Ihre E-Mail Adresse.";
		$saveText = "Anmeldung absenden";
		$aenderung = false;
		
		extract( shortcode_atts( array(
			'evtid'		 => '0',
			), $atts ) );
				
		$evtId = intval($evtid);
		
		if ($_SERVER['REQUEST_METHOD'] == "GET") {
			if ( isset($_GET['lgvas_evtid'])) {
				$evtId = intval($_GET['lgvas_evtid']);
			} elseif ( isset($_GET['evt'])) {
				$evtIdStr = trim($_GET['evt']);
				$events = LGV_AS_DB_Event::all(LGV_AS_BO::Event_State_Active);
				foreach ( $events as $evt ) {
					if (empty($evt->AddParaMain->UrlId) == false) {
						if (strcasecmp($evtIdStr, $evt->AddParaMain->UrlId) == 0) {
							$evtId = $evt->getId();
							break;
						}
					}
				}
			}
		} else {
			if ( isset($_POST['lgvas_evtid'])) {
				$evtId = intval($_POST['lgvas_evtid']);
			} elseif ( isset($_POST['evt'])) {
				$evtIdStr = trim($_POST['evt']);
				$events = LGV_AS_DB_Event::all(LGV_AS_BO::Event_State_Active);
				foreach ( $events as $evt ) {
					if (empty($evt->AddParaMain->UrlId) == false) {
						if (strcasecmp($evtIdStr, $evt->AddParaMain->UrlId) == 0) {
							$evtId = $evt->getId();
							break;
						}
					}
				}
			}
		}
		
		// empty default
		$reg = NULL;
		
		// Try to find the registration if this is a modify request
		// Security: It is not enough to check the "id" we also need to check the e-mail and password
		// We do not allow the change the e-mail
		$regEmail = "";
		if ( isset($_GET['lgvas_email'])) {
			$regEmail = $_GET['lgvas_email'];
		}
		$regPasscode = "";
		if ( isset($_GET['lgvas_passcode'])) {
			$regPasscode = $_GET['lgvas_passcode'];
		}
		if (!empty($regEmail) && !empty($regPasscode) ) {
			$regOld = LGV_AS_DB_Registration::findFromUserData($regEmail, $regPasscode);
			if (!empty($regOld)) {
				$reg = $regOld;
				$evtId = $reg->getEventId();
				$saveText = "Anmeldung ändern";
				$aenderung = true;
			}
			else {
				$string .= "<h2>Ihre Daten stimmen nicht überein. Bitte fordern Sie diese nochmals per E-Mail an.</h2>";
				return $string;
			}
		}

		if (empty($reg) && LGV_AS_Util::isEditorOrAdmin()) {
			// Check if we got an registrationId to edit... (from the backend)
			if ( isset($_GET['lgvas_regid'])) {
				$regId = intval($_GET['lgvas_regid']);
				$regOld = LGV_AS_DB_Registration::find($regId);
				if (!empty($regOld)) {
					$reg = $regOld;
					$evtId = $reg->getEventId();
					$saveText = "Anmeldung ändern";
					$aenderung = true;
				}
			}
		}
		
		if ($evtId == 0)
		{
			// Zeige alle an...
			$string .= self::_display_all_events();
			return $string;
		}

		$evt = LGV_AS_DB_Event::find($evtId);
		if (empty($evt)) {
			wp_die("Ungültige Veranstaltung ausgewählt!");
			return;
		}
		
		if ( ($evt->getState() != LGV_AS_BO::Event_State_Active) && (LGV_AS_Util::isEditorOrAdmin() == false) ) {
			wp_die("Inaktive Veranstaltung ausgewählt!");
			return;
		}
		
		// Übernehme einen speziellen "SavedText"
		if (!empty($evt->AddParaMain->SavedText)) {
			$savedText = $evt->AddParaMain->SavedText;
		}
		
		$subAction = "";
		if ( isset($_POST['lgvas_sub_action'])) {
			$subAction = $_POST['lgvas_sub_action'];
		}
		
		$doPreview = false;
		if (isset($_POST["lgvas_submit_preview"])) {
			$doPreview = true;
		}

		$shouldBeDeleted = false;
		$shouldBeDeletedReal = false;  // Soll echt aus der DB gelöscht werden; also nicht nur als gelöscht markiert werden, wie sonst
		if (!empty($reg) && isset($_POST["lgvas_submit_del"])) {
			// Registrierung soll gelöscht werden!
			$shouldBeDeleted = true;
			$savedText  = "Ihre Anmeldung wurde gelöscht. ";
			$savedText .= "Sie bekommen nun eine Bestätigungsmail an Ihre E-Mail Adresse. ";
			//$savedText .= "Besuchen Sie auch unsere LGV-Seite [lgv]!";
		}
		
		if (!empty($reg) && isset($_POST["lgvas_submit_del_real"])) {
			// Registrierung soll endgültig gelöscht werden!
			$shouldBeDeleted = true;
			$shouldBeDeletedReal = true;
			$savedText  = "Ihre Anmeldung wurde endgültig gelöscht. ";
			//$savedText .= "Sie bekommen nun eine Bestätigungsmail an Ihre E-Mail Adresse. ";
			//$savedText .= "Besuchen Sie auch unsere LGV-Seite [lgv]!";
		}
		
		
		// Prüfe, ob die Veranstaltung schon voll ist
		$registerWaitingList = false;
		$waitingListConfirm = false;  // Wenn der Benutzer sich bestätigen will
		$ReRegisterFromDeleted = false;  // Wenn eine gelöschte Anmeldung wieder angemeldet werden soll
		$registered = $evt->getRegisteredCnt();
		$unconfirmed = $evt->getNotConfirmedCnt();
		$waitingList = $evt->getWaitingListCnt();
		$maxCount = $evt->getMaxRegistrations();
		$maxMaxCount = $maxCount + $evt->AddParaMain->MaxWaitingList;
		$freeCount = $maxCount - ($registered+$unconfirmed+$waitingList);
		$waitingFree = $evt->AddParaMain->MaxWaitingList - ($unconfirmed+$waitingList);
		if (($evt->getMaxRegistrations() > 0) && ($freeCount <= 0) ) {
			// Veranstaltung hat eine Grenze und ist schon voll...
			if (empty($reg)) {
				$saveText = "Anmeldung auf Warteliste absenden";
			}
			$registerWaitingList = true;
		}
		$overFull = false;
		if ( ($evt->getMaxRegistrations() > 0) && ($maxMaxCount <= ($registered+$unconfirmed+$waitingList)) ) {
			$overFull = true;
		}
		
		// Ich muss mir die orginal-MaxAnzahl merken, damit ich bei "Warteliste" nie einen größeren Wert auswählen darf!
		$maxAnzahl = $evt->AddParaMain->MaxPerRegister;
		if ($maxAnzahl <= 0) {
			$maxAnzahl = 20;
		}
		$maxAnzahlOrg = $maxAnzahl;
		
		$newRegistration = false;
		$prevState = -1;
		if (empty($reg)) {
			$newRegistration = true;
			// Es soll keine Anmeldung bearbeitet werden, sonder es handelt sich um eine neue Anmeldung
			$reg = new LGV_AS_DB_Registration();
			if ($registerWaitingList == false) {
				// Normale Anmeldung
				$reg->setState(LGV_AS_BO::Registration_State_Registered);
			}
			else {
				if ($evt->AddParaMain->MaxWaitingList > 0) {
					// Anmeldung auf die Warteliste
					$reg->setState(LGV_AS_BO::Registration_State_WaitList);
				} else {
					// 2017-01-14: Wir denken wir waren auf der Warteliste (weil die Veranstaltung in der Zwischenzeit voll wurde), aber wir haben keine Warteliste
					$reg->setState(LGV_AS_BO::Registration_State_Registered);
				}
				// Wenn wir uns auf die Warteliste anmelden, dann begrenze die Anzahl nochmals
				$maxAnzahl = min($maxAnzahl, 10);
			}
		}
		else {
			$prevState = $reg->getState();
			if ( ($registerWaitingList || $overFull) && (LGV_AS_Util::isEditorOrAdmin() == false)) {
				// Ein normaler Benutzer darf seine Anzahl nicht mehr erhöhen! 
				// Also begrenze auf der Wert, den er schon eingegeben hat!
				$maxAnzahl = intval($reg->getRegistrations());
			}
			
			if ($reg->getState() == LGV_AS_BO::Registration_State_NotConfirmed) {
				// Der Beuntzer ist gerade nicht bestätigt und möchte bestätigt werden...
				// Setze somit mal "Registered" (damit es stimmt, wenn er es speichert)
				$reg->setState(LGV_AS_BO::Registration_State_Registered);
				$saveText = "Anmeldung bestätigen";
				$waitingListConfirm = true;
				$maxAnzahl = intval($reg->getRegistrations());
			}
			if ($reg->getState() == LGV_AS_BO::Registration_State_Deleted) {
				$ReRegisterFromDeleted = true;
				// Der Beuntzer war schon gelöscht und will sich jetzt wieder anmelden!
				// Das geht natürlich nur, wenn wir auch noch Platz haben!
				if ($registerWaitingList == false) {
					// Normale Anmeldung
					$reg->setState(LGV_AS_BO::Registration_State_Registered);
				}
				else {
					$saveText = "Anmeldung auf Warteliste absenden";
					// Anmeldung auf die Warteliste
					$reg->setState(LGV_AS_BO::Registration_State_WaitList);
					// Wenn wir uns auf die Warteliste anmelden, dann begrenze die Anzahl nochmals
					$wFree = 10;
					if ($waitingFree > 0) { $wFree = $waitingFree; }
					if (LGV_AS_Util::isEditorOrAdmin() == false) {
						$maxAnzahl = min($maxAnzahl, intval($reg->getRegistrations()), $wFree, 10);
					}
				}
			}
		}
		
		$validationResult = array();
		
		if (($subAction == 'doRegister') || $shouldBeDeleted) {
			$reg->fillFromPostData($evt);
			
			//print_r($reg);
			
			// be sure the "maxAnzahl" is not too much!!!
			$maxAnzahlForValidation = $maxAnzahl;
			if (LGV_AS_Util::isEditorOrAdmin() == false) {
				$maxAnzahlForValidation = $maxAnzahlOrg;
			}
			
			//$reg->setEventTitle($evt->getTitle());
			
			$validationResult = $reg->validate($maxAnzahl, $evt);
			if (empty($validationResult) || $shouldBeDeleted) {
				// Es ist alles gültig, also speichere die Registrierung ab und gebe als Antwort ein "Ok" zurück
				$sendMail = true;
				
				if ($shouldBeDeletedReal) {
					$reg->delete();
					$string .= LGV_AS_Util::getText($savedText);
					
					// Sende die Daten nochmals an den beteiligten, so dass er sieht, für was er noch angemeldet ist; falls überhaupt...
					$email = trim($reg->getEmail());
					if (!empty($email)) {
						// Now try to find the events from the e-mail:
						if (LGV_AS_BO_Registration::sendNotifyFromEmail($email)) {
							$string .= "<p>Ihre Daten wurden nochmals an Ihre E-Mail Adresse gesendet.</p>";
						}
					}
					
					return $string;
				}

				// Ermittle, ob das Mail-Versenden unterbunden werden soll
				if (isset($_POST["lgvas_sendNoMail"])) {
					$sendMail = intval($_POST["lgvas_sendNoMail"]) != 1;
				}
				
				if ($doPreview) {
					$sendMail = false;
				}

				$moveUp = false;
				if ($reg->getState() == LGV_AS_BO::Registration_State_NotConfirmed) {
					$moveUp = true;
				}
				
				if ($shouldBeDeleted) {
					$reg->setState(LGV_AS_BO::Registration_State_Deleted);
				}
				
				// Logge diese Aktion mit, 
				// wenn es nicht das Neuanlegen eines Datensatzes war 
				// (das steht ja schon im 'created_date' drin)
				if ($newRegistration == false) {
					$log = $reg->getPara("log");
					$admTxt = "";
					if (LGV_AS_Util::isEditorOrAdmin()) {
						$admTxt = " (Admin)";
					}
					$logTextAdded = false;
					if ($moveUp) {
						$log .= current_time('mysql') . ": Automatisches Nachrücken von Warteliste (nach Unbestätigt)" . $admTxt . "\r\n";
						$logTextAdded = true;
					}
					if ($prevState != $reg->getState()) {
						$log .= current_time('mysql') . ": Zustand geändert von " . LGV_AS_BO::getRegStateName($prevState) . " nach " . LGV_AS_BO::getRegStateName($reg->getState()) . $admTxt . "\r\n";
						$logTextAdded = true;
					}
					if ($logTextAdded == false) {
						$log .= current_time('mysql') . ": Geändert" . $admTxt . "\r\n";
					}
					
					$reg->setPara("log", $log);
				}
				
				$previewText = $reg->store($sendMail, $moveUp, $prevState, $doPreview);
				
				$string .= "<hr/>";
				
				if ($doPreview) {
					$string .= $previewText;
				} else {
					$string .= LGV_AS_Util::getText($savedText);
				
					// Wurden mit dieser Anmeldung Plätze frei!? Dann versuche diese zu vergeben!
					self::_check_for_free_places_and_inform_waiting_list($evt->getId());
				}
				
				return $string;
			}
		}				
		
		// Display Registration-Form for specific event:
		
		// ID ist ja nur vorhanden, wenn eine bestehende bearbeitet werden soll!
		$regId = $reg->getId();
		
		
		$vh = "h2";
		if (!empty($evt->AddParaMain->GroupKey)) {
			$evtGrp = LGV_AS_DB_EventGroup::findByKey($evt->AddParaMain->GroupKey);
			if (!empty($evtGrp)) {
				$string .= "<h2>" . esc_html($evtGrp->AddPara->Title) . "</h2>";
				$vh = "h3";
			}
		}
		
		$string .= "<" . $vh . ">Anmeldung zu '" . esc_html($evt->getTitle()) . "'</" . $vh . ">";
		
		if (empty($evt->AddParaMain->HeaderText) == false) {
			$string .= "<p>";
			$string .= LGV_AS_Util::getText(esc_html($evt->AddParaMain->HeaderText));
			$string .= "</p>";
		}
		
		// Pürfe, ob sich überhaupt noch Leute anmelden können!
		if (empty($regId) || $ReRegisterFromDeleted) {
			if ($overFull) {
				// Veranstaltung hat eine Grenze und ist schon übervoll!
				$string .= "<h2 class='lgvas_event_full'>Es tut uns leid. ";
				if($evt->AddParaMain->MaxWaitingList > 0) {
					$string .= "Die Veranstaltung ist ausgebucht und auch die Warteliste ist schon voll!</h2>";
				} else {
					$string .= "Die Veranstaltung ist ausgebucht.</h2>";
				}
				$string .= "Bitte versuchen Sie es in einigen Wochen oder kurz vor der Veranstaltung nochmals, vielleicht haben dann noch einige Personen/Gruppen abgesagt und es werden wieder Plätze frei!</h2>";
				if (LGV_AS_Util::isEditorOrAdmin() == false) {
					return $string;
				}
			}
			else if ($registerWaitingList && ($waitingListConfirm == false)) {
				$string .= "<h3 class='lgvas_event_waitinglist'>ACHTUNG: Die Veranstaltung ist schon ausgebucht, sie können sich aber hiermit auf der Warteliste anmelden!</h2>";
			}
		}
				
		if ($waitingListConfirm) {
			$string .= "<h3 class='lgvas_event_waitinglist'>Bitte bestätigen Sie Ihre Anmeldung, so dass Sie von der Warteliste nachrücken können!</h2>";
		}
		
		//print_r($validationResult);
		if (!empty($validationResult)) {
			$string .= "<h3 class='lgvas_validation_error'>Es sind Fehler bei der Eingabe vorhanden!</h3>";
		}
		
		$string .= '<form action="" method="post">';
		$string .= "  <input type='hidden' name='lgvas_evtid' value='" . esc_attr($evtId) . "'></input>";
		$string .= "  <input type='hidden' name='lgvas_sub_action' value='doRegister'></input>";
		
		$string .= "<table>";

		if (LGV_AS_Util::isEditorOrAdmin() ) {
			// Zeige den Zustand an.... erlaube auch diesen zu ändern... (auch bei neuen Einträgen)
			$string .= "<tr>";
			$string .= "  <td><label for='lgvas_state'>Zustand</label>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "state")) . "</td>";
			$string .= "  <td><select id='lgvas_state' name='lgvas_state' >";
			$selectedVal = "";
			if ($reg->getState() == LGV_AS_BO::Registration_State_NotConfirmed) {
				$selectedVal = " selected";
			}
			$string .= "<option value='" . LGV_AS_BO::Registration_State_NotConfirmed . "'" . $selectedVal . ">Unbestätigt</option>";
			
			$selectedVal = "";
			if ($reg->getState() == LGV_AS_BO::Registration_State_Registered) {
				$selectedVal = " selected";
			}
			$string .= "<option value='" . LGV_AS_BO::Registration_State_Registered . "'" . $selectedVal . ">Registriert</option>";
			$selectedVal = "";
			if ($reg->getState() == LGV_AS_BO::Registration_State_WaitList) {
				$selectedVal = " selected";
			}
			$string .= "<option value='" . LGV_AS_BO::Registration_State_WaitList . "'" . $selectedVal . ">Warteliste</option>";
			$selectedVal = "";
			if ($reg->getState() == LGV_AS_BO::Registration_State_Deleted) {
				$selectedVal = " selected";
			}
			$string .= "<option value='" . LGV_AS_BO::Registration_State_Deleted . "'" . $selectedVal . ">Gelöscht</option>";
			$string .= "  </select></td>";
			$string .= "</tr>";
		}
		else {
			// Zeige den Zustand an, wenn er vom "normalen" Abweicht (als <> Registriert)
			if ($reg->getState() != LGV_AS_BO::Registration_State_Registered) {
				$string .= "<tr>";
				$string .= "  <td><label for='lgvas_state'>Zustand</label></td>";
				$string .= "  <td>" . esc_html(LGV_AS_BO::getRegStateName($reg->getState())) . "</td>";
				$string .= "</tr>";
			}
		}

	
		if (!empty($evt->AddParaMain->PersonHeader)) {
			$string .= "<tr>\r\n";
			$string .= "  <td colspan='2'><strong>" . LGV_AS_Util::getText(esc_html($evt->AddParaMain->PersonHeader)) . "</strong></td>\r\n";
			$string .= "</tr>\r\n";
		}
		
		$string .= "<tr>";
		$string .= "  <td><label for='lgvas_firstName'>Vorname<sup>*</sup></label>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "firstName"));
		if (!empty($evt->AddParaMain->FirstNameHint)) {
			$string .= "<div class='lgvas_event_hinttext'>" . LGV_AS_Util::getText(esc_html($evt->AddParaMain->FirstNameHint)) . "</div>";
		}
		$string .= "</td>\r\n";
		$string .= "  <td><input type='text' id='lgvas_firstName' name='lgvas_firstName' value='" . esc_attr($reg->getFirstName()) . "' /></td>";
		$string .= "</tr>";
		
		$string .= "<tr>";
		$string .= "  <td><label for='lgvas_lastName'>Nachname<sup>*</sup></label>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "lastName"));
		if (!empty($evt->AddParaMain->LastNameHint)) {
			$string .= "<div class='lgvas_event_hinttext'>" . LGV_AS_Util::getText(esc_html($evt->AddParaMain->LastNameHint)) . "</div>";
		}
		$string .= "</td>\r\n";
		$string .= "  <td><input type='text' id='lgvas_lastName' name='lgvas_lastName' value='" . esc_attr($reg->getLastName()) . "'></input></td>";
		$string .= "</tr>";
		
		if ($evt->AddParaMain->OptStreet >= 0) {
			$reqTxt = "";
			if ($evt->AddParaMain->OptStreet == 0) {
				$reqTxt = "<sup>*</sup>";
			}
			$string .= "<tr>";
			$string .= "  <td><label for='lgvas_street'>Straße und Hausnummer".$reqTxt."</label>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "street")) . "</td>";
			$string .= "  <td><input type='text' id='lgvas_street' name='lgvas_street' value='" . esc_attr($reg->getStreet()) . "'></input></td>";
			$string .= "</tr>";
		}

		if ($evt->AddParaMain->OptZip >= 0) {
			$reqTxt = "";
			if ($evt->AddParaMain->OptZip == 0) {
				$reqTxt = "<sup>*</sup>";
			}
			$string .= "<tr>";
			$string .= "  <td><label for='lgvas_zipCode'>PLZ".$reqTxt."</label>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "zipCode")) . "</td>";
			$string .= "  <td><input type='text' id='lgvas_zipCode' name='lgvas_zipCode' value='" . esc_attr($reg->getZipCode()) . "'></input></td>";
			$string .= "</tr>";
		}
		
		if ($evt->AddParaMain->OptCity >= 0) {
			$reqTxt = "";
			if ($evt->AddParaMain->OptCity == 0) {
				$reqTxt = "<sup>*</sup>";
			}
			$string .= "<tr>";
			$string .= "  <td><label for='lgvas_city'>Ort".$reqTxt."</label>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "city")) . "</td>";
			$string .= "  <td><input type='text' id='lgvas_city' name='lgvas_city' value='" . esc_attr($reg->getCity()) . "'></input></td>";
			$string .= "</tr>";
		}
		
		if ($evt->AddParaMain->OptPhone == 0) {
		$string .= "<tr>";
		$string .= "  <td><label for='lgvas_phone'>Telefon</label>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "phone")) . "</td>";
		$string .= "  <td><input type='text' id='lgvas_phone' name='lgvas_phone' value='" . esc_attr($reg->getPhone()) . "'></input></td>";
		$string .= "</tr>";
		}
		
		$string .= "<tr>";
		$string .= "  <td><label for='lgvas_email'>E-Mail<sup>*</sup></label>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "email")) . "</td>";
		
		if (empty($regId) || LGV_AS_Util::isEditorOrAdmin()) {
			$string .= "  <td><input type='text' id='lgvas_email' name='lgvas_email' value=" . esc_attr($reg->getEmail()) . "></input>";
			if (LGV_AS_Util::isEditorOrAdmin()) {
				// Blende Feld ein, dass die E-Mail nicht versendet werden soll:
				$string .= "<br/>";
				$string .= "<input type='checkbox' id='lgvas_sendNoMail' name='lgvas_sendNoMail' value='1'>Keine E-Mail versenden</input>";
			}
			$string .= "</td>";
		}
		else {
			// If this is a modification, we do not allow to change the e-mail
			$string .= "  <td>" . esc_html($reg->getEmail()) . "</td>";
		}
		$string .= "</tr>";

		// If new entry, then confirmation is needed for the e-mail
		if (empty($regId) || LGV_AS_Util::isEditorOrAdmin())
		{
			// Es dürfen KEINE E-Mail Fehler vorliegen; nur dann übernehme die Confirm-Email
			$mailErr = LGV_AS_Util::get_validation_error($validationResult, "emailConfirm") . LGV_AS_Util::get_validation_error($validationResult, "email");
			$string .= "<tr>";
			$string .= "  <td><label for='lgvas_emailConfirm'>E-Mail wiederholen<sup>*</sup></label>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "emailConfirm")) . "</td>";
			$email2 = "";
			if ( empty($mailErr) || LGV_AS_Util::isEditorOrAdmin()) {
				$email2 = $reg->getEmail();
			}
			$string .= "  <td><input type='text' id='lgvas_emailConfirm' name='lgvas_emailConfirm' value='" . esc_attr($email2) . "'></input></td>";
			$string .= "</tr>";
		}
		
		if ($evt->AddParaMain->MaxPerRegister > 1) {
			
			// Zeige die Anzahl nur an, wenn es keine Namens-Registrierung gibt:
			if ($evt->AddParaMain->RegWithNames == false || LGV_AS_Util::isEditorOrAdmin()) {
				$string .= "<tr>";
				$string .= "  <td><label for='lgvas_para_anzahl'>" . LGV_AS_Util::getText(esc_html($evt->AddParaMain->WirKommenText)) . "<sup>*</sup></label>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "registrations")) . "</td>";
				// Wenn es nur noch eine Warteliste gibt, dann darf ich hier bei ÄNDERUNGEN keine größeren Zahlen mehr zulassen als ursprünglich geplant!
				//$string .= "  <td><select id='lgvas_para_anzahl' name='lgvas_para_anzahl' >" . LGV_AS_Util::_get_options(1, $maxAnzahl, intval($reg->getRegistrations())) . "</select></td>";
				$anz = intval($reg->getRegistrations());
				if (empty($anz)) {
					$anz = 1;
				}
				$string .= "  <td><input type='text' id='lgvas_para_anzahl' name='lgvas_para_anzahl' value='" . LGV_AS_Util::get_value_as_text(1, $maxAnzahl, $anz) . "' onkeypress='return lgvas_isNumber(event)' /></td>";
				$string .= "</tr>";
			}
		}
		
		// Zusätzliche Daten:
		foreach($evt->AddParaMain->Parameters as $para)
		{
			$string .= "\r\n";
			switch($para->Typ)
			{
				case "Header":
					$string .= "<tr>\r\n";
					$string .= "  <td colspan='2'><strong>" . LGV_AS_Util::getText(esc_html($para->Title)) . "</strong></td>\r\n";
					$string .= "</tr>\r\n";
				break;
				case "MinToMax":
					$string .= "<tr>\r\n";
					$string .= "  <td><label for='" . esc_attr($para->getIdName()) . "'>" . LGV_AS_Util::getText(esc_html($para->Title)) . "</label>";
					if (!empty($para->Hint)) {
						$string .= "<div class='lgvas_event_hinttext'>" . LGV_AS_Util::getText(esc_html($para->Hint)) . "</div>";
					}
					$string .= "</td>\r\n";
					$string .= "  <td><select id='" . esc_attr($para->getIdName()) . "' name='" . esc_attr($para->getIdName()) . "' >" . LGV_AS_Util::_get_options($para->Min, $para->Max, $para->getDef($reg->getParameters())) . "</select></td>\r\n";
					$string .= "</tr>\r\n";
				break;
				case "MinToMaxRegistered":
					$string .= "<tr>\r\n";
					$string .= "  <td><label for='" . esc_attr($para->getIdName()) . "'>" . LGV_AS_Util::getText(esc_html($para->Title)) . "</label>";
					$string .= LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, $para->getIdName()));
					if (!empty($para->Hint)) {
						$string .= "<div class='lgvas_event_hinttext'>" . LGV_AS_Util::getText(esc_html($para->Hint)) . "</div>";
					}
					$string .= "</td>\r\n";
					$string .= "  <td><select id='" . esc_attr($para->getIdName()) . "' name='" . esc_attr($para->getIdName()) . "' >" . LGV_AS_Util::_get_options($para->Min, $maxAnzahl, $para->getDef($reg->getParameters())) . "</select></td>\r\n";
					$string .= "</tr>\r\n";
				break;
				case "Bool":
					$string .= "<tr>\r\n";
					$string .= "  <td><label for='" . esc_attr($para->getIdName()) . "'>" . LGV_AS_Util::getText(esc_html($para->Title)) . "</label>";
					$string .= LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, $para->getIdName()));
					if (!empty($para->Hint)) {
						$string .= "<div class='lgvas_event_hinttext'>" . LGV_AS_Util::getText(esc_html($para->Hint)) . "</div>";
					}
					$string .= "</td>\r\n";
					$string .= "  <td><input type='checkbox' value='1' id='" . esc_attr($para->getIdName()) . "' name='" . esc_attr($para->getIdName()) . "' " . LGV_AS_Util::get_checked($para->getDef($reg->getParameters())) . " /></td>\r\n";
					$string .= "</tr>\r\n";
				break;
				case "MinToMaxText":	
					$string .= "<tr>\r\n";
					$string .= "  <td><label for='" . esc_attr($para->getIdName()) . "'>" . LGV_AS_Util::getText(esc_html($para->Title));
					$string .= "</label>";
					if (!empty($para->Hint)) {
						$string .= "<div class='lgvas_event_hinttext'>" . LGV_AS_Util::getText(esc_html($para->Hint)) . "</div>";
					}
					$string .= "</td>\r\n";
					$string .= "  <td><input type='text' id='" . esc_attr($para->getIdName()) . "' name='" . esc_attr($para->getIdName()) . "' value='" . LGV_AS_Util::get_value_as_text($para->Min, $para->Max, $para->getDef($reg->getParameters())) . "' onkeypress='return lgvas_isNumber(event)' /></td>\r\n";
					$string .= "</tr>\r\n";
				break;
				case "Sel":
					$string .= "<tr>\r\n";
					$string .= "  <td><label for='" . esc_attr($para->getIdName()) . "'>" . LGV_AS_Util::getText(esc_html($para->Title));
					if (!LGV_AS_Util::get_sel_hasValidDefault($para->Sel, $para->getDef(null))) {
						$string .= "<sup>*</sup>";
					}
					$string .= "</label>";
					$string .= LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, $para->getIdName()));
					if (!empty($para->Hint)) {
						$string .= "<div class='lgvas_event_hinttext'>" . LGV_AS_Util::getText(esc_html($para->Hint)) . "</div>";
					}
					$string .= "</td>\r\n";
					$string .= "  <td>\r\n";
					$string .= "    <select id='" . esc_attr($para->getIdName()) . "' name='" . esc_attr($para->getIdName()) . "' >" . LGV_AS_Util::get_sel($para->Sel, $para->getDef($reg->getParameters()), $para->getDef(null)) . "\r\n    </select>\r\n</td>\r\n";
					$string .= "</tr>\r\n";
				break;
				case "Text":
					$string .= "<tr>\r\n";
					$string .= "  <td><label for='" . esc_attr($para->getIdName()) . "'>" . LGV_AS_Util::getText(esc_html($para->Title));
					if (intval($para->Req) > 0) {
						$string .= "<sup>*</sup>";
					}
					$string .= "</label>";
					$string .= LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, $para->getIdName()));
					if (!empty($para->Hint)) {
						$string .= "<div class='lgvas_event_hinttext'>" . LGV_AS_Util::getText(esc_html($para->Hint)) . "</div>";
					}
					$string .= "</td>\r\n";
					$string .= "  <td><input type='text' id='" . esc_attr($para->getIdName()) . "' name='" . esc_attr($para->getIdName()) . "' value='" . esc_attr($para->getDef($reg->getParameters())) . "' /></td>\r\n";
					$string .= "</tr>\r\n";
				break;
				case "Date":
					$string .= "<tr>\r\n";
					$string .= "  <td><label for='" . esc_attr($para->getIdName()) . "'>" . LGV_AS_Util::getText(esc_html($para->Title));
					$string .= "<sup>*</sup>";
					$string .= "</label>";
					$string .= LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, $para->getIdName()));
					if (!empty($para->Hint)) {
						$string .= "<div class='lgvas_event_hinttext'>" . LGV_AS_Util::getText(esc_html($para->Hint)) . "</div>";
					}
					$string .= "</td>\r\n";
					$string .= "  <td><input type='text' id='" . esc_attr($para->getIdName()) . "' name='" . esc_attr($para->getIdName()) . "' value='" . esc_attr($para->getDef($reg->getParameters())) . "' /></td>\r\n";
					$string .= "</tr>\r\n";
				break;
			case "Payment":
					$opt = LGV_AS_BO::getOptions();
					$string .= $opt->PaymentInfos->getFrontEndFormData($para, $validationResult, $reg);
				break;
			}
		}

		if ($evt->AddParaMain->RegWithNames && ($evt->AddParaMain->MaxPerRegister > 1)) {
			if (!empty($evt->AddParaMain->RegWithNamesMsg)) {
				$string .= "<tr><td colspan='2'>";
				$string .= LGV_AS_Util::getText($evt->AddParaMain->RegWithNamesMsg);
				$string .= "</td></tr>";
			}
			$i =0;
			$regNames = array();
			$regNamesStr = $reg->getPara("regNames");
			if (!empty($regNamesStr)) {
				$regNames = json_decode($regNamesStr, true);
				//print_r($regNames);
			}
			$regCnt = $reg->getRegistrations() -1;
			if ($regCnt < 0) { $regCnt = 0; }
			for($i=1; $i < $maxAnzahl; $i++) {
				// Ermittle den Default-Wert (oder die letzte Eingabe...
				$defVal = "";
				$defStyle = "style='display:none'";
				$visVal = "";
				if ($regCnt >= $i) {
					if (count($regNames) >= $i) {
						$arrVal = array_values($regNames);
						$defVal  = $arrVal[$i-1];
					}
					$defStyle = ""; //"style='display:inherit'";
					$visVal = "1";
				}
				$string .= "<tr id='lgvas_para_anzahl_root" . $i . "' " . $defStyle . " colspan='2'><td>";
				$string .= "<input id='lgvas_para_anzahl_remove'  name='lgvas_para_anzahl_remove' type='button' value='Entfernen' onClick='lgvas_onRemove(" . $i . ", " . ($maxAnzahl-1) . ")'>";
				$string .= "<input id='lgvas_para_anzahl_visible" . $i . "' type='hidden' name='lgvas_para_anzahl_visible" . $i . "' value='" . $visVal . "' />";
				$string .= " ";
				$string .= "<input id='lgvas_para_anzahl" . $i . "' type='text' name='lgvas_para_anzahl" . $i . "' value='" . $defVal . "' />";
				$string .= LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "lgvas_para_anzahl" . $i));
				$string .= "</td></tr>\r\n";
			}
			
			$string .= "<tr><td colspan='2'>";
			$string .= "<input id='lgvas_para_anzahl_add'  name='lgvas_para_anzahl_add' type='button' value='Person hinzufügen' onClick='lgvas_onAdd(" . ($maxAnzahl-1) . ")'>";
			$string .= "<script>lgvas_onAdd_onLoad(" . (intval($regCnt) + 1) . ", " . ($maxAnzahl-1) . ")</script>";
			$string .= "</td></tr>";
		}
		
		$string .= "</table>";
		
		//$string .= "<p>Nachdem Sie die Anmeldung abgesendet haben, bekommen Sie eine E-Mail in der die Daten nochmals aufgeführt sind. " .
		//	"In der E-Mail ist auch ein Link vorhanden, durch welchen Sie die Angaben jederzeit selbstständig ändern können. ";
		
		if (empty($evt->AddParaMain->FooterText) == false) {
			$string .= "<p>";
			$string .= LGV_AS_Util::getText(esc_html($evt->AddParaMain->FooterText));
			$string .= "</p>";
		}

		$string .= "<p>";
		$string .= "<input type='submit' name='lgvas_submit' value='" . $saveText . "' />";
		
		if ($aenderung && ($ReRegisterFromDeleted == false) && (LGV_AS_Util::isEditorOrAdmin() == false) &&(intval($evt->AddParaMain->EditMode) == 0) ) {
			$string .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			$string .= "<input style='color:red' type='submit' name='lgvas_submit_del' value='Anmeldung LÖSCHEN!' onclick=\"return confirm('Wollen Sie Ihre Anmeldung wirklich löschen?')\" />";
		}

		if (LGV_AS_Util::isEditorOrAdmin()) {
			$string .= "<hr/>";
			$string .= "<input type='submit' name='lgvas_submit_preview' value='Vorschau' />";
		}
		$string .= "</p>";
		
		if ($aenderung && LGV_AS_Util::isEditorOrAdmin()) {
			$string .= "<hr/>";
			$string .= "<input style='color:red' type='submit' name='lgvas_submit_del_real' value='Anmeldung endgültig LÖSCHEN!' onclick=\"return confirm('Wollen Sie Ihre Anmeldung endgültig löschen?')\" />";
		}
		

		$string .= "<div><sup>* Diese Angaben sind Pflichtfelder</sup></div>";

		$string .= "</form>";
		
		return $string;		
	}
	

}  // end class LGV_AS_Frontend
