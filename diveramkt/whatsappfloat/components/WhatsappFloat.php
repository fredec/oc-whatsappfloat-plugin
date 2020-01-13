<?php
namespace Diveramkt\WhatsappFloat\Components;
// namespace diveramkt\whtasappfloat\components;

use Diveramkt\WhatsappFloat\Models\Settings;
// use Detection\MobileDetect as Mobile_Detect;

class WhatsappFloat extends \Cms\Classes\ComponentBase
{
	public function componentDetails()
	{
		return [
			'name' => 'Whatsapp Float',
			'description' => 'Link para enviar mensagem diretamente no Whatsapp.'
		];
	}

	public function onRun(){
		$this->addCss('/plugins/diveramkt/whatsappfloat/assets/whatsapp.css');
		$defaultFields = Settings::instance()->toArray();
		if (!empty($defaultFields)) {
			foreach ($defaultFields['value'] as $key => $defaultValue) {
				$this->settings[$key] = $defaultValue;
			}
		}
		$this->settings = (object)$this->settings;
		$this->numero=''; $this->telefone='';
		if(isset($this->settings->numero) && $this->settings->numero) $this->numero=preg_replace("/[^0-9]/", "", $this->settings->numero);
		if(isset($this->settings->tel) && $this->settings->tel) $this->telefone=preg_replace("/[^0-9]/", "", $this->settings->tel);

		if($this->mobile()) $this->device = 'mobile'; else $this->device = 'desktop';
		// $detect = new Mobile_Detect;
		// $this->device = 'desktop'; if ($detect->isMobile()) $this->device = 'mobile';
	}

	public function mobile(){
		if(!isset($_SERVER['HTTP_USER_AGENT'])) return;
		$iphone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
		$ipad = strpos($_SERVER['HTTP_USER_AGENT'],"iPad");
		$android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
		$palmpre = strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
		$berry = strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
		$ipod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
		$symbian =  strpos($_SERVER['HTTP_USER_AGENT'],"Symbian");
		if ($iphone || $ipad || $android || $palmpre || $ipod || $berry || $symbian == true):
			$mobile=true;
		else : 
			$mobile=false;
		endif;

		return $mobile;
	}

	public $device;
	public $settings;
	public $numero, $telefone;

}