# WSDLGenerator

Another one WSDL generator for PHP.  

Because I found nothing really working for my case, where an automatic generation of WSDL for SOAP service used by a specific accounting application (1С/1C/1S) having very strict requirements was necessary.

Supposed to be used together with the standard PHP SoapServer class, but can be used with other server implementations too. 

# Basics

The generator creates WSDL using a service class instance for reference. 
It relies on defined types of parameters and results of the service class (so **defining all types there is mandatory**, required PHP7.4+).

Supported simple types:
 - int 
 - float
 - string
 - bool

In addition to the simple types, the lib allows to define and use custom complex types: structures and arrays 
(it contains classes AbstractStructure and AbstractTypedArray for this appropriately).  
When custom types are necessary, they should be defined as classes extending the abstracts. 

**Structures** should be defined as classes extending **WSDL\Type\AbstractStructure** with all necessary fields defined as typed public class properties (**defining all types of every property is mandatory**).  

**Arrays** should be defined as classes extending **WSDL\Type\AbstractTypedArray** with one specific typed private static property $single_item (type of this property defines type of array elements, so **defining type for this property is mandatory**).

By default, all custom complex types get WSDL type names equal to the names of their classes (without namespace). 
If necessary, it is possible to define more specific WSDL type names by alternating the result the inherited public static function getWSDLTypeName() 

To let the SOAP service to work with defined custom types as incoming parameters, the generator also creates a classmap array. 

# Examples
service and custom types  
https://github.com/MikhailShemelin/WSDLGenerator/blob/main/src/example/service.php

server   
https://github.com/MikhailShemelin/WSDLGenerator/blob/main/src/example/server.php

client (just for demonstrating the client side work, it does not use the generator lib directly)  
https://github.com/MikhailShemelin/WSDLGenerator/blob/main/src/example/client.php
