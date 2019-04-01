<?php
/**
 * 2007-2019 Farmalisto
 *
 *  @author    Farmalisto SA <alejandro.villegas@farmalisto.com.co>
 *  @copyright 2007-2019 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

//  http://www.openmarket.com.co/WS-Picking/arecibo_picking.aspx?wsdl
//  http://www.mauricioalpizar.com/ejemplos/nusoap/mi_ws1.php?wsdl


error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!defined('_PS_VERSION_')) {
 exit;
}

const WSOPENMARKET_PATH_LOG = _PS_ROOT_DIR_ . "/modules/wsopenmarket/log/";

class Wsopenmarket extends Module
{
 private $WSOPENMARKET_SANDBOX_MODE;
 private $WSOPENMARKET_WS_SANDBOX_URL;
 private $WSOPENMARKET_WS_PRODUCTION_ORDEN_URL;
 private $WSOPENMARKET_WS_PRODUCTION_USER;
 private $WSOPENMARKET_WS_PRODUCTION_PASSWORD;

 public function __construct()
 {
  $this->name = 'wsopenmarket';
  $this->tab = 'front_office_features';
  $this->version = '1.0.0';
  $this->author = 'Farmalisto';
  $this->need_instance = 0;

  /**
   * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
   */
  $this->bootstrap = true;

  parent::__construct();

  $this->displayName = $this->l('WS Open Market');
  $this->description = $this->l('Open Market integration module');

  $this->confirmUninstall = $this->l('Are you sure you want to unistall this module?');

  $this->ps_versions_compliancy['min'] = '1.7.0.0';


  $this->WSOPENMARKET_SANDBOX_MODE = Configuration::get('WSOPENMARKET_SANDBOX_MODE');
  $this->WSOPENMARKET_WS_SANDBOX_URL = Configuration::get('WSOPENMARKET_WS_SANDBOX_URL');
  $this->WSOPENMARKET_WS_PRODUCTION_ORDEN_URL = Configuration::get('WSOPENMARKET_WS_PRODUCTION_ORDEN_URL');
  $this->WSOPENMARKET_WS_PRODUCTION_USER = Configuration::get('WSOPENMARKET_WS_PRODUCTION_USER');
  $this->WSOPENMARKET_WS_PRODUCTION_PASSWORD = Configuration::get('WSOPENMARKET_WS_PRODUCTION_PASSWORD');

  if ($this->active && Configuration::get('wsopenmarket') == '') {
   $this->warning = $this->l('You have to configure your module');
  }

  $this->errors = array();

 }

 /**
  * Install module
  *
  * @return boolean
  */
 public function install()
 {
  if (parent::install() && $this->registerStHook() && self::createPath(WSOPENMARKET_PATH_LOG)) {

   $res = true;
   $res &= $this->createTables();
   $res &= $this->createConfiguration();
   return (bool) $res;
  }
  return false;

 }

 /**
  * Unistall module
  *
  * @return boolean
  */
 public function uninstall()
 {
  if (parent::uninstall() && $this->unregisterStHook()) {
   $res = true;
   $res &= $this->deleteTables();
   $res &= $this->deleteConfiguration();
   return (bool) $res;
  }
  return false;
 }

 /**
  * Register hooks when the module is installed
  *
  * @return boolean
  */
 private function registerStHook()
 {
  $res = true;
  $res &= $this->registerHook('header');
  $res &= $this->registerHook('backOfficeHeader');
  $res &= $this->registerHook('displayHome');
  $res &= $this->registerHook('ActionPaymentConfirmation');
  $res &= $this->registerHook('displayOrderDetail');
  return $res;
 }

 /**
  * Unregister hooks when the module is uninstalled
  *
  * @return boolean
  */
 private function unRegisterStHook()
 {
  $res = true;
  $res &= $this->unRegisterHook('header');
  $res &= $this->unRegisterHook('backOfficeHeader');
  $res &= $this->unRegisterHook('displayHome');
  $res &= $this->unRegisterHook('ActionPaymentConfirmation');
  $res &= $this->unRegisterHook('displayOrderDetail');
  return $res;
 }

 /**
  * Create tables when the module in install
  *
  * @return boolean
  */
 private function createTables()
 {

  $res = true;
  include_once dirname(__FILE__) . '/sql/install.php';
  return $res;
 }

 /**
  * Delete tables when the module is uninstall
  *
  * @return boolean
  */
 private function deleteTables()
 {

  $res = true;
  include_once dirname(__FILE__) . '/sql/uninstall.php';
  return $res;
 }

 /**
  * Create parameters of Configuration
  *
  * @return boolean
  */
 private function createConfiguration()
 {

  if (
   Configuration::updateValue('WSOPENMARKET_SANDBOX_MODE', 1) &&
   Configuration::updateValue('WSOPENMARKET_WS_SANDBOX_URL', '') &&
   Configuration::updateValue('WSOPENMARKET_WS_PRODUCTION_ORDEN_URL', '') &&
   Configuration::updateValue('WSOPENMARKET_WS_PRODUCTION_USER', '') &&
   Configuration::updateValue('WSOPENMARKET_WS_PRODUCTION_PASSWORD', '')
  ) {
   return true;
  } else {
   return false;
  }

 }

 /**
  * Delete configuration's variables when the module is un-install
  *
  * @return boolean
  */
 private function deleteConfiguration()
 {

  if (
   Configuration::deleteByName('WSOPENMARKET_SANDBOX_MODE') &&
   Configuration::deleteByName('WSOPENMARKET_WS_SANDBOX_URL') &&
   Configuration::deleteByName('WSOPENMARKET_WS_PRODUCTION_ORDEN_URL') &&
   Configuration::deleteByName('WSOPENMARKET_WS_PRODUCTION_USER') &&
   Configuration::deleteByName('WSOPENMARKET_WS_PRODUCTION_PASSWORD')
  ) {
   return true;
  } else {
   return false;
  }

 }

 /**
  * Load the configuration form
  */
 public function getContent()
 {
  /**
   * If values have been submitted in the form, process.
   */
  if (((bool) Tools::isSubmit('submitWsopenmarketModule')) == true) {
   $this->postProcess();
  }

  $this->context->smarty->assign('module_dir', $this->_path);

  $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

  return $output . $this->renderForm();
 }

 /**
  * Create the form that will be displayed in the configuration of your module.
  */
 protected function renderForm()
 {
  $helper = new HelperForm();
  $helper->show_toolbar = false;
  $helper->table = $this->table;
  $helper->module = $this;
  $helper->default_form_language = $this->context->language->id;
  $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
  $helper->title = 'WS Open Market';
  $helper->identifier = $this->identifier;
  $helper->submit_action = 'submitWsopenmarketModule';
  $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
  . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
  $helper->token = Tools::getAdminTokenLite('AdminModules');

  $helper->tpl_vars = array(
   'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
   'languages' => $this->context->controller->getLanguages(),
   'id_language' => $this->context->language->id,
  );

  return $helper->generateForm(array($this->getConfigForm(), $this->getCredentialsForm()));
 }

 /**
  * Create the structure of your form.
  */
 protected function getConfigForm()
 {
  return array(
    // Settings
   'form' => array(
    'legend' => array(
     'title' => $this->l('Settings'),
     'icon' => 'icon-cogs',
    ),
    'input' => array(
     array(
      'col' => 6,
      'type' => 'text',
      'prefix' => '<i class="icon icon-link"></i>',
      'desc' => $this->l('Enter a WSDL Orden URL Client'),
      'name' => 'WSOPENMARKET_WS_PRODUCTION_ORDEN_URL',
      'label' => $this->l('Orden WSDL URL'),
     ),
     array(
      'col' => 6,
      'type' => 'switch',
      'label' => $this->l('Sandbox Mode'),
      'name' => 'WSOPENMARKET_SANDBOX_MODE',
      'is_bool' => true,
      'desc' => $this->l('Use this module in sandbox mode'),
      'values' => array(
       array(
        'id' => 'active_on',
        'value' => true,
        'label' => $this->l('Enabled'),
       ),
       array(
        'id' => 'active_off',
        'value' => false,
        'label' => $this->l('Disabled'),
       ),
      ),
     ),
     array(
      'col' => 6,
      'type' => 'text',
      'prefix' => '<i class="icon icon-link"></i>',
      'desc' => $this->l('Enter a Sandbox WSDL url client'),
      'name' => 'WSOPENMARKET_WS_SANDBOX_URL',
      'label' => $this->l('Sandbox URL'),
     ),
    ),
    'submit' => array(
     'title' => $this->l('Save'),
    ),
   ),
  );
 }
 protected function getCredentialsForm()
 {
  return array(
    // Credentials
   'form' => array(
    'legend' => array(
     'title' => $this->l('Credentials'),
     'icon' => 'icon-key',
    ),
    'input' => array(
     array(
      'col' => 3,
      'type' => 'text',
      'prefix' => '<i class="icon icon-user"></i>',
      'desc' => $this->l('Enter a user'),
      'name' => 'WSOPENMARKET_WS_PRODUCTION_USER',
      'label' => $this->l('User'),
     ),
     array(
      'col' => 3,
      'type' => 'text',
      'prefix' => '<i class="icon icon-key"></i>',
      'desc' => $this->l('Enter a password'),
      'name' => 'WSOPENMARKET_WS_PRODUCTION_PASSWORD',
      'label' => $this->l('Password'),
     ),
    ),
    'submit' => array(
     'title' => $this->l('Save'),
    ),
   ),
  );
 }

 /**
  * Set values for the inputs.
  */
 protected function getConfigFormValues()
 {

  return array(
   'WSOPENMARKET_SANDBOX_MODE' => (int) Configuration::get('WSOPENMARKET_SANDBOX_MODE'),
   'WSOPENMARKET_WS_SANDBOX_URL' => Configuration::get('WSOPENMARKET_WS_SANDBOX_URL'),
   'WSOPENMARKET_WS_PRODUCTION_ORDEN_URL' => Configuration::get('WSOPENMARKET_WS_PRODUCTION_ORDEN_URL'),
   'WSOPENMARKET_WS_PRODUCTION_USER' => Configuration::get('WSOPENMARKET_WS_PRODUCTION_USER'),
   'WSOPENMARKET_WS_PRODUCTION_PASSWORD' => Configuration::get('WSOPENMARKET_WS_PRODUCTION_PASSWORD'),
  );
 }

 /**
  * Save form data.
  */
 protected function postProcess()
 {
  $form_values = $this->getConfigFormValues();

  foreach (array_keys($form_values) as $key) {
   Configuration::updateValue($key, Tools::getValue($key));
  }

 }

 /**
  * Error log
  *
  * @param string $text text that will be saved in the file
  * @return void Error record in file "log_errors.log"
  */
 public static function logtxt($text = "")
 {

  if (file_exists(WSOPENMARKET_PATH_LOG)) {
   $fp = fopen(_PS_ROOT_DIR_ . "/modules/wsopenmarket/log/log_errors.log", "a+");
   fwrite($fp, date('l jS \of F Y h:i:s A') . ", " . $text . "\r\n");
   fclose($fp);
   return true;
  } else {
   self::createPath(WSOPENMARKET_PATH_LOG);
  }
 }

 /**
  * Add the CSS & JavaScript files you want to be loaded in the BO.
  */
 public function hookBackOfficeHeader()
 {
  if (Tools::getValue('module_name') == $this->name) {
   $this->context->controller->addJS($this->_path . 'views/js/back.js');
   $this->context->controller->addCSS($this->_path . 'views/css/back.css');
  }
 }

 /**
  * Add the CSS & JavaScript files you want to be added on the FO.
  */
 public function hookHeader()
 {
  $this->context->controller->addJS($this->_path . '/views/js/front.js');
  $this->context->controller->addCSS($this->_path . '/views/css/front.css');
 }

 public function hookDisplayHome()
 {
  if ($this->WSOPENMARKET_SANDBOX_MODE == 1) {

   try {
    self::logtxt("probando log en hookDisplayHome() en modo Sandbox");

    require_once 'ws/orden_client.php';

    $wsOrden = new Orden();
    $orden = $wsOrden->getDataOrdenSandbox();

    // set to smarty template values
    $this->context->smarty->assign('orden', $orden);

    // Hook::exec('ActionCartSave');
    // Hook::exec('ActionPaymentConfirmation');


    return $this->display(__FILE__, 'wsopenmarketSandbox.tpl');

   } catch (Exception $e) {
    $this->setErrorMessage("Exeptions on hookDisplayHome: " . $e->getMessage());
    self::logtxt("Exeptions on hookDisplayHome: " . $e->getMessage());
   }

  } else {
    try {
      // Hook::exec('ActionCartSave');
      self::logtxt("probando log en hookDisplayHome() en modo Production");

      // require_once 'ws/orden_client.php';

      // // add input params
      // $DESPACHOS = array();
      // $DESPACHOS['Usuario'] = $this->WSOPENMARKET_WS_PRODUCTION_USER;
      // $DESPACHOS['Clave'] = $this->WSOPENMARKET_WS_PRODUCTION_PASSWORD;
      // // cabecera
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['Nit'] = '860002134-9';
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['Documento'] = '406'; // id orden
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['OrdenCompra'] = '406'; // id orden
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['NroPedido'] = '406'; // id orden
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaPedido'] = '27/03/2019';
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['HoraPedido'] = '16:58:02';
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CodigoDestinatario'] = '16355867';
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['NombreDestinatario'] = 'Alejandro Villegas';
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['DireccionDestinatario'] = 'Calle 69a, #118b-11, Engativá, Bogotá';
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CiudadDestinatario'] = '11001';
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['TelefonoDestinatario'] = '3022471141';
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CelularDestinatario'] = '3022471141';
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMinimaEntrega'] = '27/03/2019'; //fecha del pedido
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMaximaEntrega'] = '27/03/2019'; // fecha del pedido
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['Observaciones'] = ''; // siempre vacio
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['ValorAsegurado'] = 7500;
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaReciboIntegracion'] = ''; //dejar vacio
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['EstadoProceso'] = 'N'; // siempre N
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['MensajeRecibido'] = ''; // dejar vacio
      // $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['MensajeRespueta'] = ''; // dejar vacio
      // // Detalle
      // $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Nit'] = '860002134-9';
      // $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Documento'] = '406';
      // $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['OrdenCompra'] = '406';
      // $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Consecutivo'] = 1;
      // $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['CodigoProducto'] = 'PRD200';
      // $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Lote'] = ''; //dejar vacio
      // $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['UnidadesSolucitadas'] = 1;
      // $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Bodega'] = ''; //dejar vacio
      // $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['EstadoRegistro'] = 'N'; //siempre es N

      // $wsOrden = new Orden();
      // $orden = $wsOrden->getDataOrden($DESPACHOS);

      // // set to smarty template values
      // $this->context->smarty->assign('orden', $orden);
      // self::logtxt("Resultado: $orden");

      // return $this->display(__FILE__, 'wsopenmarketMessage.tpl');

    } catch (Exception $e) {
        $this->setErrorMessage("Exeptions on hookDisplayHome: " . $e->getMessage());
        self::logtxt("Exeptions on hookDisplayHome: " . $e->getMessage());
    }

  }

 }

 // hook para atajar los valores del pedido despues del pago del mismo
 public function hookActionPaymentConfirmation($params)
 {

  self::logtxt("se ejecuto ActionPaymentConfirmation Successful!!");


 }

 // hook para testear in order details
 public function hookdisplayOrderDetail($params)
 {
    self::logtxt("se ejecuto hookdisplayOrderDetail Successful!!");

  if (!$this->WSOPENMARKET_SANDBOX_MODE == 1) {
    try {

      $cart = new CartCore($params['order']->id_cart);
      $products = $cart->getProducts();
      $number_products = count($products); // para saber cuantos items tiene cada pedido y enviar el xml

      // cabecera
      $NroPedido = $params['order']->id;
      $Documento = $params['order']->id;
      $customer_firstname = $params['cookie']->__get('customer_firstname');
      $customer_lastname = $params['cookie']->__get('customer_lastname');
      $FechaHora = $params['order']->date_add;
      // formatear fecha
      $FechaPedido = substr($FechaHora, 0, 10);
      $FechaPedido = strtotime($FechaPedido);
      $FechaPedido = date('d-m-Y', $FechaPedido);
      // formatear hora
      $HoraPedido = substr($FechaHora, 11, 19);
      // obtenemos la direccion y datos del cliente
      $id_address_delivery = $params['order']->id_address_delivery;
      $address = new Address((int) $id_address_delivery);
      if (Validate::isLoadedObject($address)) {
          $FullAddress = $address->address1.' - '.$address->address2;
          $TelefonoDestinatario = $address->phone;
          $CelularDestinatario = $address->phone_mobile;
          $CiudadDes = $address->city;
      } else {
          self::logtxt("Dirección erronea, el objeto no es válido");
      }
      $CodigoDestinatario = $params['order']->id; //TODO: buscar como integrar la cedula
      $ValorAsegurado = round($params['order']->total_paid, 2);

      // obtenemos el codigo dane segun la ciudad del destinatario
      setlocale(LC_ALL, 'en_US.UTF8');
      $CiudadDes= preg_replace("/[^A-Za-z0-9 ]/", '', iconv('UTF-8', 'ASCII//TRANSLIT', $CiudadDes));
      $Ciudad = mb_strtoupper($CiudadDes);
      $CiudadDestinatario = Wsopenmarket::DaneCode($Ciudad);

      // detalle
      $OrdenCompra = $params['order']->id;

      // Armando el xml de envio:
      require_once 'ws/orden_client.php';

      // iteramos el pedido
      foreach ($products as $key => $product) {
        
        $Consecutivo = ($key+1);
        $CodigoProducto = $product['reference'];
        $UnidadesSolucitadas = $product['quantity'];

        // add input params
        $DESPACHOS = array();
        $DESPACHOS['Usuario'] = $this->WSOPENMARKET_WS_PRODUCTION_USER;
        $DESPACHOS['Clave'] = $this->WSOPENMARKET_WS_PRODUCTION_PASSWORD;
        // cabecera
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['Nit'] = '860002134-9';
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['Documento'] = $Documento; // id orden
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['OrdenCompra'] = $OrdenCompra; // id orden
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['NroPedido'] = $NroPedido; // id orden
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaPedido'] = $FechaPedido;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['HoraPedido'] = $HoraPedido;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CodigoDestinatario'] = '16355867';
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['NombreDestinatario'] = $customer_firstname.' '.$customer_lastname;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['DireccionDestinatario'] = $FullAddress;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CiudadDestinatario'] = $CiudadDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['TelefonoDestinatario'] = $TelefonoDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CelularDestinatario'] = $CelularDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMinimaEntrega'] = $FechaPedido; //fecha del pedido
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMaximaEntrega'] = $FechaPedido; // fecha del pedido
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['Observaciones'] = ''; // siempre vacio
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['ValorAsegurado'] = $ValorAsegurado;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaReciboIntegracion'] = ''; //dejar vacio
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['EstadoProceso'] = 'N'; // siempre N
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['MensajeRecibido'] = ''; // dejar vacio
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['MensajeRespueta'] = ''; // dejar vacio
        // Detalle
        $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Nit'] = '860002134-9';
        $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Documento'] = $Documento;
        $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['OrdenCompra'] = $OrdenCompra;
        $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Consecutivo'] = $Consecutivo;
        $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['CodigoProducto'] = $CodigoProducto;
        $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Lote'] = ''; //dejar vacio
        $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['UnidadesSolucitadas'] = $UnidadesSolucitadas;
        $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['Bodega'] = ''; //dejar vacio
        $DESPACHOS['Sdt_productos']['SDT_ProductosItem']['EstadoRegistro'] = 'N'; //siempre es N

        self::logtxt("Consecutivo: $Consecutivo");

        $wsOrden = new Orden();
        $orden = $wsOrden->getDataOrden($DESPACHOS);

        // set to smarty template values
        $this->context->smarty->assign('orden', $orden);
        self::logtxt("Resultado: $orden");

        // return $this->display(__FILE__, 'wsopenmarketMessage.tpl'); // only for testing

      // echo "<pre>";
      // var_dump($DESPACHOS);
      // echo "</pre>";



      } //end foreach
        

    } catch (Exception $e) {
        $this->setErrorMessage("Exeptions on hookdisplayOrderDetail: " . $e->getMessage());
        self::logtxt("Exeptions on hookdisplayOrderDetail: " . $e->getMessage());
    }
  } // end production

 }              

//  funcion para obtener el codigo dane desde un archivo json
 public function DaneCode($ciudad) {
   $CodeDanedata = file_get_contents(_PS_BASE_URL_.'/modules/wsopenmarket/lib/codigosdane.json');
      $codigosDane = json_decode($CodeDanedata, true);

      foreach ($codigosDane as $codigoDane) {
        $Ciudad = $codigoDane['ciudad'];
        if($Ciudad == $ciudad) {
          $daneCode = $codigoDane['codigo'];
        }
      }
      // self::logtxt($daneCode);
      return $daneCode;

  }

}
