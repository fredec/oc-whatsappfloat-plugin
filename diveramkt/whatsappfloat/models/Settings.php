<?php namespace Diveramkt\Whatsappfloat\Models;

use Model;
use Cms\Classes\Page;
use Cms\Classes\Theme;

class Settings extends Model
{
	public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
	public $settingsCode = 'whatsapp_settings';

    // Reference to field configuration
	public $settingsFields = 'fields.yaml';

	public $attachOne = [
		'foto_mensagem' => 'System\Models\File',
	];

	protected $casts = [
		'default_offset_x' => 'integer',
		'default_offset_y' => 'integer',
		'default_quality'  => 'integer',
		'default_sharpen'  => 'integer'
	];

	public $rules = [
		'default_quality'           => 'integer|between:0,100',
		'default_sharpen'           => 'integer|between:0,100',
		'tinypng_developer_key'     => 'required_if:enable_tinypng,1'
	];
	
    // Default setting data
	public function initSettingsData()
	{
		$this->default_extension = 'auto';
		$this->default_mode = 'auto';
		$this->default_offset_x = 0;
		$this->default_offset_y = 0;
		$this->default_quality = 95;
		$this->default_sharpen = 0;
	}

	public function getIconeFonteOptions(){
		$icon=array();
		return $icon;
	}


	public function getVisibleListPagesOptions(){
		$theme = Theme::getActiveTheme();
		$currentTheme = Theme::getEditTheme();
		$allPages = Page::listInTheme($currentTheme, true);

		// $veri = Db::table($this->table)->get();
		// $pags=array();
		// foreach ($veri as $vet) { $pags[$vet->page]=true; }

		$retorno['']='Selecionar Página';
		foreach ($allPages as $pg) {
			if(isset($pags[$pg->id])) continue;
			$retorno[$pg->id]=ucfirst($pg->title);
		}

		return $retorno;
	}

}
