<?php

//  LIVEOPENCART: SOAP
//  Support: support@liveopencart.com / Поддержка: help@liveopencart.ru

namespace WSDL;

class Generator {
	
	protected $name;
	protected $url_service;
	protected $url_wsdl;
	protected $xml_dom;
	protected $xml_definitions;
	protected $xml_port_type;
	protected $xml_binding;
	protected $xml_types_schema;
	protected $classmap  = [];
	protected $xsd_types = [
		'int'     => 'int',
		'float'   => 'float',
		'string'  => 'string',
		'bool'    => 'Boolean',
	];
	
	public function __construct($service_class_instance, $name, $url_wsdl, $url_service)
	{
		
		$this->name        = $name;
		$this->url_service = $url_service;
		$this->url_wsdl    = $url_wsdl;
		
		$this->xml_dom = new \DOMDocument('1.0', 'UTF-8');
		
		$this->xml_definitions = $this->addChildTo($this->xml_dom, 'definitions');
		
		$this->xml_definitions->setAttribute('name', $this->name);

		$this->xml_definitions->setAttribute('xmlns', 'http://schemas.xmlsoap.org/wsdl/');
		//$this->xml->setAttribute('xmlns:xmlns:tns', $this->url_service); // xmlns:xmlns:tns gives xmlns:tns (so we duplicate the prefix)
		//$this->xml->setAttribute('xmlns:xmlns:tns', $this->url_wsdl); // xmlns:xmlns:tns gives xmlns:tns (so we duplicate the prefix)
		$this->xml_definitions->setAttribute('xmlns:tns', $this->url_service); // xmlns:xmlns:tns gives xmlns:tns (so we duplicate the prefix)
		
		$this->xml_definitions->setAttribute('xmlns:soap-enc', 'http://schemas.xmlsoap.org/soap/encoding/');
		
		$this->xml_definitions->setAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
		//$this->xml->setAttribute('xmlns:xmlns:xsd', $url_wsdl);
		$this->xml_definitions->setAttribute('xmlns:soap', 'http://schemas.xmlsoap.org/wsdl/soap/');
		//$this->xml->setAttribute('targetNamespace', $this->url_wsdl);
		$this->xml_definitions->setAttribute('targetNamespace', $this->url_service);
		//$this->xml->setAttribute('targetNamespace', 'http://schemas.xmlsoap.org/wsdl/');
		
		$this->getXMLTypesSchema(); // add types on the top
		
		$this->generateWSDLByServiceClassInstance($service_class_instance);
		
		$xml_service = $this->addChildTo($this->xml_definitions, 'service');
		$xml_service->setAttribute('name', $this->getServiceName());
		
		$xml_service_port = $this->addChildTo($xml_service, 'port');
		$xml_service_port->setAttribute('name', $this->getPortName());
		$xml_service_port->setAttribute('binding', 'tns:'.$this->getBindingName());
		
		$xml_service_port_soap_address = $this->addChildTo($xml_service_port, 'soap:address');
		$xml_service_port_soap_address->setAttribute('location', $url_service);
		
	}
	
	public function getClassmap()
	{
		return $this->classmap;
	}
	
	public function getUniqueWSDLTypeNameByClassReflection($reflection_class)
	{
		
		//if ( $reflection_class->hasProperty('wsdl_type_name') && $reflection_class->getProperty('wsdl_type_name')->getValue() ) {
		//	$type_name = $reflection_class->getProperty('wsdl_type_name')->getValue();
		//} else {
		//	$type_name = $reflection_class->getShortName();
		//	//$type_name = str_replace('\\', '_', $reflection_class->getName());
		//}
		$type_name = $reflection_class->getMethod('getWSDLTypeName')->invoke(null);
		
		$type_name_full = $type_name;
		$cnt            = 0;
		while ( isset($this->classmap[$type_name_full]) ) {
			$cnt++;
			$type_name_full = $type_name.$cnt;
		}
		return $type_name_full;
	}
	
	protected function addChildTo($parent, $child_name)
	{
		$child = $this->xml_dom->createElement($child_name);
		$parent->appendChild($child);
		return $child;
	}
	
	//public function getSimpleXML() {
	//	return $this->xml;
	//}
	
	public function getXML()
	{
		return $this->xml_dom->asXML();
	}
	
	public function getXMLFormatted()
	{
		$this->xml_dom->formatOutput = true;
		return $this->xml_dom->saveXML();
	}
	
	protected function getXMLPortType()
	{
		if (!$this->xml_port_type) {
			$this->xml_port_type = $this->addChildTo($this->xml_definitions, 'portType');
			$this->xml_port_type->setAttribute('name', $this->getPortTypeName());
		}
		return $this->xml_port_type;
	}
	
	protected function getXMLTypesSchema()
	{
		
		if ( is_null($this->xml_types_schema) ) {
			$xml_types              = $this->addChildTo($this->xml_definitions, 'types');
			$this->xml_types_schema = $this->addChildTo($xml_types, 'xsd:schema');
			$this->xml_types_schema->setAttribute('targetNamespace', $this->url_service);
			//$this->xml_types_schema->setAttribute('targetNamespace', $this->url_wsdl);
			////$this->xml_types_schema->setAttribute('xmlns', $this->url_wsdl);
			$this->xml_types_schema->setAttribute('elementFormDefault', 'qualified');
			//$this->xml_types_schema->setAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
			
		}
		return $this->xml_types_schema;
	}
	
	protected function getXMLBinding()
	{
		if (!$this->xml_binding) {
			$this->xml_binding = $this->addChildTo($this->xml_definitions, 'binding');
			$this->xml_binding->setAttribute('name', $this->getBindingName());
			$this->xml_binding->setAttribute('type', 'tns:'.$this->getPortTypeName());
			$binding_soap = $this->addChildTo($this->xml_binding, 'soap:binding');
			//$binding_soap->setAttribute('style', 'document');
			$binding_soap->setAttribute('style', 'rpc');
			$binding_soap->setAttribute('transport', 'http://schemas.xmlsoap.org/soap/http');
		}
		return $this->xml_binding;
	}
	
	protected function getPortName()
	{
		return $this->name.'_Port';
	}
	
	protected function getServiceName()
	{
		return $this->name.'_Service';
	}
	
	protected function getPortTypeName()
	{
		return $this->name.'_PortType';
	}
	
	protected function getBindingName()
	{
		return $this->name.'_Binding';
	}
	
	protected function getMessageRequestNameByMethodReflection($class_method_reflection)
	{
		return $class_method_reflection->name.'_Request';
	}
	
	protected function getMessageResponseNameByMethodReflection($class_method_reflection)
	{
		return $class_method_reflection->name.'_Response';
	}
	
	protected function getServicePublicMethodsReflections($service_instance) {
		$class_reflection        = new \ReflectionClass($service_instance);
		$class_public_methods    = $class_reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
		$parent_class_reflection = $class_reflection->getParentClass();
		$service_methods         = [];
		foreach($class_public_methods as $class_method_key => $class_method_reflection) {
			if (!$class_method_reflection->isConstructor() && !$class_method_reflection->isDestructor()) {
				if (!$parent_class_reflection || !$parent_class_reflection->hasMethod($class_method_reflection->name) ) {
					$service_methods[] = $class_method_reflection;
				}
			}
		}
		return $service_methods;
	}
	
	protected function generateWSDLByServiceClassInstance($service_instance)
	{
		
		$class_methods = $this->getServicePublicMethodsReflections($service_instance);
		foreach($class_methods as $class_method_key => $class_method_reflection) {
			$this->addMessageByMethodReflection($class_method_reflection);
			
		}
		// two loops to put all <message> tags together
		foreach($class_methods as $class_method_key => $class_method_reflection) {
			$this->addPortTypeOperationsByMethodReflection($class_method_reflection);
			$this->addBindingOperationsByMethodReflection($class_method_reflection);
		}
		
	}
	
	//protected function hasCustomType($name)
	//{
	//	// ******** implement
	//}
	
	protected function getWSDLComplexTypeNameByReflectionType($reflection_type)
	{
		// check if already defined
		$existing_type = array_search($reflection_type->getName(), $this->classmap);
		if ( $existing_type ) {
			return 'tns:'.$existing_type;
		} else {
		
			// simplify names?
			
			$reflection_class = new \ReflectionClass($reflection_type->getName());
			
			$complex_type = $this->addChildTo($this->getXMLTypesSchema(), 'xsd:complexType');
			
			$type_name = $this->getUniqueWSDLTypeNameByClassReflection($reflection_class);
			
			$this->classmap[$type_name] = $reflection_type->getName();
			$complex_type->setAttribute('name', $type_name);
			
			$sequence = $this->addChildTo($complex_type, 'xsd:sequence');
			
			if ( $reflection_class->isSubclassOf('WSDL\Type\AbstractTypedArray') ) { // array
				$element = $this->addChildTo($sequence, 'xsd:element');
				$element->setAttribute('name', 'item');
				$element->setAttribute('minOccurs', '0');
				$element->setAttribute('maxOccurs', 'unbounded');
				
				$array_item_reflection_type = $reflection_class->getProperty('single_item')->getType();
				//$array_item_reflection_type = $reflection_class->getMethod('getSingleItemReflectionType')->invoke(null);
				$element->setAttribute('type', $this->getWSDLTypeNameByReflectionType($array_item_reflection_type));
			} else {
				foreach ( $reflection_class->getProperties(\ReflectionProperty::IS_PUBLIC) as $reflection_class_property ) {
					if ( !$reflection_class_property->isStatic()  ) {
						$element = $this->addChildTo($sequence, 'xsd:element');
						$element->setAttribute('name', $reflection_class_property->getName());
						$element->setAttribute('type', $this->getWSDLTypeNameByReflectionType($reflection_class_property->getType()));
						$element->setAttribute('minOccurs', '0');
					}
				}
			}
			
			return 'tns:'.$type_name;
		}
	}
	
	protected function getWSDLTypeNameByReflectionType($reflection_type)
	{
		
		if ( $reflection_type && isset($this->xsd_types[$reflection_type->getName()])) {
			return 'xsd:'.$this->xsd_types[$reflection_type->getName()];
		
		} elseif ( !$reflection_type->isBuiltin() && class_exists($reflection_type->getName()) ) { // custom type
			return $this->getWSDLComplexTypeNameByReflectionType($reflection_type);
			
		} else { // better never happen (for 1C - 100% never)
			return 'xsd:anyType';
		}
		
	}
	
	protected function addMessageByMethodReflection($class_method_reflection)
	{
		$xml_message = $this->addChildTo($this->xml_definitions, 'message');
		$xml_message->setAttribute('name', $this->getMessageRequestNameByMethodReflection($class_method_reflection));
		
		$class_method_params = $class_method_reflection->getParameters();
		foreach($class_method_params as $class_method_param_key => $class_method_param_reflection) {
			$xml_message_part = $this->addChildTo($xml_message, 'part');
			$xml_message_part->setAttribute('name', $class_method_param_reflection->name);
			
			//$xml_message_part->setAttribute('xsi:nil', 'true');
			//$xml_message_part->setAttribute('nillable', 'true');
			
			//$xml_message_part->setAttribute('type', 'xsd:anyType');
			$xml_message_part->setAttribute('type', $this->getWSDLTypeNameByReflectionType($class_method_param_reflection->getType()));
		}
		
		$xml_message = $this->addChildTo($this->xml_definitions, 'message');
		$xml_message->setAttribute('name', $this->getMessageResponseNameByMethodReflection($class_method_reflection));
		$xml_message_part = $this->addChildTo($xml_message, 'part');
		$xml_message_part->setAttribute('name', 'Result');
		//$xml_message_part->setAttribute('type', 'xsd:anyType');
		$xml_message_part->setAttribute('type', $this->getWSDLTypeNameByReflectionType($class_method_reflection->getReturnType()));
		
	}
	
	protected function addPortTypeOperationsByMethodReflection($class_method_reflection)
	{

		$xml_port_type_operation = $this->addChildTo($this->getXMLPortType(), 'operation');
		$xml_port_type_operation->setAttribute('name', $class_method_reflection->name);
		$xml_port_type_operation_input = $this->addChildTo($xml_port_type_operation, 'input');
		$xml_port_type_operation_input->setAttribute('message', 'tns:'.$this->getMessageRequestNameByMethodReflection($class_method_reflection));
		$xml_port_type_operation_output = $this->addChildTo($xml_port_type_operation, 'output');
		$xml_port_type_operation_output->setAttribute('message', 'tns:'.$this->getMessageResponseNameByMethodReflection($class_method_reflection));
		
	}
	
	protected function addBindingOperationsByMethodReflection($class_method_reflection)
	{
		
		//$xml_binding = $this->xml->addChild('binding');
		//
		//$binding->setAttribute('name', $class_method_reflection->name.'Binding');
		//$binding->setAttribute('type', 'tns:MyPortType');
		//$binding_soap = $binding->addChild('soap:soap:binding');
		//$binding_soap->setAttribute('style', 'rpc');
		//$binding_soap->setAttribute('transport', 'http://schemas.xmlsoap.org/soap/http');
		$binding_operation = $this->addChildTo($this->getXMLBinding(), 'operation');
		$binding_operation->setAttribute('name', $class_method_reflection->name);
		
		$binding_operation_soap_operation = $this->addChildTo($binding_operation, 'soap:operation');
		$binding_operation_soap_operation->setAttribute('soapAction', $class_method_reflection->name);
		
		$binding_operation_input      = $this->addChildTo($binding_operation, 'input');
		$binding_operation_input_soap = $this->addChildTo($binding_operation_input, 'soap:body');
		$binding_operation_input_soap->setAttribute('use', 'literal');
		//$binding_operation_input_soap->setAttribute('use', 'encoded');
		//$binding_operation_input_soap->setAttribute('encodingStyle', 'http://schemas.xmlsoap.org/soap/encoding/');
		$binding_operation_input_soap->setAttribute('namespace', $this->url_service );
		
		$binding_operation_output      = $this->addChildTo($binding_operation, 'output');
		$binding_operation_output_soap = $this->addChildTo($binding_operation_output, 'soap:body');
		$binding_operation_output_soap->setAttribute('use', 'literal');
		//$binding_operation_output_soap->setAttribute('use', 'encoded');
		//$binding_operation_output_soap->setAttribute('encodingStyle', 'http://schemas.xmlsoap.org/soap/encoding/');
		$binding_operation_output_soap->setAttribute('namespace', $this->url_service );
		
	}
}
