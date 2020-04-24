<?php
namespace Diveramkt\Whatsappfloat;
// namespace diveramkt\whatsappfloat;

use Diveramkt\Whatsappfloat\Components\Whatsappfloat;

use System\Classes\PluginBase;

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
				'permissions' => ['Whatsappfloat.manage_whatsapp']
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

	}

}
