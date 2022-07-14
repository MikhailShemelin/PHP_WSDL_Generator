<?php

// in the example we just use the current file url as the service/wsdl url
$protocol    = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$url_service = $protocol.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']).'/server.php';
$url_wsdl    = $url_service.'?wsdl';

$options = [
   	'soap_version' => SOAP_1_2,
   	'trace'        => true,
   	'cache_wsdl'   => WSDL_CACHE_NONE,
   	'features'     => SOAP_SINGLE_ELEMENT_ARRAYS, 
];

$client = new SoapClient($url_wsdl, $options);


echo("Service <br>\n");
echo($url_service."<br><br>\n\n");

echo("WSDL <br>\n");
echo($url_wsdl."<br><br>\n\n");

echo("Client object functions: <br>\n");
foreach($client->__getFunctions() as $fn_description) {
    echo(" - ".$fn_description." <br>\n");
}


echo("<br>\n");
echo("Results: <br><br>\n\n");

echo(" - returnMyString: <br>\n");
var_dump($client->returnMyString('The string I am sending from client'));
echo("<br><br>\n\n");

echo(" - getRandomInt: <br>\n");
var_dump($client->getRandomInt());
echo("<br><br>\n\n");

echo(" - getArrayOfRandomInts: <br>\n");
var_dump($client->getArrayOfRandomInts());
echo("<br><br>\n\n");

echo(" - getTestObject: <br>\n");
var_dump($client->getTestObject());
echo("<br><br>\n\n");

echo(" - returnMyTestObject: <br>\n");
var_dump($client->returnMyTestObject(['id'=>777, 'name'=>'Client obj name']));
echo("<br><br>\n\n");

echo(" - getArrayOfTestObjects: <br>\n");
var_dump($client->getArrayOfTestObjects());
echo("<br><br>\n\n");







