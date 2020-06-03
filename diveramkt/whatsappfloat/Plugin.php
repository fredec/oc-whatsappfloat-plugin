<?php
namespace Diveramkt\Whatsappfloat;
// namespace diveramkt\whatsappfloat;

use Diveramkt\Whatsappfloat\Components\Whatsappfloat;

use System\Classes\PluginBase;
use Diveramkt\WhatsappFloat\Models\Settings;

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
		if(!isset($settings->ordem) or !$settings->ordem){
			$ordem_padrao=array();
			$id=0; $ordem_padrao[$id]['botao']='Whatsapp'; $ordem_padrao[$id]['tamanho_mobile']='12';
			$id=1; $ordem_padrao[$id]['botao']='Telefone'; $ordem_padrao[$id]['tamanho_mobile']='12';
			$id=2; $ordem_padrao[$id]['botao']='Contato'; $ordem_padrao[$id]['tamanho_mobile']='12';
			$id=3; $ordem_padrao[$id]['botao']='Ligamos'; $ordem_padrao[$id]['tamanho_mobile']='12';
			$id=4; $ordem_padrao[$id]['botao']='Form_externo'; $ordem_padrao[$id]['tamanho_mobile']='12';
			$settings->ordem=$ordem_padrao;
			$settings->save();
		}

	}

}
