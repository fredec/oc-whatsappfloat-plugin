<?php namespace Diveramkt\FloatingBanner\Components;

use Cms\Classes\ComponentBase;

use Diveramkt\FloatingBanner\Models\ExitPopup;

class ExitFloatPopup extends ComponentBase
{

	public function componentDetails(){
		return [
			'name' => 'Exit Popup',
			'description' => 'A popup to open when the user is about to exit'
		];
	}

	public function defineProperties(){
		return [

		];
	}

	public function link($url) {
		if(!strpos("[".$url."]", "http://") && !strpos("[".$url."]", "https://")) $url='http://'.$url;
		return $url;
	}

	public function target($link){
		if(!strpos("[".$link."]", $_SERVER['HTTP_HOST'])) return '_blank';
		else return '_parent';
	}

	public function onRun(){
		$this->exitpopup = $this->getExitPopup();

		// $base_url = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
		// $valores['target']=$this->target($valores['link'], $base_url);

		if(isset($this->exitpopup->attributes)){
			$valores=$this->exitpopup->attributes;
			if(str_replace(' ','',$valores['link']) != '') $valores['link']=$this->link($valores['link']);
			$valores['target']='_parent'; if(!strpos("[".$valores['link']."]", $_SERVER['HTTP_HOST'])) $valores['target']='_blank';
			$this->exitpopup->attributes=$valores;
		}
	}

	protected function getExitPopup(){
		return ExitPopup::first();
	}

	public $exitpopup;
}