<?php

namespace Diveramkt\WhatsappFloat\Classes;
use Diveramkt\WhatsappFloat\Classes\formContato;
// use Martin\Forms\Classes\MagicForm;

// use Diveramkt\WhatsappFloat\Classes\MagicForm;
use Diveramkt\WhatsappFloat\Classes\Image;
use Config;
use Flash;
use Request;

use Diveramkt\WhatsappFloat\Models\Settings;
use System\Models\MailSetting;
use Diveramkt\Miscelanious\Classes\Functions as functions_miscelanious;
use Diveramkt\WhatsappFloat\Classes\BackendHelpers;

class Configsfloat {

	function __construct(){}

	public static function personalizados($settings){
		$retorno=array();

		$total=0;
		$total_mobile=0;
		$total_desktop=0;

		foreach ($settings->links_personalizados as $key => $value) {
			if(isset($value['enabled']) && !$value['enabled']) continue;
			if((!$value['link'] && (!isset($value['tipo']) or $value['tipo'] != 'form')) or !$value['title']) continue;

			if($value['enabled_programacao']){
				$value['ativar']=0;
				if(count($value['programacao'])){
					foreach ($value['programacao'] as $key2 => $vet) {
						if($value['ativar']) continue;
						if($settings->dia_semana == $vet['dia']){
							$inicio=explode(' ', $vet['inicio']); $inicio=end($inicio);
							$fim=explode(' ', $vet['fim']); $fim=end($fim);
							if($inicio <= date('H:i:s') && $fim >= date('H:i:s')) $value['ativar']=1;
						}
					}
				}
				if(!$value['ativar']) continue;
			}

			if($value['tipo'] == 'whatsapp'){
				if($value['link']) $value['link']=self::whats_link($value['link']);
				if(!$value['icone']) $value['icone']='fa fa-whatsapp';
				if(!$value['color_button']) $value['color_button']='#0DC152';
			}elseif($value['tipo'] == 'telefone'){
				// $value['title']=$value['link'];
				if($value['link']) $value['link']=self::phone_link($value['link']);
				if(!$value['icone']) $value['icone']='fa fa-phone';
				if(!$value['color_button']) $value['color_button']='#007bff';
			}elseif($value['tipo'] == 'email'){
				if($value['link']) $value['link']='mailto:'.$value['link'];
				if(!$value['icone']) $value['icone']='fa fa-envelope';
				if(!$value['color_button']) $value['color_button']='#3498DB';
			}else{
				if($value['link']) $value['link']=self::prep_url($value['link']);

				if(!$value['icone']) $value['icone']='fa fa-'.$value['tipo'];
				if($value['tipo'] == 'padrao'){
					if(!isset($value['icone']) or !$value['icone']) $value['icone']='fa fa-link';
					if(!$value['color_button']) $value['color_button']='#007bff';
				}elseif($value['tipo'] == 'facebook'){
					if(!$value['color_button']) $value['color_button']='#465791';
				}elseif($value['tipo'] == 'instagram'){
					if(!$value['color_button']) $value['color_button']='#C6379E';
				}elseif($value['tipo'] == 'twitter'){
					if(!$value['color_button']) $value['color_button']='#29A4D9';
				}elseif($value['tipo'] == 'youtube'){
					if(!$value['color_button']) $value['color_button']='#F60000';
				}elseif($value['tipo'] == 'linkedin'){
					if(!$value['color_button']) $value['color_button']='#0077B0';
				}elseif($value['tipo'] == 'vimeo'){
					if(!$value['color_button']) $value['color_button']='#19B1E3';
				}

				if(!$value['color_button']) $value['color_button']='#0DC152';
				if(!$value['color_text']) unset($value['color_text']);

				if($value['icone_externo']){
					$value['icone_externo']=Config::get('cms.storage.media.path').$value['icone_externo'];
					$value['icone_externo']=self::resize_image($value['icone_externo']);
				}

			}
			if($value['link']) $value['target']=self::target($value['link']);

			$total++;
			if($value['visivel'] == 'visible_desktop'){
				$total_desktop++;
			}elseif($value['visivel'] == 'visible_mobile'){
				$total_mobile++;
			}else{
				$total_mobile++;
				$total_desktop++;
			}

			if(isset($value['icone']) && BackendHelpers::isMiscelanious() && method_exists('\Diveramkt\Miscelanious\Classes\Functions','getIconClass')){
				$value['icone']=functions_miscelanious::getIconClass(trim(str_replace([' fa ','fa-',], ['',''], ' '.$value['icone'].' ')));
			}
			$retorno['itens'][]=$value;
		}

		$retorno['total']=$total;
		$retorno['total_mobile']=$total_mobile;
		$retorno['total_desktop']=$total_desktop;

		// echo '<pre>';
		// // print_r($settings->links_personalizados);
		// print_r($retorno['itens']);
		// echo '</pre>';
		return $retorno;

		// echo '<pre>';
		// print_r($settings);
		// echo '</pre>';
		// $this->item();
	}

	public static function veri_visible($value){
		$retorno=array();
		$retorno['mobile']=0;
		$retorno['desktop']=0;
		if($value == 'visible_desktop') $retorno['desktop']++;
		elseif($value == 'visible_mobile') $retorno['mobile']++;
		else{
			$retorno['desktop']++;
			$retorno['mobile']++;
		}
		return $retorno;
	}

	// public static function prep_url($url) {
	// 	$base = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . str_replace('//', '/', dirname($_SERVER['SCRIPT_NAME']) . '/');

	// 	if(!strpos("[".$url."]", "http://") && !strpos("[".$url."]", "https://")){
	// 		$veri=str_replace('www.','',$_SERVER['HTTP_HOST']. str_replace('//', '/', dirname($_SERVER['SCRIPT_NAME'])));
	// 		if(!strpos("[".$url."]", ".") && !strpos("[".$veri."]", "https://")){
	// 			$url='http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://www.'.str_replace(array('//','\/'),array('/','/'),$veri.'/'.$url);
	// 		}else $url='http://'.$url;
	// 	}
	// 	return str_replace('//www.','//',$url);
	// }

	public static function prep_url($url) {
		if(!strpos("[".$url."]", "http://") && !strpos("[".$url."]", "https://")){
			if(!strpos("[".$url."]", ".") && !strpos("[".url('/')."]", "https://")){
				$url=url($url);
				if(Request::server('HTTPS') == 'on') $url=str_replace('http://', 'https://', $url);
			}else $url='http://'.$url;
		}
		return $url;
	}

	public static function mobile(){
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

	public static function only_numbers($string) {
		return preg_replace("/[^0-9]/", "", $string);
	}

	public static function target($link){
		if(!strpos("[".$link."]", $_SERVER['HTTP_HOST'])) return 'target="_blank"';
		else return 'target="_parent"';
	}

	public static function phone_link($string) {
		return 'tel:+55'.preg_replace("/[^0-9]/", "", $string);
	}

	public static function whats_link($tel) {
		$iphone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
		$android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
		$palmpre = strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
		$berry = strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
		$ipod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");

		if ($iphone || $android || $palmpre || $ipod || $berry == true) {
			$link='https://api.whatsapp.com/send?phone=55';
		} else {
			$link='https://web.whatsapp.com/send?phone=55';
		}
		return $link.preg_replace("/[^0-9]/", "", $tel);
	}

	public static function resize_image($image=false, $width=30, $height=30){
		if(!$image) return false;
		// if(!in_array('ToughDeveloper\ImageResizer\Plugin', $this->class)){
		$image = new Image($image);
		$options = [];
		$options['extension']='png';
		// $options['mode']='crop';
		$options['quality']=80;
		return $image->resize($width, $height, $options);
		// }else return false;
	}

	public static function create_slug($string) {
		$table = array(
			'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
			'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
			'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
			'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
			'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
			'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
			'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
			'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', '/' => '-', ' ' => '-'
		);
		$stripped = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $string);
		return strtolower(strtr($string, $table));
	}

	public static function formPadrao(){

		$settings = Settings::instance();
		$form_enviar=new formContato();
		$mail_setting=MailSetting::instance();
		$post=post();
		// Flash::success(serialize($form_enviar));


		$infos=false;
		$infos=array();
		$infos['destino_form']='suporte@divera.com.br';
		// foreach ($settings->links_personalizados as $key => $value) {
		// 	if($value['title'] == $post['form_titulo']){
		// 		$infos=$value;
		// 	}
		// }

		// if(!$infos && isset($infos['destino_form']) && str_replace(' ', '', $infos['destino_form']) != ''){
		// 	Flash::error('Ocorreu um erro no envio, tente novamente.');
		// 	return;
		// }

		// $arquivo = "posts.txt";
		// $fp = fopen($arquivo, "w+");
		// fwrite($fp, json_encode($infos));
		// fclose($fp);
		// return;


		$post['form_titulo']='Form WhatsappFloat Float';
		$settings->mensagem_sucesso_contato="Sua mensagem foi enviada com sucesso. Obrigado!";

		$properties=$form_enviar->getProperties();
		$properties['group']=$post['form_titulo'];
		$properties['messages_success']=$settings->mensagem_sucesso_contato;
		$properties['messages_errors']='Ocorreu um erro no envio. Tente novamente por favor.';

		$properties['mail_enabled']=1;
		$properties['mail_subject']=$post['form_titulo'];


		$infos['destino_form']=str_replace(';', ',', str_replace(' ','',$infos['destino_form']));
		$infos['destino_form']=explode(',', $infos['destino_form']);
		$properties['mail_recipients']=$infos['destino_form'];

		if(isset($post['email']) && $post['email']){
			$properties['mail_replyto']='email';
			$properties['mail_resp_enabled']=1;
			$properties['mail_resp_field']='email';
			$properties['mail_resp_from']=$mail_setting->smtp_user;
			$properties['mail_resp_subject']='Recebemos sua mensagem - '.$post['form_titulo'];
		}else $properties['mail_resp_enabled']=0;
		$properties['reset_form']=1;

		$properties['inline_errors']= "disabled";
		$properties['sanitize_data']= "disabled";
		$properties['anonymize_ip']= "disabled";
		$properties['recaptcha_enabled']= 0;
		$properties['recaptcha_theme']= "light";
		$properties['recaptcha_type']= "image";
		$properties['recaptcha_size']= "normal";
		$form_enviar->setProperties($properties);

		$form_enviar->alias='faleConoscoWhastappFloat';
		$form_enviar->name='genericForm';
		$form_enviar->assetPath='/plugins/diveramkt/whatsappfloat';

		$form_enviar->inline_errors='display';

		return $form_enviar->onFormSubmit();
	}

	// public function componentDetails() {
	// 	return [
	// 		'name'        => 'martin.forms::lang.components.generic_form.name',
	// 		'description' => 'martin.forms::lang.components.generic_form.description',
	// 	];
	// }

	// public function onFormSubmit() {
	// 	echo 'teste';
	// }

}

?>