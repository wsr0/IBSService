<?php
require_once 'autoload.php';
use WSDL\WSDLCreator;
//Для запросов отовсюду
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods : POST,GET,OPTIONS");
header("Access-Control-Allow-Headers : SoapAction,Origin, X-Requested-With, Content-Type, Accept");

$wsdl = new WSDL\WSDLCreator('soapServerClass', 'http://localhost:8888/soapServerClass.php');
$wsdl->setNamespace("http://localhost:8888/");

if (isset($_GET['wsdl'])) {
    $wsdl->renderWSDL();
    exit;
}
$wsdl->renderWSDLService();

$server = new SoapServer(null, array(
    'uri' => 'http://localhost:8888/soapServer.php',
    'encoding' => 'Windows-1251'
    //'encoding' => 'UTF-8'
));
$server->setClass('soapServerClass');
$server->handle();


