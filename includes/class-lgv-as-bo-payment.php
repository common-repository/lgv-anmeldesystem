<?php

/**
 * Description of class LGV_AS_PaymentInfos, LGV_AS_Sepa
 *
 * @authors Jochen Kalmbach
 */
 
require_once LGV_AS_PATH . 'includes/iban.php';


class LGV_AS_PaymentInfos {
	public function __construct()
	{
		$this->Payments = array();
	}
	
	public $Payments;

	public function fillFromBackendPostData($text)
	{
		//print_r($text);
		$jsonData = json_decode($text, true);
		if (json_last_error() != JSON_ERROR_NONE) {
			print_r(json_last_error_msg());
			return;
		}
		$this->fill($jsonData);
	}

	public function fill($jsonData)
	{
		if (array_key_exists("Payments", $jsonData)) {
			$pArr = $jsonData["Payments"];
			if (is_array($pArr)) 
			{
				$this->Payments = array();  // clear array
				foreach($pArr as $value)
				{
					$pE = new LGV_AS_Payment();
					$pE->fill($value);
					array_push($this->Payments, $pE);
				}
			}
		}
	}
	
	public function fillFromPostData($para, &$regParaArray) {
		$payment = $this->GetPaymentFromId($para->Id);
		if (empty($payment)) {
			return;
		}
		
		$data = new LGV_AS_PaymentData();
		
		$method = sanitize_text_field($_POST[$para->getIdName()]);
		
		$data->Method = $method;
		
		switch($method) {
			case "bar":
			break;
			case "sepa":
				if (isset($_POST["lgvas_payment_sepa_cb"])) {
					$data->SepaChecked = boolval($_POST["lgvas_payment_sepa_cb"]);
				}
				$data->SepaKonto = sanitize_text_field($_POST["lgvas_payment_sepa_konto"]);
				$data->SepaIban = sanitize_text_field($_POST["lgvas_payment_sepa_iban"]);
				$data->SepaBic = sanitize_text_field($_POST["lgvas_payment_sepa_bic"]);
				
				$iban = new LGV_AS_IBAN($data->SepaIban);
				$ibanError = "";
				if ($iban->validate($ibanError)) {
					$data->SepaIban = $iban->format();
				}
			break;
		}

		$regParaArray[$para->getParaName()] = $data->ToJsonString();
	}
	
	public function validate($para, $regParaArray, &$res, $registrations) {
		$payment = $this->GetPaymentFromId($para->Id);
		if (empty($payment)) {
			return;
		}
		
		$data = LGV_AS_PaymentData::FromJsonString($regParaArray[$para->getParaName()]);
		
		if ($payment->Sepa && $data->Method == "sepa") {
			if (empty($data->SepaKonto)) {
				$res["lgvas_payment_sepa_konto"] = "Bitte geben Sie einen Kontoinhaber an.";
			}
			
			if (empty($data->SepaIban)) {
				$res["lgvas_payment_sepa_iban"] = "Bitte geben Sie eine IBAN an.";
			}
			else {
				$iban = new LGV_AS_IBAN($data->SepaIban);
				$ibanError = "";
				if (!$iban->validate($ibanError)) {
					switch($ibanError) {
						case 1:
							$res["lgvas_payment_sepa_iban"] = "IBAN Ländercode wird nicht unterstützt.";
							break;
						case 2:
							$res["lgvas_payment_sepa_iban"] = "IBAN Länge ist ungültig.";
							break;
						case 3:
							$res["lgvas_payment_sepa_iban"] = "IBAN Format ist ungültig.";
							break;
						case 4:
							$res["lgvas_payment_sepa_iban"] = "IBAN Prüfsumme ist ungültig (Zahlendreher?).";
							break;
						default:
							$res["lgvas_payment_sepa_iban"] = "IBAN: Unbekannter Fehler.";
							break;
					}
				}
			}

			if (empty($data->SepaBic)) {
				$res["lgvas_payment_sepa_bic"] = "Bitte geben Sie eine BIC an.";
			}
			else {
				if (!LGV_AS_IBAN::validateSwiftBic($data->SepaBic)) {
					$res["lgvas_payment_sepa_bic"] = "Bitte geben Sie eine gültige BIC an.";
				}
			}
			
			if (!$data->SepaChecked) {
				$res["lgvas_payment_sepa_cb"] = "Sie müssen dem SEPA-Lastschriftmandat zustimmen.";
			}
		}
	}
	
	public function getFrontEndFormData($para, $validationResult, $reg) {
		$string = "";
		
		// Find payment method with the "id" of the AddPara (of class LGV_AS_BO_EventAddPara)
		$payment = $this->GetPaymentFromId($para->Id);
		if (empty($payment)) {
			$string .= "<tr><td style='color:red'>Invalid payment id (" . $para->Id . "). Please configure payment in global settings!</td></tr>";
			return $string;
		}
		
		$regParaArray = $reg->getParameters();
		$key = $para->getParaName();
		$data = null;
		if (!empty($regParaArray) && (isset($regParaArray[$key])) ) {
			$data = LGV_AS_PaymentData::FromJsonString($regParaArray[$key]);
		}
		if (empty($data)) {
			$data = new LGV_AS_PaymentData();
		}
		
		$string .= "<tr>\r\n";
		$string .= "  <td><label for='" . esc_attr($para->getIdName()) . "'>" . LGV_AS_Util::getText(esc_html($para->Title));
		$string .= "<sup>*</sup>";
		$string .= "</label>";
		$string .= LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, $para->getIdName()));
		if (!empty($para->Hint)) {
			$string .= "<div class='lgvas_event_hinttext'>" . LGV_AS_Util::getText(esc_html($para->Hint)) . "</div>";
		}
		$string .= "  </td>\r\n";
		
		$string .= "  <td>\r\n";
		$string .= "    <select id='" . esc_attr($para->getIdName()) . "' name='" . esc_attr($para->getIdName()) . "' onChange='lgvas_payment_selection_onChange(this.value)' >";
		if ($payment->SepaFirst) {
			if ($payment->Sepa) {
				$selected = "";
				if (strcasecmp($this->GetMethodOrDef($para, $payment, $data), "sepa") == 0) {
					$selected = " selected";
				}
				$value = "sepa";
				$text = $payment->SepaText;
				$string .= "\r\n<option value='" . $value . "'" . $selected .">" . $text . "</option>";
			}
		}
		if ($payment->Bar) {
			$selected = "";
			if (strcasecmp($this->GetMethodOrDef($para, $payment, $data), "bar") == 0) {
				$selected = " selected";
			}
			$value = "bar";
			$text = $payment->BarText;
			$string .= "\r\n<option value='" . $value . "'" . $selected .">" . $text . "</option>";
		}
		if (!$payment->SepaFirst) {
			if ($payment->Sepa) {
				$selected = "";
				if (strcasecmp($this->GetMethodOrDef($para, $payment, $data), "sepa") == 0) {
					$selected = " selected";
				}
				$value = "sepa";
				$text = $payment->SepaText;
				$string .= "\r\n<option value='" . $value . "'" . $selected .">" . $text . "</option>";
			}
		}
		$string .= "    </select>\r\n  </td>\r\n";

		$string .= "</tr>\r\n";
		
		if ($payment->Sepa) {
			$string .= "<tr id='lgvas_payment_sepa_tr_desc' style='display:none'>";
			$string .= "<td colspan=2>";
			$string .= LGV_AS_Util::getText(esc_html($payment->SepaInfo->Header));
			$string .= "</tr>";

			$string .= "<tr id='lgvas_payment_sepa_tr_konto' style='display:none'>";
			$string .= "<td><label for='lgvas_payment_sepa_konto'>Kontoinhaber<sup>*</sup></label>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "lgvas_payment_sepa_konto")) . "</td>";
			$string .= "  <td><input type='text' id='lgvas_payment_sepa_konto' name='lgvas_payment_sepa_konto' value='" . esc_attr($data->SepaKonto) . "'></input></td>";
			$string .= "</tr>";

			$string .= "<tr id='lgvas_payment_sepa_tr_iban' style='display:none'>";
			$string .= "<td><label for='lgvas_payment_sepa_iban'>IBAN<sup>*</sup>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "lgvas_payment_sepa_iban")) . "</label></td>";
			$string .= "  <td><input type='text' id='lgvas_payment_sepa_iban' name='lgvas_payment_sepa_iban' value='" . esc_attr($data->SepaIban) . "'></input></td>";
			$string .= "</tr>";

			$string .= "<tr id='lgvas_payment_sepa_tr_bic' style='display:none'>";
			$string .= "<td><label for='lgvas_payment_sepa_bic'>BIC<sup>*</sup>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "lgvas_payment_sepa_bic")) . "</label></td>";
			$string .= "  <td><input type='text' id='lgvas_payment_sepa_bic' name='lgvas_payment_sepa_bic' value='" . esc_attr($data->SepaBic) . "'></input></td>";
			$string .= "</tr>";

			$string .= "<tr id='lgvas_payment_sepa_tr_cb' style='display:none'>";
			$string .= "  <td><label for='lgvas_payment_sepa_cb'>" . LGV_AS_Util::getText(esc_html($payment->SepaInfo->Title)) . "<sup>*</sup></label>" . LGV_AS_Util::getText(LGV_AS_Util::get_validation_error($validationResult, "lgvas_payment_sepa_cb")) . "</td>";
			$string .= "  <td><input type='checkbox' value='1' id='lgvas_payment_sepa_cb' name='lgvas_payment_sepa_cb' " . LGV_AS_Util::get_checked($data->SepaChecked) . "/></td>\r\n";
			$string .= "</tr>";
		}
		
		$string .= "<script>lgvas_payment_selection_onChange(document.getElementById('" . esc_attr($para->getIdName()) . "').value)</script>";
		
		return $string;
	}
	
	private function GetMethodOrDef($para, $payment, $data) {
		switch($data->Method) {
			case "bar":
				if ($payment->Bar) {
					return "bar";
				}
			case "sepa":
				if ($payment->Sepa) {
					return "sepa";
				}
		}
		if ($payment->Bar && (strcasecmp($para->Def, "bar") == 0) ) {
			return "bar";
		}
		if ($payment->Sepa && (strcasecmp($para->Def, "sepa") == 0) ) {
			return "sepa";
		}
		return null;
	}
	
	private function GetValueFromReg($reg) {
		return null; // TODO:
	}
	
	private function GetPaymentFromId($id) {
		foreach($this->Payments as $payment) {
			if (strcasecmp($payment->Id, $id) == 0) {
				return $payment;
			}
		}
		return null;
	}
}

class LGV_AS_Payment {
	public function __construct()
	{
		$this->Id = "payment01";
		$this->Bar = true;
		$this->BarText = "Barzahlung vor Ort (bei Seminarbeginn)";
		$this->Sepa = true;
		$this->SepaText = "Lastschrift/Bankeinzug";
		$this->SepaInfo = new LGV_AS_Sepa();
		$this->SepaFirst = true;
	}
	public function fill($jsonData)
	{
		if (array_key_exists("Id", $jsonData)) {
			$this->Id = $jsonData["Id"];
		}
		if (array_key_exists("Bar", $jsonData)) {
			$this->Bar = boolval($jsonData["Bar"]);
		}
		if (array_key_exists("BarText", $jsonData)) {
			$this->BarText = $jsonData["BarText"];
		}
		if (array_key_exists("Sepa", $jsonData)) {
			$this->Sepa = boolval($jsonData["Sepa"]);
		}
		if (array_key_exists("SepaText", $jsonData)) {
			$this->SepaText = $jsonData["SepaText"];
		}
		if (array_key_exists("SepaFirst", $jsonData)) {
			$this->SepaFirst = boolval($jsonData["SepaFirst"]);
		}
		if (array_key_exists("SepaInfo", $jsonData)) {
			$this->SepaInfo =  new LGV_AS_Sepa();
			$this->SepaInfo->fill($jsonData["SepaInfo"]);
		}
	}
	
	public $Id;
	public $Bar;
	public $BarText;
	public $Sepa;
	public $SepaText;
	public $SepaInfo;
	public $SepaFirst;
}


class LGV_AS_Sepa {
	public function __construct()
	{
		$this->Title = "Hiermit erteile ich das SEPA-Lastschriftmandat";
		$this->Header = "[b]Zahlungsempfänger:[/b] XXXX[br]Gläubiger: LGV[br]Gläubiger-Identifikationsnummer: DE27ZZZ00000026352[p]" .
			"[b]Mandatsreferenz[/b]: Wird separat mitgeteilt.[p]" .
			"Hiermit ermächtige ich den Zahlungsempfänger, einmalig eine Zahlung von meinem Konto mittels Lastschrift einzuziehen. " .
			"Zugleich weise ich mein Kreditinstitut an, die von dem Zahlungsempfänger auf mein Konto gezogene Lastschrift einzulösen.[p]" .
			"Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. " .
			"Es gelten dabei die mit meinem Kreditinstitut vereinbanen Bedingungen."
			;
	}
	
	public $Header;
	public $Title;
	
	public function fill($jsonData)
	{
		if (array_key_exists("Header", $jsonData)) {
			$this->Header = $jsonData["Header"];
		}
		if (array_key_exists("Title", $jsonData)) {
			$this->Title = $jsonData["Title"];
		}
	}
}


class LGV_AS_PaymentData {
	public function __construct()
	{
		$this->Method = null;
		$this->SepaChecked = null;
		$this->SepaKonto = null;
		$this->SepaIban = null;
		$this->SepaBic = null;
	}
	
	public $Method;
	
	public $SepaChecked;
	public $SepaKonto;
	public $SepaIban;
	public $SepaBic;
	
	public static function FromJsonString($text) {
		if (empty($text)) {
			return new LGV_AS_PaymentData();  // return empty object (default values)
		}
		$jsonData = json_decode($text, true);
		if (json_last_error() != JSON_ERROR_NONE) {
			//print_r(json_last_error_msg());
			return new LGV_AS_PaymentData();  // return empty object (default values)
		}
		$data = new LGV_AS_PaymentData();
		$data->fill($jsonData);
		return $data;
	}
	
	private function fill($jsonData) {
		if (array_key_exists("Method", $jsonData)) {
			$this->Method = $jsonData["Method"];
		}
		if (array_key_exists("SepaChecked", $jsonData)) {
			$this->SepaChecked = boolval($jsonData["SepaChecked"]);
		}
		if (array_key_exists("SepaKonto", $jsonData)) {
			$this->SepaKonto = $jsonData["SepaKonto"];
		}
		if (array_key_exists("SepaIban", $jsonData)) {
			$this->SepaIban = $jsonData["SepaIban"];
		}
		if (array_key_exists("SepaBic", $jsonData)) {
			$this->SepaBic = $jsonData["SepaBic"];
		}
	}
	
	public function IbanForMail() {
		// show the first 4 and the last 6 characters... all others are X...
		if (strlen($this->SepaIban) > 4) {
			$first4 = substr($this->SepaIban, 0, 4);
			$after4 = substr($this->SepaIban, 4);
		  
			if (strlen($after4) > 7) {
				$last7 = substr($after4, -7);  // last 7 characters
				$middle = substr($after4, 0, strlen($after4)-7);
				return $first4 . preg_replace('/\\d/', 'X', $middle) . $last7;
			}
			else {
				return $first4 . preg_replace('/\\d/', 'X', $after4);
			}
		}
		return $this->SepaIban;
	}

	public function ToJsonString() {
		$str =  json_encode($this, JSON_PRETTY_PRINT);
		return $str;
	}
	
	public function ToCsvString() {
		$string = "";
		if ($this->Method == "sepa") {
			$string .= "SEPA:\r\n";
			$string .= "Konto: " . $this->SepaKonto . "\r\n";
			$string .= "IBAN: " . $this->SepaIban . "\r\n";
			$string .= "BIC: " . $this->SepaBic;
			return $string;
		}
		if ($this->Method == "bar") {
			return "BAR";
		}
	}

	public function ToMailString() {
		$string = "";
		if ($this->Method == "sepa") {
			$string .= "SEPA-Lastschriftmandat, Konto: " . $this->SepaKonto . ", IBAN: " . $this->IbanForMail();
			return $string;
		}
		if ($this->Method == "bar") {
			return "Bar";
		}
	}
}
