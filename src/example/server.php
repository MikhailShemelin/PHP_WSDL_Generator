<?php

include('loader.php');
include('service.php');

// in the example we just use the current file url as the service/wsdl url
$protocol    = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$url_service = $protocol.'://'.$_SERVER['HTTP_HOST'].parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$url_wsdl    = $url_service.'?wsdl';

$service = new MyService();

$wsdl_generator = new WSDL\Generator($service, 'ServiceOfMine', $url_wsdl, $url_service);

if (isset($_GET['wsdl'])) {
	header('Content-Type: application/xml; charset=utf-8');
	echo $wsdl_generator->getXMLFormatted();
} else {
	$options = [
        'soap_version' => SOAP_1_2,
        'features'     => SOAP_SINGLE_ELEMENT_ARRAYS,
        'classmap'     => $wsdl_generator->getClassmap(), // necessary for correct inputs/requests using custom types (structures/arrays)
    ];
	
	$server = new SoapServer('data://text/plain;base64,'.base64_encode($wsdl_generator->getXMLFormatted()), $options);
	$server->setObject($service);
	$server->handle();
}
