<?php
ini_set('soap.wsdl_cache_enabled', 0);
ini_set('soap.wsdl_cache_ttl', 0);

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// http://localhost:82/modules/wsopenmarket/ws/server.php

class Server
{
 private $_soapServer = null;

 public function __construct()
 {
  require_once '../lib/nusoap-0.9.5/lib/nusoap.php';
  require_once 'service.php';
  $this->_soapServer = new soap_server();
  $this->_soapServer->configureWSDL('WS Open Market', 'urn:ws_openmarket');
  $this->_soapServer->wsdl->schemaTargetNamespace = "urn:ws_openmarket";

  // Input parameters
  $this->_soapServer->wsdl->addComplexType('inPicking',
   'complexType',
   'struct',
   'all',
   '',
   array('Token' => array('name' => 'Token', 'type' => 'xsd:string'),
    'NroPedido' => array('name' => 'NroPedido', 'type' => 'xsd:int'),
    'Fecha' => array('name' => 'Fecha', 'type' => 'xsd:string'),
    'Hora' => array('name' => 'Hora', 'type' => 'xsd:string'),
    'Estado' => array('name' => 'Estado', 'type' => 'xsd:string'))
  );

  $this->_soapServer->register(
   'Service.pickingConfirmation', // method name
   array('inPicking' => 'tns:inPicking'), // input parameters
   array('return' => 'xsd:Array'), // output parameters
   'urn:ws_openmarket', // namespace
   'urn:ws_openmarket#pickingConfirmation', // soapaction debe ir asociado al nombre del metodo
   'rpc', // style
   'encoded', // use
   'The next function request picking confirmation' // documentation
  );

//procesamos el webservice
  $this->_soapServer->service(file_get_contents("php://input"));
 }

 }
$server = new Server();
