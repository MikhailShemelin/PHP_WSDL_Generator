<?php

namespace WSDL\Type;

abstract class AbstractComplexType
{
	
	// can be overrided in childs if necessary to have custom WSDL type names
	public static function getWSDLTypeName() {
		$reflection_class = new \ReflectionClass(get_called_class());
		return $reflection_class->getShortName();
	}
	
}
