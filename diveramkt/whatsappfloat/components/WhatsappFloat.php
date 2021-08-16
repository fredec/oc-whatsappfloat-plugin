<?php
namespace Diveramkt\WhatsappFloat\Components;
// namespace diveramkt\whtasappfloat\components;

use Diveramkt\WhatsappFloat\Models\Settings;
// use Detection\MobileDetect as Mobile_Detect;

// use Diveramkt\WhatsappFloat\Classes\Gregwar\Image\Image;
use Diveramkt\WhatsappFloat\Classes\Image;

use Diveramkt\WhatsappFloat\Classes\formContato;
use Diveramkt\WhatsappFloat\Classes\Configsfloat;
use System\Models\MailSetting;

use stdclass;
use Config;

// use System\Models\File;

// use October\Rain\Filesystem\Definitions;
// use Martin\Forms\Classes\MagicForm;
// use Martin\Forms\Models\Record;

class WhatsappFloat extends \Cms\Classes\ComponentBase
{

	use \Diveramkt\WhatsappFloat\Traits\FileUploader;
	// use \Martin\Forms\Traits\FileUploader;

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
		$defaultFields = $settings->toArray();
		$this->class=get_declared_classes();

		if(in_array('RainLab\Translate\Plugin', $this->class) || in_array('RainLab\Translate\Classes\Translator', $this->class)){
			$locale=\RainLab\Translate\Classes\Translator::instance();
			$words=\RainLab\Translate\Models\Attribute::where('model_type','Diveramkt\Whatsappfloat\Models\Settings')->where('locale',$locale->getLocale())->first();

			if(isset($words->attribute_data)){
				$words=(array) json_decode($words->attribute_data);
				if(count($words) > 1){
					foreach ($words as $key => $value) {
						if(isset($settings->$key) && str_replace(' ', '', $value) != '') $settings->$key=$value;
					}
				}
			}
			// ->lang('fr')->
			// $user->getAttributeTranslated('name', 'fr')
		}

		if (!empty($defaultFields)) {
			if(isset($defaultFields['value'])){
				foreach ($defaultFields['value'] as $key => $defaultValue) {
					$this->settings[$key] = $defaultValue;
				}
			}
		}

		$this->settings = $settings;

		if(!isset($this->settings->legendas_mobile)) $this->settings->legendas_mobile=1;

		if(isset($uploadedFile->attributes)){
			$foto=$uploadedFile->getPath();
			$this->settings->foto_mensagem=new stdclass;
			// $this->settings->foto_mensagem=$uploadedFile->attributes;
			$this->settings->foto_mensagem->path=$foto;

			// if(!in_array('ToughDeveloper\ImageResizer\Plugin', $this->class)){
			// 	$image = new Image($foto);
			// 	$options = [];
			// 	$options['extension']='jpg';
			// 	$options['mode']='crop';
			// // $foto=$image->resize(60, 60, $options)->render();
			// 	// $this->settings['foto_mensagem']['path_resize']=$image->resize(60, 60, $options);
			// 	$this->settings->foto_mensagem->path_resize=$image->resize(60, 60, $options);
			// }
			$this->settings->foto_mensagem->path_resize=$this->resize_image($foto, 60, 60);
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
	}

	public function onRun(){
		$setin = new Settings;
		$class=get_declared_classes();
		$this->iniciar_settings();

		if($this->settings->visible_plugin && is_array($this->settings->visible_plugin) && count($this->settings->visible_plugin)){
			$this->settings->visible_plugin=' none visible_'.implode(' visible_', $this->settings->visible_plugin).' ';
		}

		if(!isset($this->settings->enabled) or !$this->settings->enabled) return;

		if($this->settings->visible_enabled_pages && !$this->veri_pages_visible()) return;

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

		// $this->settings->ordem=false;
		$ordem_padrao=array();
		$id=0; $ordem_padrao[$id]['botao']='Whatsapp'; $ordem_padrao[$id]['tamanho_mobile']='12';
		$id++; $ordem_padrao[$id]['botao']='Telefone'; $ordem_padrao[$id]['tamanho_mobile']='12';
		$id++; $ordem_padrao[$id]['botao']='Contato'; $ordem_padrao[$id]['tamanho_mobile']='12';
		$id++; $ordem_padrao[$id]['botao']='Ligamos'; $ordem_padrao[$id]['tamanho_mobile']='12';
		// $id++; $ordem_padrao[$id]['botao']='Form_externo'; $ordem_padrao[$id]['tamanho_mobile']='12';
		$id++; $ordem_padrao[$id]['botao']='Link_personalizados'; $ordem_padrao[$id]['tamanho_mobile']='12';

		if((!isset($this->settings->ordem) or !$this->settings->ordem or $this->settings->ordem[0]['botao'] == '') or (count($ordem_padrao) != count($this->settings->ordem))){
			$this->settings->ordem=$ordem_padrao;
			$this->settings->font_awesome_link='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css';
			$this->settings->save();
		}

		// /////////////BOTÕES PERSONALIZADOS
		if(isset($this->settings->links_personalizados) && is_array($this->settings->links_personalizados) && count($this->settings->links_personalizados) > 0){
			$this->links_personalizados=array();
			$retorno=Configsfloat::personalizados($this->settings);

			// print_r($retorno);

			if($retorno['total'] > 0){
				$this->quant_botoes_mobile+=$retorno['total_mobile'];
				$this->quant_botoes_desktop+=$retorno['total_desktop'];
			}
			if(isset($retorno['itens'])){
				$this->links_personalizados['botoes']=$retorno['itens'];
				$this->links_personalizados['count']=count($retorno['itens']);
			}
		}
		// /////////////BOTÕES PERSONALIZADOS

		// //////////////BOTÃO WHATSAPP
		$this->numero_whats=''; $this->telefone='';
		// $this->settings->ativar_whatsapp=1;
		if(isset($this->settings->numero) && $this->settings->numero && $this->settings->ativar_whatsapp){
			if($this->settings->formato == 4) $this->settings->color_whatsapp=false;

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
				$veri_quant=Configsfloat::veri_visible($this->settings->visivel_whatsapp);
				$this->quant_botoes_mobile+=$veri_quant['mobile']; $this->quant_botoes_desktop+=$veri_quant['desktop'];

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

				// $this->addComponent('Martin\Forms\Components\GenericForm', 'formBalaoWhats', ['formCode' => 'formIdCode'], true); 
				// $this->addComponent('Diveramkt\WhatsappFloat\Components\GenericForm', 'formBalaoWhats', ['formCode' => 'formIdCode'], true);

				// $cmsController->addComponent(
				// 	$componentInfo['class'],
				// 	$componentInfo['alias'],
				// 	$componentInfo['properties']
				// );

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
				
				$veri_quant=Configsfloat::veri_visible($this->settings->visivel_telefone);
				$this->quant_botoes_mobile+=$veri_quant['mobile']; $this->quant_botoes_desktop+=$veri_quant['desktop'];

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
				$veri_quant=Configsfloat::veri_visible($this->settings->visivel_form_contato);
				$this->quant_botoes_mobile+=$veri_quant['mobile']; $this->quant_botoes_desktop+=$veri_quant['desktop'];

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

				$veri_quant=Configsfloat::veri_visible($this->settings->visivel_form_ligamos);
				$this->quant_botoes_mobile+=$veri_quant['mobile']; $this->quant_botoes_desktop+=$veri_quant['desktop'];

				if(!isset($this->settings->legenda_ligamos) || !$this->settings->legenda_ligamos) $this->settings->legenda_ligamos='Ligamos para você';

				$this->settings->icone_ligamos=new stdclass;
				$this->settings->icone_ligamos->path='/plugins/diveramkt/whatsappfloat/assets/imagens/telLigamos.png';
				$this->settings->icone_ligamos->path_resize=$this->resize_image($this->settings->icone_ligamos->path, 30, 'auto');
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

					$veri_quant=Configsfloat::veri_visible($this->settings->visivel_form_externo);
					$this->quant_botoes_mobile+=$veri_quant['mobile']; $this->quant_botoes_desktop+=$veri_quant['desktop'];

					if(!strpos("[".$this->settings->link_form_externo."]", "@") && !strpos("[".$this->settings->link_form_externo."]", "http://") && !strpos("[".$this->settings->link_form_externo."]", "https://")) $this->settings->link_form_externo='http://'.$this->settings->link_form_externo;

					if(strpos("[".$this->settings->link_form_externo."]", "@")) $this->settings->link_form_externo_email=1;

					if(isset($this->settings->foto_icone_externo->path) && $this->settings->foto_icone_externo->path){
						$this->settings->icone_externo_path_resize=$this->resize_image($this->settings->foto_icone_externo->path);
						// if(!$this->settings->icone_externo_path_resize) $this->settings->icone_externo_path=$this->settings->foto_icone_externo->path;

						// echo '<img src="'.$this->settings->icone_externo_path_resize.'" />';
						// print_r($this->settings->icone_externo_path);
					}
					// print_r($this->settings->icone_externo);

				}else $this->settings->ativar_form_externo=0;
			}
		}else $this->settings->ativar_form_externo=0;
		// //////////////BOTÃO FORMULÁRIO LIGAMOS PARA VOCÊ

		// /////////MENSAGEM PADRÃO BALÃO
		// if($this->quant_botoes_mobile or $this->quant_botoes_desktop){

		if($this->quant_botoes_desktop){
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
		}else $this->settings->active_mensagem=0;
		// /////////MENSAGEM PADRÃO BALÃO

		// /////////////////////CONFIGURAÇÕES
		// /////////////CLASS ICONES BOTÕES
		$this->icones_fonte['numero']='fa'; $this->icones_fonte['whatsapp']='fa';
		$versao_icones='4.7.0'; if(isset($this->settings->versao_awesome)) $versao_icones=$this->settings->versao_awesome;
		if($versao_icones == '5.1.0') $this->icones_fonte['whatsapp']='fab';

		$this->icones_image['whatsapp']='/plugins/diveramkt/whatsappfloat/assets/imagens/icone-whatsapp-default.png';
		// /////////////CLASS ICONES BOTÕES

		if(!isset($this->settings->formato)) $this->settings->formato=1;
		if(!isset($this->settings->posicao_horizontal)) $this->settings->posicao_horizontal='right';
		if(!isset($this->settings->margin_bottom)) $this->settings->margin_bottom='10';
		// else $this->settings->margin_bottom.='px';
		// /////////////////////CONFIGURAÇÕES

		if($this->mobile()) $this->device = 'mobile'; else $this->device = 'desktop';

		// $detect = new Mobile_Detect;
		// $this->device = 'desktop'; if ($detect->isMobile()) $this->device = 'mobile';

		if($this->quant_botoes_mobile or $this->quant_botoes_desktop){
			$this->addCss('/plugins/diveramkt/whatsappfloat/assets/whatsapp.css','0.0.9');
			$this->addCss('/plugins/diveramkt/whatsappfloat/assets/efeitos.css','0.0.1');
			// $this->addJs('/plugins/diveramkt/whatsappfloat/assets/scripts.js','0.0.3');

			$this->uploader_enable=1; $this->uploader_multi=1;
			// $this->isMulti = $this->property('uploader_multi');
            // if($result = $this->checkUploadAction()) { return $result; }
			$this->addCss('/plugins/diveramkt/whatsappfloat/assets/upload/uploader.css','0.0.0');
			// $this->addJs('/plugins/diveramkt/whatsappfloat/assets/upload/dropzone.js','0.0.0');
			// $this->addJs('/plugins/diveramkt/whatsappfloat/assets/upload/uploader.js','0.0.0');
		}

	}

	public function onFormPadrao(){
		return Configsfloat::formPadrao();
	}

	// protected function decorateFileAttributes($file)
	// {
	// 	$file->pathUrl = $file->thumbUrl = $file->getPath();
	// 	return $file;
	// }

	// public function onRemoveAttachment()
	// {
	// 	if (($file_id = post('file_id')) && ($file = File::find($file_id))) {
	// 		$this->model->{$this->attribute}()->remove($file, $this->getSessionKey());
	// 	}
	// }

	public function veri_pages_visible(){
		$retorno=true;
		$base=Config::get('app.url');
		$cur_url=$this->currentPageUrl();
		// $cur_url=$this->prep_url($_SERVER['REQUEST_URI']);

		$link_=false;
		$links=str_replace(' ','',$this->settings->visible_links);
		if($links){
			$links = explode("\n", $links);
			foreach ($links as $key => $value) {
				if(trim($this->create_slug($this->prep_url($value))) == trim($this->create_slug($cur_url))) $link_=true;
			}
		}

		if(isset($this->settings->visible_links_pages[0]['visible_list_pages'])){
			foreach ($this->settings->visible_links_pages as $key => $value) {
				if($this->page->id == $value['visible_list_pages']) $link_=true;
			}
		}

		// $this->settings->visible_enabled_pages
		// $this->settings->visible_tipo_paginas
		// $this->settings->visible_links
		// $this->settings->visible_links_pages

		if($this->settings->visible_tipo_paginas == 0 && $link_) $retorno=false;
		elseif($this->settings->visible_tipo_paginas == 1 && !$link_) $retorno=false;

		// if($this->settings->visible_tipo_paginas == 0 && $link_)
		// elseif($this->settings->visible_tipo_paginas == 1 && $link_)

		return $retorno;
	}

	public function create_slug($string) {
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
	public function prep_url($url) {
		$base = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . str_replace('//', '/', dirname($_SERVER['SCRIPT_NAME']) . '/');

		if(!strpos("[".$url."]", "http://") && !strpos("[".$url."]", "https://")){
			$veri=str_replace('www.','',$_SERVER['HTTP_HOST']. str_replace('//', '/', dirname($_SERVER['SCRIPT_NAME'])));
			if(!strpos("[".$url."]", ".") && !strpos("[".$veri."]", "https://")){
				$url='http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://www.'.str_replace(array('//','\/'),array('/','/'),$veri.'/'.$url);
			}else $url='http://'.$url;
		}
		return str_replace('//www.','//',$url);
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
	public $numero_whats, $telefone, $links_personalizados;
	public $quant_botoes_mobile=0, $quant_botoes_desktop=0;
	public $icones_fonte, $icones_image;
	public $class='';
	public $GenericForm_;
	public $uploader_enable=1, $uploader_multi=1;

	public function onFormSubmitPersonalizados() {
		$this->iniciar_settings();
		$form_enviar=new formContato();
		$mail_setting=MailSetting::instance();

		$post=post();

		$infos=false;
		foreach ($this->settings->links_personalizados as $key => $value) {
			if($value['title'] == $post['form_titulo']){
				$infos=$value;
			}
		}

		if(!$infos && isset($infos['destino_form']) && str_replace(' ', '', $infos['destino_form']) != ''){
			Flash::error('Ocorreu um erro no envio, tente novamente.');
			return;
		}

		// $arquivo = "posts.txt";
		// $fp = fopen($arquivo, "w+");
		// fwrite($fp, json_encode($infos));
		// fclose($fp);
		// return;

		$this->settings->mensagem_sucesso_contato="Sua mensagem foi enviada com sucesso. Obrigado!";

		// $form_enviar->onRun();
		// $form_enviar->alias='';
		$form_enviar->properties['group']=$post['form_titulo'];

		// $rules=array();
		// $rules['nome'] = "required|required_if:nome,Seu nome";
		// $rules['email'] = "required|email";
		// $rules['telefone'] = "required";
		// $rules['mensagem'] = "required|min:5";
		// $form_enviar->properties['rules']=$rules;

		// $rules_messages=array();
		// $rules_messages['nome.required'] = "Informe seu nome";
		// $rules_messages['nome.required_if'] = "Informe seu nome if";
		// $rules_messages['email.required'] = "Informe seu e-mail";
		// $rules_messages['email.email'] = "E-mail inválido";
		// $rules_messages['telefone.required'] = "Informe seu telefone";
		// $rules_messages['mensagem.required'] = "Informe sua mensagem";
		// $rules_messages['mensagem.min'] = "Necessário ao mínimo 5 caracteres";
		// $form_enviar->properties['rules_messages']=$rules_messages;

		$form_enviar->properties['messages_success']=$this->settings->mensagem_sucesso_contato;
		$form_enviar->properties['messages_errors']='Ocorreu um erro no envio. Tente novamente por favor.';

		$form_enviar->properties['mail_enabled']=1;
		$form_enviar->properties['mail_subject']=$post['form_titulo'];

		// $destinos=array();
		// $destinos[]='suporte@divera.com.br';
		// $form_enviar->properties['mail_recipients']=$destinos;
		$infos['destino_form']=str_replace(';', ',', str_replace(' ','',$infos['destino_form']));
		$infos['destino_form']=explode(',', $infos['destino_form']);
		$form_enviar->properties['mail_recipients']=$infos['destino_form'];

		if(isset($post['email']) && $post['email']){
			// if(!isset($this->settings->mail_resp_assunto_contato) || !$this->settings->mail_resp_assunto_contato) $this->settings->mail_resp_assunto_contato='Recebemos sua mensagem';

			$form_enviar->properties['mail_replyto']='email';
			$form_enviar->properties['mail_resp_enabled']=1;
			$form_enviar->properties['mail_resp_field']='email';
			// $form_enviar->properties['mail_resp_from']=str_replace(' ','',$this->settings->mail_resp_from_contato);
			$form_enviar->properties['mail_resp_from']=$mail_setting->smtp_user;
			$form_enviar->properties['mail_resp_subject']='Recebemos sua mensagem - '.$post['form_titulo'];
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


	public function onFormSubmitContato() {
		$this->iniciar_settings();
		$form_enviar=new formContato();
		$mail_setting=MailSetting::instance();

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
			// $form_enviar->properties['mail_resp_from']=str_replace(' ','',$this->settings->mail_resp_from_contato);
			$form_enviar->properties['mail_resp_from']=$mail_setting->smtp_user;
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
		$mail_setting=MailSetting::instance();

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
		$mail_setting=MailSetting::instance();

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
			// $form_enviar->properties['mail_resp_from']=$destinos[0];
			$form_enviar->properties['mail_resp_from']=$mail_setting->smtp_user;
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


	public function resize_image($image=false, $width=30, $height=30, $options=false){
		if(!$image) return false;
		// if(!in_array('ToughDeveloper\ImageResizer\Plugin', $this->class)){
		$image = new Image($image);
		if(!$options){
			$options = [];
			$options['extension']='png';
			$options['mode']='crop';
		}
		// $options['quality']=80;
		return $image->resize($width, $height, $options);
		// }else return false;
	}



    /**
     * Add Twig extensions.
     *
     * @see Text extensions http://twig.sensiolabs.org/doc/extensions/text.html
     * @see Intl extensions http://twig.sensiolabs.org/doc/extensions/intl.html
     * @see Array extension http://twig.sensiolabs.org/doc/extensions/array.html
     * @see Time extension http://twig.sensiolabs.org/doc/extensions/date.html
     *
     * @return array
     */


}