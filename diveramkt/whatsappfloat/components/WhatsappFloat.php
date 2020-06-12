<?php
namespace Diveramkt\WhatsappFloat\Components;
// namespace diveramkt\whtasappfloat\components;

use Diveramkt\WhatsappFloat\Models\Settings;
// use Detection\MobileDetect as Mobile_Detect;

// use Diveramkt\WhatsappFloat\Classes\Gregwar\Image\Image;
use Diveramkt\WhatsappFloat\Classes\Image;

use Diveramkt\WhatsappFloat\Classes\formContato;

use stdclass;

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
		// $defaultFields = Settings::instance();
		$settings = Settings::instance();
		$uploadedFile = $settings->foto_mensagem;
		// return;
		$defaultFields = $settings->toArray();
		$class=get_declared_classes();

		if (!empty($defaultFields)) {
			if(isset($defaultFields['value'])){
				foreach ($defaultFields['value'] as $key => $defaultValue) {
					$this->settings[$key] = $defaultValue;
				}
			}
		}

		$this->settings = $settings;

		if(isset($uploadedFile->attributes)){
			$foto=$uploadedFile->getPath();
			$this->settings->foto_mensagem=new stdclass;
			// $this->settings->foto_mensagem=$uploadedFile->attributes;
			$this->settings->foto_mensagem->path=$foto;

			if(!in_array('ToughDeveloper\ImageResizer\Plugin', $class)){
				$image = new Image($foto);
				$options = [];
				$options['extension']='jpg';
				$options['mode']='crop';
			// $foto=$image->resize(60, 60, $options)->render();
				// $this->settings['foto_mensagem']['path_resize']=$image->resize(60, 60, $options);
				$this->settings->foto_mensagem->path_resize=$image->resize(60, 60, $options);
			}
		}

		switch($this->diasemana(date('Y-m-d'))) {
			case"0": $this->settings->dia_semana='domingo'; break;
			case"1": $this->settings->dia_semana='segunda'; break;
			case"2": $this->settings->dia_semana='terca'; break;
			case"3": $this->settings->dia_semana='quarta'; break;
			case"4": $this->settings->dia_semana='quinta'; break;
			case"5": $this->settings->dia_semana='sexta'; break;
			case"6": $this->settings->dia_semana='sabado'; break;
		}

		// $this->settings = (object)$this->settings;
	}

	public function diasemana($data, $mes=false, $dia=false) {
		if(!$mes && !$dia){
			$ano = substr($data, 0, 4); $mes = substr($data, 5, 2); $dia = substr($data, 8, 2);
		}else $ano=$data;
		$diasemana = date("w", mktime(0,0,0,$mes,$dia,$ano) );

		return $diasemana;
		// switch($diasemana) {
		// 	case"0": $diasemana = "Domingo";       break;
		// 	case"1": $diasemana = "Segunda-Feira"; break;
		// 	case"2": $diasemana = "Terça-Feira";   break;
		// 	case"3": $diasemana = "Quarta-Feira";  break;
		// 	case"4": $diasemana = "Quinta-Feira";  break;
		// 	case"5": $diasemana = "Sexta-Feira";   break;
		// 	case"6": $diasemana = "Sábado";        break;
		// }
		// return $diasemana;
	}

	public function onRun(){
		$setin = new Settings;
		$class=get_declared_classes();
		$this->iniciar_settings();

		if(!isset($this->settings->enabled) or !$this->settings->enabled) return;

		if(isset($this->settings->enabled_horarios) && $this->settings->enabled_horarios){
			$horarios='';
			switch($this->diasemana(date('Y-m-d'))) {
				case"0": $horarios=$this->settings->horarios_domingo; break;
				case"1": $horarios=$this->settings->horarios_segunda; break;
				case"2": $horarios=$this->settings->horarios_terca; break;
				case"3": $horarios=$this->settings->horarios_quarta; break;
				case"4": $horarios=$this->settings->horarios_quinta; break;
				case"5": $horarios=$this->settings->horarios_sexta; break;
				case"6": $horarios=$this->settings->horarios_sabado; break;
			}

			if(is_array($horarios) && count($horarios) > 0){
				$horario=false;
				foreach ($horarios as $key => $value) {
					$inicio=explode(' ', $value['horario_inicio']); $inicio=end($inicio);
					$fim=explode(' ', $value['horario_fim']); $fim=end($fim);
					if(date('H:i:s') >= $inicio && date('H:i:s') <= $fim) $horario=true;
				}
				if(!$horario) return;
			}else return;
		}

		if(!isset($this->settings->formato_mobile) or !$this->settings->formato_mobile) $this->settings->formato_mobile='aolado';
		if(!isset($this->settings->ordem) or !$this->settings->ordem or $this->settings->ordem[0]['botao'] == ''){
			$ordem_padrao=array();
			$id=0; $ordem_padrao[$id]['botao']='Whatsapp'; $ordem_padrao[$id]['tamanho_mobile']='12';
			$id=1; $ordem_padrao[$id]['botao']='Telefone'; $ordem_padrao[$id]['tamanho_mobile']='12';
			$id=2; $ordem_padrao[$id]['botao']='Contato'; $ordem_padrao[$id]['tamanho_mobile']='12';
			$id=3; $ordem_padrao[$id]['botao']='Ligamos'; $ordem_padrao[$id]['tamanho_mobile']='12';
			$id=4; $ordem_padrao[$id]['botao']='Form_externo'; $ordem_padrao[$id]['tamanho_mobile']='12';
			$this->settings->ordem=$ordem_padrao;
			$this->settings->save();
			$this->settings->ordem=$ordem_padrao;
		}

		// //////////////BOTÃO WHATSAPP
		$this->numero_whats=''; $this->telefone='';
		// $this->settings->ativar_whatsapp=1;
		if(isset($this->settings->numero) && $this->settings->numero && $this->settings->ativar_whatsapp){

			if($this->settings->habilitar_programacao_whatsapp){
				if(!count($this->settings->programacao_whatsapp)) $this->settings->ativar_whatsapp=0;
				else{
					$this->settings->ativar_whatsapp=0;
					foreach ($this->settings->programacao_whatsapp as $key => $value) {
						if($this->settings->ativar_whatsapp) continue;
						if($this->settings->dia_semana == $value['dia']){
							$inicio=explode(' ', $value['inicio']); $inicio=end($inicio);
							$fim=explode(' ', $value['fim']); $fim=end($fim);
							if($inicio <= date('H:i:s') && $fim >= date('H:i:s')) $this->settings->ativar_whatsapp=1;
						}
					}
				}
			}

			if($this->settings->ativar_whatsapp){
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
		}
		// //////////////BOTÃO WHATSAPP

		// //////////////BOTÃO TELEFONE
		$this->settings->legenda_telefone='';
		// $this->settings->ativar_telefone=1;
		if(isset($this->settings->tel) && $this->settings->tel && $this->settings->ativar_telefone){

			if($this->settings->habilitar_programacao_telefone){
				if(!count($this->settings->programacao_telefone)) $this->settings->ativar_telefone=0;
				else{
					$this->settings->ativar_telefone=0;
					foreach ($this->settings->programacao_telefone as $key => $value) {
						if($this->settings->ativar_telefone) continue;
						if($this->settings->dia_semana == $value['dia']){
							$inicio=explode(' ', $value['inicio']); $inicio=end($inicio);
							$fim=explode(' ', $value['fim']); $fim=end($fim);
							if($inicio <= date('H:i:s') && $fim >= date('H:i:s')) $this->settings->ativar_telefone=1;
						}
					}
				}
			}
			if($this->settings->ativar_telefone){
				$this->quant_botoes+=1;
				$this->settings->legenda_telefone=$this->settings->tel;
				$this->telefone=preg_replace("/[^0-9]/", "", $this->settings->tel);

				if(isset($this->settings->legenda_tel) && str_replace(' ', '', $this->settings->legenda_tel) != ''){
					$this->settings->legenda_telefone=$this->settings->legenda_tel;
				}
			}
		}
		// //////////////BOTÃO TELEFONE

		// //////////////BOTÃO FORMULÁRIO DE CONTATO
		if(in_array('Martin\Forms\Plugin', $class) && isset($this->settings->ativar_form_contato) && $this->settings->ativar_form_contato && isset($this->settings->destino_contato) && $this->settings->destino_contato && str_replace(' ','',$this->settings->destino_contato) != ''){

			if($this->settings->habilitar_programacao_contato){
				if(!count($this->settings->programacao_contato)) $this->settings->ativar_form_contato=0;
				else{
					$this->settings->ativar_form_contato=0;
					foreach ($this->settings->programacao_contato as $key => $value) {
						if($this->settings->ativar_form_contato) continue;
						if($this->settings->dia_semana == $value['dia']){
							$inicio=explode(' ', $value['inicio']); $inicio=end($inicio);
							$fim=explode(' ', $value['fim']); $fim=end($fim);
							if($inicio <= date('H:i:s') && $fim >= date('H:i:s')) $this->settings->ativar_form_contato=1;
						}
					}
				}
			}

			if($this->settings->ativar_form_contato){
				$this->quant_botoes+=1;
				if(!isset($this->settings->legenda_contato) || !$this->settings->legenda_contato) $this->settings->legenda_contato='Fale Conosco';
			}
		}else $this->settings->ativar_form_contato=0;
		// //////////////BOTÃO FORMULÁRIO DE CONTATO

		// //////////////BOTÃO FORMULÁRIO LIGAMOS PARA VOCÊ
		if(in_array('Martin\Forms\Plugin', $class) && isset($this->settings->ativar_form_ligamos) && $this->settings->ativar_form_ligamos && isset($this->settings->destino_ligamos) && $this->settings->destino_ligamos && str_replace(' ','',$this->settings->destino_ligamos) != ''){

			if($this->settings->habilitar_programacao_ligamos){
				if(!count($this->settings->programacao_ligamos)) $this->settings->ativar_form_ligamos=0;
				else{
					$this->settings->ativar_form_ligamos=0;
					foreach ($this->settings->programacao_ligamos as $key => $value) {
						if($this->settings->ativar_form_ligamos) continue;
						if($this->settings->dia_semana == $value['dia']){
							$inicio=explode(' ', $value['inicio']); $inicio=end($inicio);
							$fim=explode(' ', $value['fim']); $fim=end($fim);
							if($inicio <= date('H:i:s') && $fim >= date('H:i:s')) $this->settings->ativar_form_ligamos=1;
						}
					}
				}
			}

			if($this->settings->ativar_form_ligamos){			
				$this->quant_botoes+=1;
				if(!isset($this->settings->legenda_ligamos) || !$this->settings->legenda_ligamos) $this->settings->legenda_ligamos='Ligamos para você';

				$this->settings->icone_ligamos=new stdclass;
				$this->settings->icone_ligamos->path='/plugins/diveramkt/whatsappfloat/assets/imagens/tel-ligamos.png';

				if(!in_array('ToughDeveloper\ImageResizer\Plugin', $class)){
					$image = new Image($this->settings->icone_ligamos);
					$options = [];
					$options['extension']='png';
				// $options['mode']='crop';
					$this->settings->icone_ligamos->path_resize=$image->resize(30, auto, $options);
				// $this->settings->icone_ligamos->path_resize=$image->resize(30, auto);
				}
			}

		}else $this->settings->ativar_form_ligamos=0;
		// //////////////BOTÃO FORMULÁRIO LIGAMOS PARA VOCÊ

		// //////////////BOTÃO FORMULÁRIO LIGAMOS PARA VOCÊ
		if(isset($this->settings->ativar_form_externo) && $this->settings->ativar_form_externo && isset($this->settings->legenda_form_externo) && $this->settings->legenda_form_externo && isset($this->settings->link_form_externo) && $this->settings->link_form_externo){

			if($this->settings->habilitar_programacao_externo){
				if(!count($this->settings->programacao_externo)) $this->settings->ativar_form_externo=0;
				else{
					$this->settings->ativar_form_externo=0;
					foreach ($this->settings->programacao_externo as $key => $value) {
						if($this->settings->ativar_form_externo) continue;
						if($this->settings->dia_semana == $value['dia']){
							$inicio=explode(' ', $value['inicio']); $inicio=end($inicio);
							$fim=explode(' ', $value['fim']); $fim=end($fim);
							if($inicio <= date('H:i:s') && $fim >= date('H:i:s')) $this->settings->ativar_form_externo=1;
						}
					}
				}
			}

			if($this->settings->ativar_form_externo){
				if(!isset($this->settings->input_name_form_externo) || !$this->settings->input_name_form_externo) $this->settings->input_name_form_externo=false;
				if(!isset($this->settings->input_email_form_externo) || !$this->settings->input_email_form_externo) $this->settings->input_email_form_externo=false;
				if(!isset($this->settings->input_telefone_form_externo) || !$this->settings->input_telefone_form_externo) $this->settings->input_telefone_form_externo=false;

				if($this->settings->input_name_form_externo || $this->settings->input_email_form_externo || $this->settings->input_email_form_externo){
					$this->quant_botoes+=1;

					if(!strpos("[".$this->settings->link_form_externo."]", "@") && !strpos("[".$this->settings->link_form_externo."]", "http://") && !strpos("[".$this->settings->link_form_externo."]", "https://")) $this->settings->link_form_externo='http://'.$this->settings->link_form_externo;

					if(strpos("[".$this->settings->link_form_externo."]", "@")) $this->settings->link_form_externo_email=1;

				}else $this->settings->ativar_form_externo=0;
			}
		}else $this->settings->ativar_form_externo=0;
		// //////////////BOTÃO FORMULÁRIO LIGAMOS PARA VOCÊ

		// /////////MENSAGEM PADRÃO BALÃO
		if($this->settings->habilitar_programacao_mensagem){
			if(!count($this->settings->programacao_mensagem)) $this->settings->active_mensagem=0;
			else{
				$this->settings->active_mensagem=0;
				foreach ($this->settings->programacao_mensagem as $key => $value) {
					if($this->settings->active_mensagem) continue;
					if($this->settings->dia_semana == $value['dia']){
						$inicio=explode(' ', $value['inicio']); $inicio=end($inicio);
						$fim=explode(' ', $value['fim']); $fim=end($fim);
						if($inicio <= date('H:i:s') && $fim >= date('H:i:s')) $this->settings->active_mensagem=1;
					}
				}
			}
		}
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

		if($this->quant_botoes){
			$this->addCss('/plugins/diveramkt/whatsappfloat/assets/whatsapp.css');
			$this->addJs('/plugins/diveramkt/whatsappfloat/assets/scripts.js?atualizado4');
		}
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
		$this->settings->destino_contato=str_replace(';', ',', str_replace(' ','',$this->settings->destino_contato));
		$this->settings->destino_contato=explode(',', $this->settings->destino_contato);
		$form_enviar->properties['mail_recipients']=$this->settings->destino_contato;

		if(isset($this->settings->mail_resp_from_contato) && $this->settings->mail_resp_from_contato){
			if(!isset($this->settings->mail_resp_assunto_contato) || !$this->settings->mail_resp_assunto_contato) $this->settings->mail_resp_assunto_contato='Recebemos sua mensagem';

			$form_enviar->properties['mail_replyto']='email';
			$form_enviar->properties['mail_resp_enabled']=1;
			$form_enviar->properties['mail_resp_field']='email';
			$form_enviar->properties['mail_resp_from']=str_replace(' ','',$this->settings->mail_resp_from_contato);
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

		$form_enviar->alias='faleConoscoWhastappFloat';
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
		$this->settings->destino_ligamos=str_replace(';', ',', str_replace(' ','',$this->settings->destino_ligamos));
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




	public function onFormSubmitExterno() {
		$this->iniciar_settings();
		$form_enviar=new formContato();

		// if(!isset($this->settings->mensagem_sucesso_contato) || !$this->settings->mensagem_sucesso_contato) $this->settings->mensagem_sucesso_contato="Sua mensagem foi enviada com sucesso. Obrigado!";
		// if(!isset($this->settings->grupo_contato) || !$this->settings->grupo_contato) $this->settings->grupo_contato='Fale Conosco';
		// if(!isset($this->settings->assunto_contato) || !$this->settings->assunto_contato) $this->settings->assunto_contato='Contato';

		// $form_enviar->onRun();
		// $form_enviar->alias='';
		$form_enviar->properties['group']=$this->settings->legenda_form_externo;

		$rules=array();
		$rules_messages=array();

		if($this->settings->input_nome_form_externo){
			$rules[$this->settings->input_nome_form_externo] = "required|required_if:nome,Seu nome";
			$rules_messages[$this->settings->input_nome_form_externo.'.required'] = "Informe seu nome";
			$rules_messages[$this->settings->input_nome_form_externo.'.required_if'] = "Informe seu nome if";
		}

		if($this->settings->input_email_form_externo){
			$rules[$this->settings->input_email_form_externo] = "required|email";
			$rules_messages[$this->settings->input_email_form_externo.'.required'] = "Informe seu e-mail";
			$rules_messages[$this->settings->input_email_form_externo.'.email'] = "E-mail inválido";
		}

		if($this->settings->input_telefone_form_externo){
			$rules[$this->settings->input_telefone_form_externo] = "required";
			$rules_messages[$this->settings->input_telefone_form_externo.'.required'] = "Informe seu telefone";
		}
		// $rules['mensagem'] = "required|min:5";
		$form_enviar->properties['rules']=$rules;

		
		// $rules_messages['mensagem.required'] = "Informe sua mensagem";
		// $rules_messages['mensagem.min'] = "Necessário ao mínimo 5 caracteres";
		$form_enviar->properties['rules_messages']=$rules_messages;

		$form_enviar->properties['messages_success']='Sua solicitação foi envida com sucesso. Obrigado!';
		$form_enviar->properties['messages_errors']='Ocorreu um erro no envio. Tente novamente por favor.';

		$form_enviar->properties['mail_enabled']=1;
		$form_enviar->properties['mail_subject']=$this->settings->legenda_form_externo;

		// n exception has been thrown during the rendering of a template ("Array to string conversion") in "/home2/acrjfc25/public_html/novo/plugins/diveramkt/whatsappfloat/components/whatsappfloat/form_externo.htm" at line 12." on line 421 of /home2/acrjfc25/public_html/novo/vendor/twig/twig/src/Template.php

		// $destinos=array();
		// $destinos[]='suporte@divera.com.br';
		// $form_enviar->properties['mail_recipients']=$destinos;

		$destinos=str_replace(';', ',', str_replace(' ','',$this->settings->link_form_externo));
		$destinos=explode(',', $destinos);
		$form_enviar->properties['mail_recipients']=$destinos;

		if(isset($this->settings->input_email_form_externo) && $this->settings->input_email_form_externo){
			// if(!isset($this->settings->mail_resp_assunto_contato) || !$this->settings->mail_resp_assunto_contato) $this->settings->mail_resp_assunto_contato='Recebemos sua mensagem';

			$form_enviar->properties['mail_replyto']=$this->settings->input_email_form_externo;
			$form_enviar->properties['mail_resp_enabled']=1;
			$form_enviar->properties['mail_resp_field']=$this->settings->input_email_form_externo;
			$form_enviar->properties['mail_resp_from']=$destinos[0];
			$form_enviar->properties['mail_resp_subject']='Recebemos sua mensagem - '.$this->settings->legenda_form_externo;
		}else $form_enviar->properties['mail_resp_enabled']=0;
		$form_enviar->properties['reset_form']=1;

		$form_enviar->properties['inline_errors']= "disabled";
		$form_enviar->properties['sanitize_data']= "disabled";
		$form_enviar->properties['anonymize_ip']= "disabled";
		$form_enviar->properties['recaptcha_enabled']= 0;
		$form_enviar->properties['recaptcha_theme']= "light";
		$form_enviar->properties['recaptcha_type']= "image";
		$form_enviar->properties['recaptcha_size']= "normal";

		$form_enviar->alias='faleConoscoWhastappFloat';
		$form_enviar->name='genericForm';
		$form_enviar->assetPath='/plugins/diveramkt/whatsappfloat';

		$form_enviar->inline_errors='display';


		return $form_enviar->onFormSubmit();
		// $envio=$form_enviar->onFormSubmit();
		// if($envio){
		// 	echo $form_enviar->properties['messages_success'];
		// }
	}


}