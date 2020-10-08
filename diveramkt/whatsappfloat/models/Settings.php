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
		'foto_icone_externo' => 'System\Models\File',
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
		'tinypng_developer_key'     => 'required_if:enable_tinypng,1',
		'font_awesome_link' => 'required',
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


	public function beforeSave(){
		$this->extractFontAwesome();
	}

    /**
     * Extract Font Awesome URL icons attributes and save it into database
     *
     * @throws  ValidationException
     */
    private function extractFontAwesome(){
    	$url = $this->value['font_awesome_link'];

    	$parsed_file = $this->parseCssFile($url);
    	preg_match_all("/fa\-([\w-]+):before/", $parsed_file, $matches);
    	if(!$matches || !$matches[0]){
    		throw new ValidationException(['font_awesome_link'=>Lang::get('zakir.allfonticons::lang.icon.invalid_fa_icon_file')]);
    	}

		//Check Font Awesome version:  
    	preg_match("/Font Awesome Free (.*) by @fontawesome/", $parsed_file, $version);
    	if($version && (intval(trim($version[1])) >= 5)){
    		$font_awesome_icons = $this->getFontAwesome5($matches,trim($version[1]));
    	}else{
    		$font_awesome_icons = $this->getFontAwesomeLessThen5($matches);
    	}

    	$this->value = array_merge($this->value,["font_awesome_icons"=>json_encode($font_awesome_icons)]);
    }

    private function getFontAwesome5($matches=[],$version='')
    {
    	$font_awesome_icons = [];
    	$brands_arr = $this->getFA5RenamedBrandIcons();
    	$regular_arr = $this->getFA5RenamedRegularIcons();
    	$renamed_arr = array_merge($regular_arr,$brands_arr);
    	foreach($matches[0] as $value){
    		$icon_title = str_replace(':before','',$value);
    		if(array_key_exists($icon_title,$renamed_arr)){
    			$font_awesome_icons[] = $renamed_arr[$icon_title]." ".$icon_title;
    		}else{
    			$font_awesome_icons[] = "fa ".$icon_title;
    		}
    	}
    	return $font_awesome_icons;
    }

    private function getFontAwesomeLessThen5($matches=[])
    {
    	$font_awesome_icons = [];
    	foreach ($matches[0] as $value){
    		$font_awesome_icons[] = 'fa '.str_replace(':before','',$value);
    	}
    	return $font_awesome_icons;
    }

    public function parseCssFile($url){
    	$ch = curl_init($url);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_USERAGENT, "2Checkout PHP/0.1.0%s"); 
    	$parsed_file = curl_exec($ch);
    	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    	curl_close($ch);
    	if($httpCode != 200){
    		throw new ValidationException(['font_awesome_link'=>Lang::get('zakir.allfonticons::lang.icon.invalid_fa_icon_file')]);
    	}
    	return $parsed_file;
    }

}
