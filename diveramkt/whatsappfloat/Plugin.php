<?php
namespace Diveramkt\Whatsappfloat;
// namespace diveramkt\whatsappfloat;

use Diveramkt\Whatsappfloat\Components\Whatsappfloat;

use System\Classes\PluginBase;
use Diveramkt\WhatsappFloat\Models\Settings;
use Event;
use App;

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
		$id++; $ordem_padrao[$id]['botao']='Form_externo'; $ordem_padrao[$id]['tamanho_mobile']='12';
		$id++; $ordem_padrao[$id]['botao']='Link_personalizados'; $ordem_padrao[$id]['tamanho_mobile']='12';

		if((!isset($settings->ordem) or !$settings->ordem) or (count($ordem_padrao) != count($settings->ordem))){
			$settings->ordem=$ordem_padrao;
			$settings->font_awesome_link='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css';
			$settings->save();
		}


		if (App::runningInBackend()) {
			Event::listen('backend.page.beforeDisplay', function($controller, $action, $params) {
				$controller->addJs('/plugins/diveramkt/whatsappfloat/assets/scripts_settings.js');
			});
		}

	}

	public function registerFormWidgets()
	{
		return [
			'\Diveramkt\Whatsappfloat\FormWidgets\Icon' => 'select_icones',
		];
	}

}
