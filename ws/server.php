<?php
ini_set('soap.wsdl_cache_enabled', 0);
ini_set('soap.wsdl_cache_ttl', 0);

error_reporting(E_ALL);
ini_set('display_errors', 1);
// http://localhost:82/modules/wsopenmarket/ws/server.php

class Server
{
 private $_soapServer = null;

 public function __construct()
 {
  require_once '../lib/nusoap-0.9.5/lib/nusoap.php';
  // require_once 'service.php';
  $this->_soapServer = new soap_server();
  $this->_soapServer->configureWSDL('WS Open Market', 'urn:ws_openmarket');
  $this->_soapServer->wsdl->schemaTargetNamespace = "urn:ws_openmarket";

  // Input parameters
  $this->_soapServer->wsdl->addComplexType('inPicking',
   'complexType',
   'struct',
   'all',
   '',
   array('Conf_DocFacDel' => array('name' => 'Conf_DocFacDel', 'type' => 'xsd:string'),
    'Conf_Fecha' => array('name' => 'Conf_Fecha', 'type' => 'xsd:date'),
    'Conf_CodProd' => array('name' => 'Conf_CodProd', 'type' => 'xsd:string'),
    'Conf_Lote' => array('name' => 'Conf_Lote', 'type' => 'xsd:string'),
    'Conf_Cant' => array('name' => 'Conf_Cant', 'type' => 'xsd:int'),
    'Conf_Bodega' => array('name' => 'Conf_Bodega', 'type' => 'xsd:string'))
  );

  $this->_soapServer->register(
   'pickingConfirmation', // method name
   array('inPicking' => 'tns:inPicking'), // input parameters
   array('Result' => 'xsd:Array'), // output parameters
   'urn:ws_openmarket', // namespace
   'urn:ws_openmarket#pickingConfirmation', // soapaction debe ir asociado al nombre del metodo
   'rpc', // style
   'encoded', // use
   'The next function request picking confirmation' // documentation
  );

//procesamos el webservice
  $this->_soapServer->service(file_get_contents("php://input"));
 }

 /**
  * Get data from picking
  *
  * @return return array
  */
 public function pickingConfirmation($inPicking)
 {

  $pconfirmation = array(
   'NroFactura' => $inPicking['Conf_DocFacDel'],
   'Fecha' => $inPicking['Conf_Fecha'],
   'CodProducto' => $inPicking['Conf_CodProd'],
   'Lote' => $inPicking['Conf_Lote'],
   'Cantidad' => $inPicking['Conf_Cant'],
   'Bodega' => $inPicking['Conf_Bodega'],
  );

  $pconfirmation = json_encode($pconfirmation, true);
  return array(
   'pconfirmation' => $pconfirmation,
  );

 }
}
$server = new Server();
