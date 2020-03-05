<?php
namespace Diveramkt\WhatsappFloat\Components;
// namespace diveramkt\whtasappfloat\components;

use Diveramkt\WhatsappFloat\Models\Settings;
// use Detection\MobileDetect as Mobile_Detect;

// use Diveramkt\WhatsappFloat\Classes\Gregwar\Image\Image;
use Diveramkt\WhatsappFloat\Classes\Image;

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
		$setin = new Settings;

		$defaultFields = Settings::instance();
		$uploadedFile = $defaultFields->foto_mensagem;
		$this->addCss('/plugins/diveramkt/whatsappfloat/assets/whatsapp.css?atualizado');
		$defaultFields = $defaultFields->toArray();

		if (!empty($defaultFields)) {
			if(isset($defaultFields['value'])){
				foreach ($defaultFields['value'] as $key => $defaultValue) {
					$this->settings[$key] = $defaultValue;
				}
			}
		}

		if(isset($uploadedFile->attributes)){

			$foto=$uploadedFile->getPath();
			$this->settings['foto_mensagem'] = $uploadedFile->attributes;
			$this->settings['foto_mensagem']['path']=$foto;

			$class=get_declared_classes();
			if(!in_array('ToughDeveloper\ImageResizer\Plugin', $class)){
				$image = new Image($foto);
				$options = [];
				$options['extension']='jpg';
				$options['mode']='crop';
			// $foto=$image->resize(60, 60, $options)->render();
				$this->settings['foto_mensagem']['path_resize']=$image->resize(60, 60, $options);
			}

		}


		$this->settings = (object)$this->settings;
		$this->numero=''; $this->telefone='';
		if(isset($this->settings->numero) && $this->settings->numero) $this->numero=preg_replace("/[^0-9]/", "", $this->settings->numero);

		$this->settings->legenda_telefone='';
		if(isset($this->settings->tel) && $this->settings->tel){
			$this->settings->legenda_telefone=$this->settings->tel;
			$this->telefone=preg_replace("/[^0-9]/", "", $this->settings->tel);

			if(isset($this->settings->legenda_tel) && str_replace(' ', '', $this->settings->legenda_tel) != ''){
				$this->settings->legenda_telefone=$this->settings->legenda_tel;
			}
		}

		// /////////////CLASS ICONES BOTÕES
		$this->icones_fonte['numero']='fa'; $this->icones_fonte['whatsapp']='fa';
		$versao_icones='4.7.0'; if(isset($this->settings->versao_awesome)) $versao_icones=$this->settings->versao_awesome;
		if($versao_icones == '5.1.0') $this->icones_fonte['whatsapp']='fab';
		// /////////////CLASS ICONES BOTÕES

		if((isset($this->settings->mensagem) && str_replace(' ','',$this->settings->mensagem) == '') || !isset($this->settings->mensagem)){
			$this->settings->mensagem='Precisando de Ajuda ?';
		}

		if($this->mobile()) $this->device = 'mobile'; else $this->device = 'desktop';

		if(!isset($this->settings->legenda_whats) || str_replace(' ', '', $this->settings->legenda_whats) == ''){
			$this->settings->legenda_whats='Atendimento via WhatsApp';
		}
		if(!isset($this->settings->legenda_whats_mobile) || str_replace(' ', '', $this->settings->legenda_whats_mobile) == ''){
			$this->settings->legenda_whats_mobile='WhatsApp';
		}
		if(!isset($this->settings->text_padrao) || str_replace(' ', '', $this->settings->text_padrao) == ''){
			$this->settings->text_padrao='';
		}

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
	public $icones_fonte;

}