<?php

/**
 * @class oktFormElementInputPassword
 * @ingroup okt_classes_form
 * @brief Input type password definition.
 *
 */
namespace Okatea\Tao\Forms\Simple\Elements;

use Okatea\Tao\Forms\Simple\Element;

class InputPassword extends Element
{

	protected $aConfig = array(
		'html' => '<p><label for="{{id}}">{{label}}</label><input type="password"{{attributes}} value="{{value}}" /></p>',
		'value' => null,
		'label' => ''
	);

	protected $aAttributes = [];
}
