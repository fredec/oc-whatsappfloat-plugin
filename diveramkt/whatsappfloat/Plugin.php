<?php namespace Diveramkt\Floatingbanner;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    	return [
    		'Diveramkt\FloatingBanner\Components\FloatPopup' => 'FloatPopup',
            'Diveramkt\FloatingBanner\Components\ExitFloatPopup' => 'ExitPopup',
    	];
    }

    public function registerPageSnippets()
    {
    	return [
    		'Diveramkt\FloatingBanner\Components\FloatPopup' => 'Popup',
            'Diveramkt\FloatingBanner\Components\ExitFloatPopup' => 'ExitPopup',
    	];
    }
}
