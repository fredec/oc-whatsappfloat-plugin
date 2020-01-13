<?php
namespace Diveramkt\Whatsappfloat;
// namespace diveramkt\whatsappfloat;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
	public function registerComponents()
	{
		return [
			'Diveramkt\WhatsappFloat\Components\WhatsappFloat' => 'WhatsappFloat'
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
}
