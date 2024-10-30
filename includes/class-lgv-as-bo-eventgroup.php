<?php

/**
 * Description of class-LGV_AS_BO_Event
 *
 * @authors Jochen Kalmbach
 */

// Business Object: Event-Additional-Parameter
class LGV_AS_BO_EventGroupAddPara {
	public function __construct()
	{
	}
	
	public $Title;
	
	public function fill($jsonData)
	{
		if (array_key_exists("Title", $jsonData)) {
			$this->Title = $jsonData["Title"];
		}
	}
}

// Business Object: EventGroup
class LGV_AS_BO_EventGroup {
	public function __construct()
	{
		$this->AddPara  = new LGV_AS_BO_EventGroupAddPara();
		$this->AddPara_Error = false;
	}
	protected $id;
	public function getId() { return $this->id; }
	protected function setId($id) { $this->id = $id; }

	protected $key;
	public function getKey() { return $this->key; }
	protected function setKey($key) { $this->key = $key; }

	public function getAdditionalParameters() 
	{ 
		$str =  json_encode($this->AddPara, JSON_PRETTY_PRINT);
		return $str;
	}
	public function setAdditionalParameters($additionalParameters) 
	{ 
		$this->AddPara_Error = false;
		$jsonData = json_decode($additionalParameters, true);
		if (json_last_error() != JSON_ERROR_NONE) {
			$this->AddParaMain_Error = json_last_error_msg();
		}
		
		$this->AddPara = new LGV_AS_BO_EventGroupAddPara();
		$this->AddPara->fill($jsonData);
	}
	
	public $AddPara;
	private $AddPara_Error;

	public function fillFromPostData() {
		$this->setKey($_POST['lgvas_key']);
		//$this->setAdditionalParameters(stripcslashes($_POST['lgvas_additionalParameters']));
		$this->AddPara->Title = trim($_POST['lgvas_AddPara_Title']);
	}
	
	public function validate() {
		$res = array();
		if (empty($this->key)) {
			$res["key"] = "Sie müssen einen gültigen Key eingeben!";
		}
		return $res;
	}
}
