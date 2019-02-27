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

  // set url from web services
  if ($this->WSOPENMARKET_SANDBOX_MODE == 1) {
   // init soap client on sandbox mode
   $this->_soapClient = new nusoap_client($this->WSOPENMARKET_WS_SANDBOX_URL);
  } else {
   // init soap client on production mode
   $this->_soapClient = new nusoap_client($this->WSOPENMARKET_WS_PRODUCTION_ORDEN_URL);
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
  $dataInputOrden = array(
   "Recibo_Picking.DESPACHOS" => array(
    'Usuario' => "WSABBOTT",
    'Clave' => "WS.2019.ABBOTT",
    "SDTRecOutboundDelivery.SDTRecOutboundDeliveryItem" => array(
        'Nit' => "860002134-9", //siempre este
        'Documento' => "DOC1",
        'OrdenCompra' => "OC1",
        'NroPedido'   => "1",
        'FechaPedido' => "26/02/2019",
        'CodigoDestinatario' => "01125",
        'NombreDestinatario' => "Alejandro Villegas",
        'DireccionDestinatario' => "Av. 69a, Cra. 119a, #118b-11. Engativa La Faena",
        'CiudadDestinatario' => "11001",
        'TelefonoDestinatario' => "3022471141",
        'CelularDestinatario' => "3022471141",
        'FechaMinimaEntrega' => "26/02/2019",
        'FechaMaximaEntrega' => "26/02/2019",
        'Observaciones' => "Testing WS1",
        'ValorAsegurado' => "80000",
        'FechaReciboIntegracion' => "26/02/2019", //opcional
        'EstadoProceso' => "N", //siempre N
        'MensajeRecibido' => "", //vacio
        'MensajeRespueta' => "", //vacio
    ),
    "SDT_Productos.SDT_ProductosItem" => array(
        'Nit' => "860002134-9", //siempre este
        'Documento' => "DOC1",
        'OrdenCompra' => "OC1",
        'Consecutivo'   => "1",
        'CodigoProducto' => "Prod01",
        'Lote' => "", //vacio
        'UnidadesSolucitadas' => "10",
        'Bodega' => "", //vacio
        'EstadoRegistro' => "N", //siempre N
    ),
   ),
  );

  // get data
  try {
   $result = $this->_soapClient->call('DESPACHOS', $dataInputOrden);
   $this->orden = utf8_encode($result['Resultado']);
  } catch (SoapFault $fault) {
   trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
  }
  return $this->orden;
  exit();
 }

}
