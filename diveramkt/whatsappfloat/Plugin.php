<?php
namespace Diveramkt\Whatsappfloat;
// namespace diveramkt\whatsappfloat;

use Diveramkt\Whatsappfloat\Components\Whatsappfloat;

use System\Classes\PluginBase;
use Diveramkt\WhatsappFloat\Models\Settings;
use Event;
use App;
use Request;
use Diveramkt\WhatsappFloat\Classes\Image;

class Plugin extends PluginBase
{
	public function registerComponents()
	{
		return [
			'Diveramkt\WhatsappFloat\Components\WhatsappFloat' => 'WhatsappFloat',
			// 'Diveramkt\WhatsappFloat\Components\GenericForm' => 'genericForm',
		];
		// return [
		// 	'diveramkt\whtasappfloat\components\Whatsapp' => 'Whatsapp'
		// ];
	}

	public function registerSettings()
	{
		return [
			'settings' => [
				'label'       => 'Whatsappfloat',
				'description' => 'Habilitar o link do whatsapp para enviar mensagem diretamento.',
				'category'    => 'DiveraMkt',
				'icon'        => 'icon-whatsapp',
				'class'       => 'DiveraMkt\Whatsappfloat\Models\Settings',
				'order'       => 500,
				'keywords'    => 'whatsapp link diveramkt',
				// 'permissions' => ['Whatsappfloat.manage_whatsapp']
				'permissions' => ['diveramkt.whatsappfloat.manage_whatsappfloat_setting']
			]
		];
	}

	public function registerMailTemplates() {
		return [
			'diveramkt.whatsappfloat::mail.notification' => 'Notificar quando um formulário é enviado',
			'diveramkt.whatsappfloat::mail.autoresponse' => 'Resposta automática quando um formulário é enviado',
		];
	}

	public function registerPermissions()
	{
		return [
			'diveramkt.whatsappfloat.manage_whatsappfloat_setting' => [
				'tab'   => 'diveramkt.whatsappfloat::lang.plugin.name',
				'label' => 'Acessar Configurações'
			]
		];
	}

	public function boot(){

		$class=get_declared_classes();
		if(in_array('RainLab\Translate\Plugin', $class) || in_array('RainLab\Translate\Classes\Translator', $class)){
			\Diveramkt\Whatsappfloat\Models\Settings::extend(function($model) {
				$model->implement[] = 'RainLab.Translate.Behaviors.TranslatableModel';
				$model->translatable = ['mensagem','text_padrao','legenda_whats','legenda_whats_mobile','legenda_tel','legenda_contato','mensagem_sucesso_contato','mail_resp_assunto_contato','legenda_ligamos','assunto_ligamos','mensagem_sucesso_ligamos','links_personalizados'];
			});
		}

		// new onFormSubmit();
		// $veri=new Whatsappfloat();
		// $veri->addDynamicMethod('onFormSubmit', function() {
		// });

		// \Diveramkt\WhatsappFloat\Components\WhatsappFloat::extend(function ($widget) {
		// });

		$settings = Settings::instance();
		
		$ordem_padrao=array();
		$id=0; $ordem_padrao[$id]['botao']='Whatsapp'; $ordem_padrao[$id]['tamanho_mobile']='12';
		$id++; $ordem_padrao[$id]['botao']='Telefone'; $ordem_padrao[$id]['tamanho_mobile']='12';
		$id++; $ordem_padrao[$id]['botao']='Contato'; $ordem_padrao[$id]['tamanho_mobile']='12';
		$id++; $ordem_padrao[$id]['botao']='Ligamos'; $ordem_padrao[$id]['tamanho_mobile']='12';
		// $id++; $ordem_padrao[$id]['botao']='Form_externo'; $ordem_padrao[$id]['tamanho_mobile']='12';
		$id++; $ordem_padrao[$id]['botao']='Link_personalizados'; $ordem_padrao[$id]['tamanho_mobile']='12';

		if((!isset($settings->ordem) or !$settings->ordem) or (count($ordem_padrao) != count($settings->ordem))){
			$settings->ordem=$ordem_padrao;
			$settings->font_awesome_link='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css';
			$settings->save();
		}


		if (App::runningInBackend()) {
			Event::listen('backend.page.beforeDisplay', function($controller, $action, $params) {
				$controller->addJs('/plugins/diveramkt/whatsappfloat/assets/scripts_settings.js','0.0.3');
			});
		}

	}

	public function registerFormWidgets()
	{
		return [
			'\Diveramkt\Whatsappfloat\FormWidgets\Icon' => 'select_icones',
		];
	}



	    /**
     * Returns plain PHP functions.
     *
     * @return array
     */
	    private function getPhpFunctionsNoMiscelanious()
	    {
	    	return [
	    		'create_slug' => function($string) {
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
	    		},
	    		'target' => function($link){
	    			$url = 'http' . ((Request::server('HTTPS') == 'on') ? 's' : '') . '://' . Request::server('HTTP_HOST');
	    			$link=str_replace('//www.','//',$link); $url=str_replace('//www.','//',$url);
	    			if(!strpos("[".$link."]", $url)) return 'target="_blank"';
	    			else return 'target="_parent"';
	    		},
	    	];
	    }


	    private function getPhpFunctionsNoUpload()
	    {
	    	return [
	    		'resize' => function ($image=false, $width=false, $height=false, $options=false) {
	    			if(!$image) return false;
	    			$image = new Image($image);
	    			return $image->resize($width, $height, $options);
	    		}
	    	];
	    }

	    public function registerMarkupTags()
	    {
	    	$filters = [];
        // add PHP functions
	    	if(!Helpers::isUploadsPlugin()) $filters += $this->getPhpFunctionsNoMiscelanious();
	    	if(!Helpers::isMiscelaniousPlugin()) $filters += $this->getPhpFunctionsNoUpload();

	    	return [
	    		'filters'   => $filters,
	    	];
	    }

	}
