<?php
/**
 * 2007-2019 Farmalisto
 *
 *  @author    Farmalisto SA <alejandro.villegas@farmalisto.com.co>
 *  @copyright 2007-2019 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
class Orden
{
 /**
  * $_soapClient Cliente soap
  *
  * @var NuSoap
  */
 private $_soapClient = null;

 /**
  * message respuesta WS
  *
  * @var string
  */
 private $orden;

 // init construct
 public function __construct()
 {
  $this->WSOPENMARKET_SANDBOX_MODE = Configuration::get('WSOPENMARKET_SANDBOX_MODE');
  $this->WSOPENMARKET_WS_SANDBOX_URL = Configuration::get('WSOPENMARKET_WS_SANDBOX_URL');
  $this->WSOPENMARKET_WS_PRODUCTION_ORDEN_URL = Configuration::get('WSOPENMARKET_WS_PRODUCTION_ORDEN_URL');

  require_once _PS_MODULE_DIR_ . 'wsopenmarket/lib/nusoap-0.9.5/lib/nusoap.php';
  // header("Content-Type: text/xml");


  // set url from web services
  if ($this->WSOPENMARKET_SANDBOX_MODE == 1) {
   // init soap client on sandbox mode
   $this->_soapClient = new nusoap_client($this->WSOPENMARKET_WS_SANDBOX_URL);
  } else {
   // init soap client on production mode
   $this->_soapClient = new nusoap_client($this->WSOPENMARKET_WS_PRODUCTION_ORDEN_URL);
   $this->_soapClient->debug(); //debug mode

  }
  $this->_soapClient->soap_defencoding = 'UTF-8';


 }

 /**
  * Get the value from orden and set to web service
  *
  * @return string
  */
 public function getDataOrdenSandbox()
 {
  $err = $this->_soapClient->getError();
  if ($err) {
   echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
   echo '<h2>Debug</h2><pre>' . htmlspecialchars($this->_soapClient->getDebug(), ENT_QUOTES) . '</pre>';
   exit();
  }

  // add params: only data for testing
  $dataInputPerson = array(
   "datos_persona_entrada" => array(
    'nombre' => "Alejandro Villegas",
    'email' => "alekuoshu@gmail.com",
    'telefono' => "555555469",
    'ano_nac' => 1982,
   ),
  );

  // get data
  // TODO: esperando endpoint para el consumo real y poder enviar el pedido
  try {
   $result = $this->_soapClient->call('calculo_edad', $dataInputPerson);
   $this->orden = utf8_encode($result['mensaje']);
  } catch (SoapFault $fault) {
   trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
  }
  return $this->orden;
  exit();
 }

 /**
  * Get the value from orden and set to web service
  *
  * @return string
  */
 public function getDataOrden()
 {
  $err = $this->_soapClient->getError();
  if ($err) {
   echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
   echo '<h2>Debug</h2><pre>' . htmlspecialchars($this->_soapClient->getDebug(), ENT_QUOTES) . '</pre>';
   exit();
  }

// add input params
// $DESPACHOS = array(
//   'Usuario' =>  "WSABBOTT",
//   'Clave' => "WS.2019.ABBOTT",
//   'Sdtrecoutbounddelivery' => array(
//       'SDTRecOutboundDeliveryItem' => [
//           'Nit' => "860002134-9", //siempre este
//           'Documento' => "111213",
//           'OrdenCompra' => "111213",
//           'NroPedido'   => "111213",
//           'FechaPedido' => "05/03/2019",
//           'CodigoDestinatario' => "16355867",
//           'NombreDestinatario' => "Alejandro Villegas",
//           'DireccionDestinatario' => "Engativa",
//           'CiudadDestinatario' => "11001",
//           'TelefonoDestinatario' => "3022471141",
//           'CelularDestinatario' => "3022471141",
//           'FechaMinimaEntrega' => "05/03/2019",
//           'FechaMaximaEntrega' => "05/03/2019",
//           'Observaciones' => "Prueba",
//           'ValorAsegurado' => 30000,
//           'FechaReciboIntegracion' => "", //opcional
//           'EstadoProceso' => "N", //siempre N
//           'MensajeRecibido' => "", //vacio
//           'MensajeRespueta' => "", //vacio
//       ]
//   ),
//   'Sdt_productos' => array(
//     'SDT_ProductosItem' => [
//           'Nit' => "860002134-9", //siempre este
//           'Documento' => "111213",
//           'OrdenCompra' => "111213",
//           'Consecutivo'   => 1,
//           'CodigoProducto' => "PR78910",
//           'Lote' => "", //vacio
//           'UnidadesSolucitadas' => 10,
//           'Bodega' => "", //vacio o NA
//           'EstadoRegistro' => "N", //siempre N
//     ]
//   ),
// );

$DESPACHOS = array();
$DESPACHOS['Usuario'] = 'WSABBOTT';
$DESPACHOS['Clave'] = 'WS.2019.ABBOTT';
// Cabecera
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['Nit'] = '860002134-9';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['Documento'] = '350';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['OrdenCompra'] = '350';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['NroPedido'] = '350';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaPedido'] = '08/03/2019';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CodigoDestinatario'] = '16355867';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['NombreDestinatario'] = 'Alejandro Villegas';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['DireccionDestinatario'] = 'Engativa';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CiudadDestinatario'] = '11001';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['TelefonoDestinatario'] = '3022471141';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CelularDestinatario'] = '3022471141';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMinimaEntrega'] = '08/03/2019';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMaximaEntrega'] = '08/03/2019';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['Observaciones'] = 'Prueba';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['ValorAsegurado'] = 30000;
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaReciboIntegracion'] = '';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['EstadoProceso'] = 'N';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['MensajeRecibido'] = '';
$DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['MensajeRespueta'] = '';
// Detalle
$DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Nit'] = '860002134-9';
$DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Documento'] = '350';
$DESPACHOS['Sdt_productos']['SDT_ProductosItem']['OrdenCompra'] = '350';
$DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Consecutivo'] = 1;
$DESPACHOS['Sdt_productos']['SDT_ProductosItem']['CodigoProducto'] = 'PR78910';
$DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Lote'] = '';
$DESPACHOS['Sdt_productos']['SDT_ProductosItem']['UnidadesSolucitadas'] = 10;
$DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Bodega'] = '';
$DESPACHOS['Sdt_productos']['SDT_ProductosItem']['EstadoRegistro'] = 'N';


var_dump($DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMaximaEntrega']);

  // get data
  try {
   $result = $this->_soapClient->call('DESPACHOS', $DESPACHOS, 'WSPicking');

   echo '<h2>Request</h2>';
echo '<pre>' . htmlspecialchars($this->_soapClient->request, ENT_QUOTES) . '</pre>';
echo '<h2>Response</h2>';
echo '<pre>' . htmlspecialchars($this->_soapClient->response, ENT_QUOTES) . '</pre>';
echo '<h2>Debug:</h2>';
echo '<pre>' .htmlspecialchars($this->_soapClient->debug_str, ENT_QUOTES) . '</pre>';

   echo '<br>'. utf8_encode($result);

    if ( is_array( $result ) ) {
      var_dump( $result );
      exit();
    }
    $this->orden = utf8_encode($result);
  } catch (SoapFault $fault) {
   trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
  }
   
  return $this->orden;
  exit();
 }

}












// foreach( $products as $product ) {
//   $DESPACHOS[ 'Sdt_productos' ]['SDT_ProductosItem'][] = [
//           'Nit' => "860002134-9", //siempre este
//           'Documento' => "DOC1234819",
//           'OrdenCompra' => "OC1234909",
//           'Consecutivo'   => "2",
//           'CodigoProducto' => "PSRE12560",
//           'Lote' => "", //vacio
//           'UnidadesSolucitadas' => 10,
//           'Bodega' => "", //vacio
//           'EstadoRegistro' => "N", //siempre N
//       ]
// }
// var_dump($DESPACHOS);
