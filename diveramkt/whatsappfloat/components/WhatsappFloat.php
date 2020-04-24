<?php
namespace Diveramkt\WhatsappFloat\Components;
// namespace diveramkt\whtasappfloat\components;

use Diveramkt\WhatsappFloat\Models\Settings;
// use Detection\MobileDetect as Mobile_Detect;

// use Diveramkt\WhatsappFloat\Classes\Gregwar\Image\Image;
use Diveramkt\WhatsappFloat\Classes\Image;

use Diveramkt\WhatsappFloat\Classes\formContato;

class WhatsappFloat extends \Cms\Classes\ComponentBase
{
	public function componentDetails()
	{
		return [
			'name' => 'Whatsapp Float',
			'description' => 'Link para enviar mensagem diretamente no Whatsapp.'
		];
	}

	public function iniciar_settings(){
		$defaultFields = Settings::instance();
		$uploadedFile = $defaultFields->foto_mensagem;
		// return;
		$defaultFields = $defaultFields->toArray();
		$class=get_declared_classes();

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
	}

	public function onRun(){
		$setin = new Settings;
		$class=get_declared_classes();
		$this->iniciar_settings();

		// //////////////BOTÃO WHATSAPP
		$this->numero_whats=''; $this->telefone='';
		if(isset($this->settings->numero) && $this->settings->numero){
			$this->quant_botoes+=1;
			$this->numero_whats=preg_replace("/[^0-9]/", "", $this->settings->numero);

			if(!isset($this->settings->legenda_whats) || str_replace(' ', '', $this->settings->legenda_whats) == ''){
				$this->settings->legenda_whats='Atendimento via WhatsApp';
			}
			if(!isset($this->settings->legenda_whats_mobile) || str_replace(' ', '', $this->settings->legenda_whats_mobile) == ''){
				$this->settings->legenda_whats_mobile='WhatsApp';
			}
			if(!isset($this->settings->text_padrao) || str_replace(' ', '', $this->settings->text_padrao) == ''){
				$this->settings->text_padrao='';
			}
		}
		// //////////////BOTÃO WHATSAPP

		// //////////////BOTÃO TELEFONE
		$this->settings->legenda_telefone='';
		if(isset($this->settings->tel) && $this->settings->tel){
			$this->quant_botoes+=1;
			$this->settings->legenda_telefone=$this->settings->tel;
			$this->telefone=preg_replace("/[^0-9]/", "", $this->settings->tel);

			if(isset($this->settings->legenda_tel) && str_replace(' ', '', $this->settings->legenda_tel) != ''){
				$this->settings->legenda_telefone=$this->settings->legenda_tel;
			}
		}
		// //////////////BOTÃO TELEFONE

		// //////////////BOTÃO FORMULÁRIO DE CONTATO
		if(in_array('Martin\Forms\Plugin', $class) && isset($this->settings->ativar_form_contato) && $this->settings->ativar_form_contato && isset($this->settings->destino_contato) && $this->settings->destino_contato && str_replace(' ','',$this->settings->destino_contato) != ''){
			$this->quant_botoes+=1;
			if(!isset($this->settings->legenda_contato) || !$this->settings->legenda_contato) $this->settings->legenda_contato='Fale Conosco';
		}else $this->settings->ativar_form_contato=0;
		// //////////////BOTÃO FORMULÁRIO DE CONTATO

		// //////////////BOTÃO FORMULÁRIO LIGAMOS PARA VOCÊ
		if(in_array('Martin\Forms\Plugin', $class) && isset($this->settings->ativar_form_ligamos) && $this->settings->ativar_form_ligamos && isset($this->settings->destino_ligamos) && $this->settings->destino_ligamos && str_replace(' ','',$this->settings->destino_ligamos) != ''){
			$this->quant_botoes+=1;
			if(!isset($this->settings->legenda_ligamos) || !$this->settings->legenda_ligamos) $this->settings->legenda_ligamos='Ligamos para você';
		}else $this->settings->ativar_form_ligamos=0;
		// //////////////BOTÃO FORMULÁRIO LIGAMOS PARA VOCÊ

		// //////////////BOTÃO FORMULÁRIO LIGAMOS PARA VOCÊ
		if(isset($this->settings->ativar_form_externo) && $this->settings->ativar_form_externo && isset($this->settings->legenda_form_externo) && $this->settings->legenda_form_externo && isset($this->settings->link_form_externo) && $this->settings->link_form_externo){

			if(!isset($this->settings->input_name_form_externo) || !$this->settings->input_name_form_externo) $this->settings->input_name_form_externo=false;
			if(!isset($this->settings->input_email_form_externo) || !$this->settings->input_email_form_externo) $this->settings->input_email_form_externo=false;
			if(!isset($this->settings->input_telefone_form_externo) || !$this->settings->input_telefone_form_externo) $this->settings->input_telefone_form_externo=false;

			if($this->settings->input_name_form_externo || $this->settings->input_email_form_externo || $this->settings->input_email_form_externo){
				$this->quant_botoes+=1;

				if(!strpos("[".$this->settings->link_form_externo."]", "http://") && !strpos("[".$this->settings->link_form_externo."]", "https://")) $this->settings->link_form_externo='http://'.$this->settings->link_form_externo;

			}else $this->settings->ativar_form_externo=0;
		}else $this->settings->ativar_form_externo=0;
		// //////////////BOTÃO FORMULÁRIO LIGAMOS PARA VOCÊ

		// /////////MENSAGEM PADRÃO BALÃO
		if((isset($this->settings->mensagem) && str_replace(' ','',$this->settings->mensagem) == '') || !isset($this->settings->mensagem)){
			$this->settings->mensagem='Precisando de Ajuda ?';
		}
		// /////////MENSAGEM PADRÃO BALÃO

		// /////////////////////CONFIGURAÇÕES
		// /////////////CLASS ICONES BOTÕES
		$this->icones_fonte['numero']='fa'; $this->icones_fonte['whatsapp']='fa';
		$versao_icones='4.7.0'; if(isset($this->settings->versao_awesome)) $versao_icones=$this->settings->versao_awesome;
		if($versao_icones == '5.1.0') $this->icones_fonte['whatsapp']='fab';
		// /////////////CLASS ICONES BOTÕES

		if(!isset($this->settings->formato)) $this->settings->formato=1;
		if(!isset($this->settings->posicao_horizontal)) $this->settings->posicao_horizontal='right';
		if(!isset($this->settings->margin_bottom)) $this->settings->margin_bottom='10px';
		else $this->settings->margin_bottom.='px';
		// /////////////////////CONFIGURAÇÕES

		if($this->mobile()) $this->device = 'mobile'; else $this->device = 'desktop';

		// $detect = new Mobile_Detect;
		// $this->device = 'desktop'; if ($detect->isMobile()) $this->device = 'mobile';

		if($this->quant_botoes) $this->addCss('/plugins/diveramkt/whatsappfloat/assets/whatsapp.css?atualizado');
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
	public $numero_whats, $telefone;
	public $quant_botoes=0;
	public $icones_fonte;

	public $GenericForm_;


	public function onFormSubmitContato() {
		$this->iniciar_settings();
		$form_enviar=new formContato();

		if(!isset($this->settings->mensagem_sucesso_contato) || !$this->settings->mensagem_sucesso_contato) $this->settings->mensagem_sucesso_contato="Sua mensagem foi enviada com sucesso. Obrigado!";
		if(!isset($this->settings->grupo_contato) || !$this->settings->grupo_contato) $this->settings->grupo_contato='Fale Conosco';
		if(!isset($this->settings->assunto_contato) || !$this->settings->assunto_contato) $this->settings->assunto_contato='Contato';

		// $form_enviar->onRun();
		// $form_enviar->alias='';
		$form_enviar->properties['group']=$this->settings->grupo_contato;

		$rules=array();
		$rules['nome'] = "required|required_if:nome,Seu nome";
		$rules['email'] = "required|email";
		$rules['telefone'] = "required";
		$rules['mensagem'] = "required|min:5";
		$form_enviar->properties['rules']=$rules;

		$rules_messages=array();
		$rules_messages['nome.required'] = "Informe seu nome";
		$rules_messages['nome.required_if'] = "Informe seu nome if";
		$rules_messages['email.required'] = "Informe seu e-mail";
		$rules_messages['email.email'] = "E-mail inválido";
		$rules_messages['telefone.required'] = "Informe seu telefone";
		$rules_messages['mensagem.required'] = "Informe sua mensagem";
		$rules_messages['mensagem.min'] = "Necessário ao mínimo 5 caracteres";
		$form_enviar->properties['rules_messages']=$rules_messages;

		$form_enviar->properties['messages_success']=$this->settings->mensagem_sucesso_contato;
		$form_enviar->properties['messages_errors']='Ocorreu um erro no envio. Tente novamente por favor.';

		$form_enviar->properties['mail_enabled']=1;
		$form_enviar->properties['mail_subject']=$this->settings->assunto_contato;

		// $destinos=array();
		// $destinos[]='suporte@divera.com.br';
		// $form_enviar->properties['mail_recipients']=$destinos;
		$this->settings->destino_contato=str_replace(';', ',', $this->settings->destino_contato);
		$this->settings->destino_contato=explode(',', $this->settings->destino_contato);
		$form_enviar->properties['mail_recipients']=$this->settings->destino_contato;

		if(isset($this->settings->mail_resp_from_contato) && $this->settings->mail_resp_from_contato){
			if(!isset($this->settings->mail_resp_assunto_contato) || !$this->settings->mail_resp_assunto_contato) $this->settings->mail_resp_assunto_contato='Recebemos sua mensagem';

			$form_enviar->properties['mail_replyto']='email';
			$form_enviar->properties['mail_resp_enabled']=1;
			$form_enviar->properties['mail_resp_field']='email';
			$form_enviar->properties['mail_resp_from']=$this->settings->mail_resp_from_contato;
			$form_enviar->properties['mail_resp_subject']=$this->settings->mail_resp_assunto_contato;
		}else $form_enviar->properties['mail_resp_enabled']=0;
		$form_enviar->properties['reset_form']=1;

		$form_enviar->properties['inline_errors']= "disabled";
		$form_enviar->properties['sanitize_data']= "disabled";
		$form_enviar->properties['anonymize_ip']= "disabled";
		$form_enviar->properties['recaptcha_enabled']= 0;
		$form_enviar->properties['recaptcha_theme']= "light";
		$form_enviar->properties['recaptcha_type']= "image";
		$form_enviar->properties['recaptcha_size']= "normal";

		$form_enviar->alias='faleConoscoFloat';
		$form_enviar->name='genericForm';
		$form_enviar->assetPath='/plugins/diveramkt/whatsappfloat';

		$form_enviar->inline_errors='display';

		return $form_enviar->onFormSubmit();
		// $envio=$form_enviar->onFormSubmit();
		// if($envio){
		// 	echo $form_enviar->properties['messages_success'];
		// }
	}

	public function onFormSubmitLigamos() {
		$this->iniciar_settings();
		$form_enviar=new formContato();

		if(!isset($this->settings->mensagem_sucesso_ligamos) || !$this->settings->mensagem_sucesso_ligamos) $this->settings->mensagem_sucesso_ligamos='Sua solicitação foi enviada com sucesso, em breve entraremos em contato. Obrigado!';
		if(!isset($this->settings->grupo_ligamos) || !$this->settings->grupo_ligamos) $this->settings->grupo_ligamos='Ligamos para você';
		if(!isset($this->settings->assunto_ligamos) || !$this->settings->assunto_ligamos) $this->settings->assunto_ligamos='Ligamos para você';

		// $form_enviar->onRun();
		// $form_enviar->alias='';
		$form_enviar->properties['group']=$this->settings->grupo_ligamos;

		$rules=array();
		$rules['nome'] = "required";
		$rules['telefone'] = "required";
		$form_enviar->properties['rules']=$rules;

		$rules_messages=array();
		$rules_messages['nome.required'] = "Informe seu nome";
		$rules_messages['telefone.required'] = "Informe seu telefone";
		$form_enviar->properties['rules_messages']=$rules_messages;

		$form_enviar->properties['messages_success']=$this->settings->mensagem_sucesso_ligamos;
		$form_enviar->properties['messages_errors']='Ocorreu um erro no envio. Tente novamente por favor.';

		$form_enviar->properties['mail_enabled']=1;
		$form_enviar->properties['mail_subject']=$this->settings->assunto_ligamos;

		// $destinos=array();
		// $destinos[]='suporte@divera.com.br';
		$this->settings->destino_ligamos=str_replace(';', ',', $this->settings->destino_ligamos);
		$this->settings->destino_ligamos=explode(',', $this->settings->destino_ligamos);
		$form_enviar->properties['mail_recipients']=$this->settings->destino_ligamos;

		// $form_enviar->properties['mail_replyto']='email';
		$form_enviar->properties['mail_resp_enabled']=0;
		// $form_enviar->properties['mail_resp_field']='email';
		// $form_enviar->properties['mail_resp_from']="suporte@divera.com.br";
		// $form_enviar->properties['mail_resp_subject']="Recebemos sua mensagem";
		$form_enviar->properties['reset_form']=1;

		$form_enviar->properties['inline_errors']= "disabled";
		$form_enviar->properties['sanitize_data']= "disabled";
		$form_enviar->properties['anonymize_ip']= "disabled";
		$form_enviar->properties['recaptcha_enabled']= 0;
		$form_enviar->properties['recaptcha_theme']= "light";
		$form_enviar->properties['recaptcha_type']= "image";
		$form_enviar->properties['recaptcha_size']= "normal";

		$form_enviar->alias='LigamosFloat';
		$form_enviar->name='genericForm';
		$form_enviar->assetPath='/plugins/diveramkt/whatsappfloat';

		$form_enviar->inline_errors='display';

		return $form_enviar->onFormSubmit();
	}
}