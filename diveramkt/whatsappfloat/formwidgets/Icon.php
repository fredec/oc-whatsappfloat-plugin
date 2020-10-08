<?php namespace Diveramkt\Whatsappfloat\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Diveramkt\Whatsappfloat\Models\Settings;


/**
 * Icon Form Widget
 */
class Icon extends FormWidgetBase
{

	// use \Zakir\AllFontIcons\Traits\Utility;
    use \Diveramkt\Whatsappfloat\Traits\Utility;

    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'zakir_allfonticons';

    public $placeholder = "";

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->fillFromConfig([
            'placeholder',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('icon');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
		$this->vars['id'] = $this->getId();
		$this->vars['name'] = $this->getFieldName();
		$this->vars['placeholder'] = $this->placeholder;
		$this->vars['value'] = $this->getLoadValue();
		$this->vars['icons'] = $this->getFontIcons();
    }

    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
        $settings = Settings::instance();
        if(!empty($settings->font_awesome_link)){
	        $this->addCss($settings->font_awesome_link);
        }
        $this->addCss('css/icon.css');
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return $value;
    }

    private function getFontIcons()
    {
        $settings = Settings::instance();
        return json_decode($settings->font_awesome_icons);
    }
    
}
