<?php
declare(strict_types=1);

namespace funyx\api\Data\Field;

use atk4\data\Field;

class Password extends Field
{
	public $default = '';

	public function __construct( $defaults = [] )
	{
		parent::__construct($defaults);
		$this->typecast = [function($value,$field){
			return password_hash($value, PASSWORD_BCRYPT);
		},function($value,$field){
			return $value;
		}];
	}
}
