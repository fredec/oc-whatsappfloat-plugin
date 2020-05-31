<?php

namespace Diveramkt\WhatsappFloat\Classes;

// use Martin\Forms\Classes\MagicForm;
use Diveramkt\WhatsappFloat\Classes\MagicForm;

class formContato extends MagicForm {

	function __construct(){}

	public function componentDetails() {
		return [
			'name'        => 'martin.forms::lang.components.generic_form.name',
			'description' => 'martin.forms::lang.components.generic_form.description',
		];
	}

	// public function onFormSubmit() {
	// 	echo 'teste';
	// }

}

?>