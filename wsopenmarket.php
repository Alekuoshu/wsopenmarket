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


// error_reporting(E_ALL);
// ini_set('display_errors', 1);

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
 private $WSOPENMARKET_GET_ID_ORDER;

 public function __construct()
 {
  $this->name = 'wsopenmarket';
  $this->tab = 'front_office_features';
  $this->version = '1.1.0';
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
  $this->WSOPENMARKET_GET_ID_ORDER = Tools::getValue('WSOPENMARKET_GET_ID_ORDER');

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
  $res &= $this->registerHook('actionValidateOrder');
  $res &= $this->registerHook('moduleRoutes');
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
  $res &= $this->unRegisterHook('actionValidateOrder');
  $res &= $this->unRegisterHook('moduleRoutes');
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

   $id_orderM = (int)trim($this->WSOPENMARKET_GET_ID_ORDER);
      if($id_orderM){
          self::logtxt("Entered in manually mode... ID_Order: $id_orderM");
          $this->sendManuallyOrders($id_orderM);

      }
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

  return $helper->generateForm(array($this->getConfigForm(), $this->getCredentialsForm(), $this->getSendManuallyForm()));
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
     * Create the structure of send manually data.
     */
    protected function getSendManuallyForm()
    {
        return array(
            // Send manually data
        'form' => array(
            'legend' => array(
            'title' => $this->l('Send Manually Data'),
            'icon' => 'icon-terminal',
            ),
            'input' => array(
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-gears"></i>',
                'desc' => $this->l('Enter order id'),
                'name' => 'WSOPENMARKET_GET_ID_ORDER',
                'label' => $this->l('Order ID'),
            ),
            ),
            'submit' => array(
            'title' => $this->l('Send'),
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
   'WSOPENMARKET_GET_ID_ORDER' => Tools::getValue('WSOPENMARKET_GET_ID_ORDER'),
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
     * Recursively create a string of directories
     */
    public static function createPath($path) {

      if (is_dir($path))
          return true;

      $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1);
      $return = self::createPath($prev_path);
      return ($return && is_writable($prev_path)) ? mkdir($path) : false;
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
  

 }

 // hook para atajar los valores del pedido despues del pago del mismo
 public function hookActionPaymentConfirmation($params) 
 {

  self::logtxt("se ejecuto ActionPaymentConfirmation Successful!!");

  if (!$this->WSOPENMARKET_SANDBOX_MODE == 1) {
    try {

      
      $id_order = (int)$params['id_order'];
      $order = new OrderCore($id_order);
      // consultar customer email DB/////
      $db = Db::getInstance();
      $sql = 'SELECT email FROM '._DB_PREFIX_.'customer WHERE id_customer = '.$order->id_customer;
      $customerEmail = $db->getValue($sql);
      ///////////////////////////////////////////

      $itemsDetails = $this->getOrderDetails($id_order);

      // $cart = new Cart($order->id_cart);
      // $products = $cart->getProducts();

      $number_products = count($itemsDetails); // para saber cuantos items tiene cada pedido y enviar el xml
      $customers = new CustomerCore();
      $customer = $customers->getCustomersByEmail($customerEmail);

      // self::logtxt("---AQUI---");


      self::logtxt("numero de productos: $number_products");

      // cabecera
      $NroPedido = $id_order;
      $Documento = $id_order;
      $customer_firstname = $customer[0]['firstname'];
      $customer_lastname = $customer[0]['lastname'];
      self::logtxt("$customer_firstname $customer_lastname"); //testing
      
      $FechaHora = $order->date_add;
      // formatear fecha
      $FechaPedido = substr($FechaHora, 0, 10);
      $FechaPedido = strtotime($FechaPedido);
      $FechaPedido = date('d-m-Y', $FechaPedido);
      // formatear hora
      $HoraPedido = substr($FechaHora, 11, 19);
      // obtenemos la direccion y datos del cliente
      $id_address_delivery = $order->id_address_delivery;
      $address = new Address((int) $id_address_delivery);
      if (Validate::isLoadedObject($address)) {
          $FullAddress = $address->address1.' - '.$address->address2;
          $TelefonoDestinatario = $address->phone;
          $CelularDestinatario = $address->phone_mobile;
          $CodigoDestinatario = $address->dni;
          $CiudadDes = $address->city;
          $other = $address->other;
          $id_state = $address->id_state;
      } else {
          self::logtxt("Dirección erronea, el objeto no es válido");
      }
      $ValorAsegurado = round($order->total_paid, 2);

      // obtenemos el codigo dane segun la ciudad del destinatario
      setlocale(LC_ALL, 'en_US.UTF8');
      $CiudadDes= preg_replace("/[^A-Za-z0-9 ]/", '', iconv('UTF-8', 'ASCII//TRANSLIT', $CiudadDes));
      $Ciudad = mb_strtoupper($CiudadDes);
      $CiudadDestinatario = $this->DaneCode($Ciudad);

      // detalle
      $OrdenCompra = $id_order;

      // Mensaje del chekout
      // $messageCore = new MessageCore();
      // $message = $messageCore->getMessagesByOrderId($id_order);
      // $observaciones = '';
      // if(isset($message)){
      //   $observaciones = substr($message[0]['message'], 0, 200);
      // }

      // dejar en porteria
      $observaciones = '';
      if($other == '1'){
        $observaciones = "Dejar el pedido en porteria!";
      }

      // obtenemos el Estado o Departamento
      $states = new StateCore();
      $DepartamentoDestinatario = $states->getNameById($id_state);

      // Armando el xml de envio:
      require_once 'ws/orden_client.php';

      // iteramos el pedido
      foreach ($itemsDetails as $key => $product) {
        
        $Consecutivo = ($key+1);
        $CodigoProducto = trim($product['codigo_producto']);
        // replace -1 in reference before sent to open market
        $CodigoProducto = str_replace("-1", "", $CodigoProducto);
        $CodigoProducto = str_replace("-2", "", $CodigoProducto);
        $CodigoProducto = str_replace("-3", "", $CodigoProducto);
        $CodigoProducto = str_replace("-4", "", $CodigoProducto);
        $CodigoProducto = str_replace("-5", "", $CodigoProducto);
        $UnidadesSolucitadas = $product['unidades'];

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
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CodigoDestinatario'] = $CodigoDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['NombreDestinatario'] = $customer_firstname.' '.$customer_lastname;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['DireccionDestinatario'] = $FullAddress;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CiudadDestinatario'] = $CiudadDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['TelefonoDestinatario'] = $TelefonoDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CelularDestinatario'] = $CelularDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMinimaEntrega'] = $FechaPedido; //fecha del pedido
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['DepartamentoDestinatario'] = $DepartamentoDestinatario; //departamento del destinatario
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMaximaEntrega'] = $FechaPedido; // fecha del pedido
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['Observaciones'] = $observaciones; // mensaje del checkout
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

        // self::logtxt("Consecutivo: $Consecutivo");

        $res = json_encode($DESPACHOS);
        self::logtxt("Json Enviado: $res");

        $wsOrden = new Orden();
        $orden = $wsOrden->getDataOrden($DESPACHOS);
        self::logtxt("Resultado: $orden");

        if($orden == '201_Información ingresó satisfactoriamente'){

          // update status
          $result = Db::getInstance()->update('wsopenmarket', array(
            'estado' => 1,
            'date_upd' => date("Y-m-d H:i:s"),
          ), 'id_orden = '.(int)$id_order);
          $error = Db::getInstance()->getMsgError();

          if ($result == true) {
              self::logtxt("Registro actualizado en wsopenmarket con exito");
          } else {
              if ($error != '') {
                  self::logtxt($error);
              }
              self::logtxt("Hubo un error al intentar actualizar en wsopenmarket");
          }

        }

        // set to smarty template values
        // $this->context->smarty->assign('orden', $orden);
        // self::logtxt("Resultado: $orden");

        // return $this->display(__FILE__, 'wsopenmarketMessage.tpl'); // only for testing
        
      // for testing//
      // echo "<pre>";
      // var_dump($DESPACHOS);
      // echo "</pre>";

      // if($Consecutivo > 1) {
      //   self::logtxt("$order");
      // }



      } //end foreach
        

    } catch (Exception $e) {
        self::logtxt("Exeptions on hookActionPaymentConfirmation: " . $e->getMessage());
    }
  } // end production
  else {
    self::logtxt("Modo Sandbox!");

    try {

      
      $id_order = (int)$params['id_order'];
      $order = new OrderCore($id_order);
      // consultar customer email DB/////
      $db = Db::getInstance();
      $sql = 'SELECT email FROM '._DB_PREFIX_.'customer WHERE id_customer = '.$order->id_customer;
      $customerEmail = $db->getValue($sql);
      ///////////////////////////////////////////

      $itemsDetails = $this->getOrderDetails($id_order);

      // $cart = new Cart($order->id_cart);
      // $products = $cart->getProducts();

      $number_products = count($itemsDetails); // para saber cuantos items tiene cada pedido y enviar el xml
      $customers = new CustomerCore();
      $customer = $customers->getCustomersByEmail($customerEmail);

      // self::logtxt("---AQUI---");


      self::logtxt("numero de productos: $number_products");

      // cabecera
      $NroPedido = $id_order;
      $Documento = $id_order;
      $customer_firstname = $customer[0]['firstname'];
      $customer_lastname = $customer[0]['lastname'];
      self::logtxt("$customer_firstname $customer_lastname"); //testing
      
      $FechaHora = $order->date_add;
      // formatear fecha
      $FechaPedido = substr($FechaHora, 0, 10);
      $FechaPedido = strtotime($FechaPedido);
      $FechaPedido = date('d-m-Y', $FechaPedido);
      // formatear hora
      $HoraPedido = substr($FechaHora, 11, 19);
      // obtenemos la direccion y datos del cliente
      $id_address_delivery = $order->id_address_delivery;
      $address = new Address((int) $id_address_delivery);
      if (Validate::isLoadedObject($address)) {
          $FullAddress = $address->address1.' - '.$address->address2;
          $TelefonoDestinatario = $address->phone;
          $CelularDestinatario = $address->phone_mobile;
          $CodigoDestinatario = $address->dni;
          $CiudadDes = $address->city;
          $other = $address->other;
          $id_state = $address->id_state;
      } else {
          self::logtxt("Dirección erronea, el objeto no es válido");
      }
      $ValorAsegurado = round($order->total_paid, 2);

      // obtenemos el codigo dane segun la ciudad del destinatario
      setlocale(LC_ALL, 'en_US.UTF8');
      $CiudadDes= preg_replace("/[^A-Za-z0-9 ]/", '', iconv('UTF-8', 'ASCII//TRANSLIT', $CiudadDes));
      $Ciudad = mb_strtoupper($CiudadDes);
      $CiudadDestinatario = $this->DaneCode($Ciudad);

      // detalle
      $OrdenCompra = $id_order;

      // Mensaje del chekout
      // $messageCore = new MessageCore();
      // $message = $messageCore->getMessagesByOrderId($id_order);
      // $observaciones = '';
      // if(isset($message)){
      //   $observaciones = substr($message[0]['message'], 0, 200);
      // }

      // dejar en porteria
      $observaciones = '';
      if($other == '1'){
        $observaciones = "Dejar el pedido en porteria!";
      }

      // obtenemos el Estado o Departamento
      $states = new StateCore();
      $DepartamentoDestinatario = $states->getNameById($id_state);

      // Armando el xml de envio:
      require_once 'ws/orden_client.php';

      // iteramos el pedido
      foreach ($itemsDetails as $key => $product) {
        
        $Consecutivo = ($key+1);
        $CodigoProducto = trim($product['codigo_producto']);
        // replace -1 in reference before sent to open market
        $CodigoProducto = str_replace("-1", "", $CodigoProducto);
        $CodigoProducto = str_replace("-2", "", $CodigoProducto);
        $CodigoProducto = str_replace("-3", "", $CodigoProducto);
        $CodigoProducto = str_replace("-4", "", $CodigoProducto);
        $CodigoProducto = str_replace("-5", "", $CodigoProducto);
        $UnidadesSolucitadas = $product['unidades'];

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
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CodigoDestinatario'] = $CodigoDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['NombreDestinatario'] = $customer_firstname.' '.$customer_lastname;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['DireccionDestinatario'] = $FullAddress;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CiudadDestinatario'] = $CiudadDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['TelefonoDestinatario'] = $TelefonoDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CelularDestinatario'] = $CelularDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMinimaEntrega'] = $FechaPedido; //fecha del pedido
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['DepartamentoDestinatario'] = $DepartamentoDestinatario; //departamento del destinatario
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMaximaEntrega'] = $FechaPedido; // fecha del pedido
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['Observaciones'] = $observaciones; // mensaje del checkout
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

        // self::logtxt("Consecutivo: $Consecutivo");

        $res = json_encode($DESPACHOS);
        self::logtxt("Json Enviado: $res");

        // $wsOrden = new Orden();
        // $orden = $wsOrden->getDataOrden($DESPACHOS);
        // self::logtxt("Resultado: $orden");

        // if($orden == '201_Información ingresó satisfactoriamente'){

        //   // update status
        //   $result = Db::getInstance()->update('wsopenmarket', array(
        //     'estado' => 1,
        //     'date_upd' => date("Y-m-d H:i:s"),
        //   ), 'id_orden = '.(int)$id_order);
        //   $error = Db::getInstance()->getMsgError();

        //   if ($result == true) {
        //       self::logtxt("Registro actualizado en wsopenmarket con exito");
        //   } else {
        //       if ($error != '') {
        //           self::logtxt($error);
        //       }
        //       self::logtxt("Hubo un error al intentar actualizar en wsopenmarket");
        //   }

        // }


      // if($Consecutivo > 1) {
      //   self::logtxt("$order");
      // }



      } //end foreach
        

    } catch (Exception $e) {
        self::logtxt("Exeptions on hookActionPaymentConfirmation: " . $e->getMessage());
    }


  } //end sandbox


 }


 // enviar data manualmente
 public function sendManuallyOrders($id_order)
 {

  self::logtxt("se ejecuto sendManuallyOrders Successful!!");

  if (!$this->WSOPENMARKET_SANDBOX_MODE == 1) {
    try {

      
      $id_order = (int)trim($this->WSOPENMARKET_GET_ID_ORDER);
      $order = new OrderCore($id_order);
      // consultar customer email DB/////
      $db = Db::getInstance();
      $sql = 'SELECT email FROM '._DB_PREFIX_.'customer WHERE id_customer = '.$order->id_customer;
      $customerEmail = $db->getValue($sql);
      ///////////////////////////////////////////

      $itemsDetails = $this->getOrderDetails($id_order);

      // si existen datos en la db wsopenmarket
      if($itemsDetails){
        $products = $itemsDetails;
      }else{
        // sino entonces se buscan del carrito normalmente
        $cart = new Cart($order->id_cart);
        $products = $cart->getProducts();
      }

      $number_products = count($products); // para saber cuantos items tiene cada pedido y enviar el xml
      $customers = new CustomerCore();
      $customer = $customers->getCustomersByEmail($customerEmail);

      // self::logtxt("---AQUI---");


      self::logtxt("numero de productos: $number_products");

      // cabecera
      $NroPedido = $id_order;
      $Documento = $id_order;
      $customer_firstname = $customer[0]['firstname'];
      $customer_lastname = $customer[0]['lastname'];
      self::logtxt("$customer_firstname $customer_lastname"); //testing
      
      $FechaHora = $order->date_add;
      // formatear fecha
      $FechaPedido = substr($FechaHora, 0, 10);
      $FechaPedido = strtotime($FechaPedido);
      $FechaPedido = date('d-m-Y', $FechaPedido);
      // formatear hora
      $HoraPedido = substr($FechaHora, 11, 19);
      // obtenemos la direccion y datos del cliente
      $id_address_delivery = $order->id_address_delivery;
      $address = new Address((int) $id_address_delivery);
      if (Validate::isLoadedObject($address)) {
          $FullAddress = $address->address1.' - '.$address->address2;
          $TelefonoDestinatario = $address->phone;
          $CelularDestinatario = $address->phone_mobile;
          $CodigoDestinatario = $address->dni;
          $CiudadDes = $address->city;
          $other = $address->other;
          $id_state = $address->id_state;
      } else {
          self::logtxt("Dirección erronea, el objeto no es válido");
      }
      $ValorAsegurado = round($order->total_paid, 2);

      // obtenemos el codigo dane segun la ciudad del destinatario
      setlocale(LC_ALL, 'en_US.UTF8');
      $CiudadDes= preg_replace("/[^A-Za-z0-9 ]/", '', iconv('UTF-8', 'ASCII//TRANSLIT', $CiudadDes));
      $Ciudad = mb_strtoupper($CiudadDes);
      self::logtxt("Ciudad: $Ciudad");
      $CiudadDestinatario = $this->DaneCode($Ciudad);

      // detalle
      $OrdenCompra = $id_order;

      // Mensaje del chekout
      // $messageCore = new MessageCore();
      // $message = $messageCore->getMessagesByOrderId($id_order);
      // $observaciones = '';
      // if(isset($message)){
      //   $observaciones = substr($message[0]['message'], 0, 200);
      // }

      // dejar en porteria
      $observaciones = '';
      if($other == '1'){
        $observaciones = "Dejar el pedido en porteria!";
      }

      // obtenemos el Estado o Departamento
      $states = new StateCore();
      $DepartamentoDestinatario = $states->getNameById($id_state);

      // Armando el xml de envio:
      require_once 'ws/orden_client.php';

      // iteramos el pedido
      foreach ($products as $key => $product) {
        
        $Consecutivo = ($key+1);
        if(isset($product['codigo_producto'])){
          $CodigoProducto = trim($product['codigo_producto']);
        }else{
          $CodigoProducto = trim($product['reference']);
        }
        // replace -1 in reference before sent to open market
        $CodigoProducto = str_replace("-1", "", $CodigoProducto);
        $CodigoProducto = str_replace("-2", "", $CodigoProducto);
        $CodigoProducto = str_replace("-3", "", $CodigoProducto);
        $CodigoProducto = str_replace("-4", "", $CodigoProducto);
        $CodigoProducto = str_replace("-5", "", $CodigoProducto);
        if(isset($product['unidades'])){
          $UnidadesSolucitadas = $product['unidades'];
        }else{
          $UnidadesSolucitadas = trim($product['quantity']);
        }

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
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CodigoDestinatario'] = $CodigoDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['NombreDestinatario'] = $customer_firstname.' '.$customer_lastname;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['DireccionDestinatario'] = $FullAddress;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CiudadDestinatario'] = $CiudadDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['TelefonoDestinatario'] = $TelefonoDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CelularDestinatario'] = $CelularDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMinimaEntrega'] = $FechaPedido; //fecha del pedido
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['DepartamentoDestinatario'] = $DepartamentoDestinatario; //departamento del destinatario
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMaximaEntrega'] = $FechaPedido; // fecha del pedido
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['Observaciones'] = $observaciones; // mensaje del checkout
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

        // self::logtxt("Consecutivo: $Consecutivo");

        $res = json_encode($DESPACHOS);
        self::logtxt("Json Enviado: $res");

        $wsOrden = new Orden();
        $orden = $wsOrden->getDataOrden($DESPACHOS);
        self::logtxt("Resultado: $orden");

        if($orden == '201_Información ingresó satisfactoriamente'){

          // update status
          $result = Db::getInstance()->update('wsopenmarket', array(
            'estado' => 1,
            'date_upd' => date("Y-m-d H:i:s"),
          ), 'id_orden = '.(int)$id_order);
          $error = Db::getInstance()->getMsgError();

          if ($result == true) {
              self::logtxt("Registro actualizado en wsopenmarket con exito");
          } else {
              if ($error != '') {
                  self::logtxt($error);
              }
              self::logtxt("Hubo un error al intentar actualizar en wsopenmarket");
          }

        }

        // set to smarty template values
        // $this->context->smarty->assign('orden', $orden);
        // self::logtxt("Resultado: $orden");

        // return $this->display(__FILE__, 'wsopenmarketMessage.tpl'); // only for testing
        
      // for testing//
      // echo "<pre>";
      // var_dump($DESPACHOS);
      // echo "</pre>";

      // if($Consecutivo > 1) {
      //   self::logtxt("$order");
      // }



      } //end foreach
        

    } catch (Exception $e) {
        self::logtxt("Exeptions on sendManuallyOrders: " . $e->getMessage());
    }
  } // end production
  else {
    self::logtxt("Modo Sandbox!");

    try {

      
      $id_order = (int)trim($this->WSOPENMARKET_GET_ID_ORDER);
      $order = new OrderCore($id_order);
      // consultar customer email DB/////
      $db = Db::getInstance();
      $sql = 'SELECT email FROM '._DB_PREFIX_.'customer WHERE id_customer = '.$order->id_customer;
      $customerEmail = $db->getValue($sql);
      ///////////////////////////////////////////

      $itemsDetails = $this->getOrderDetails($id_order);

      // si existen datos en la db wsopenmarket
      if($itemsDetails){
        $products = $itemsDetails;
      }else{
        // sino entonces se buscan del carrito normalmente
        $cart = new Cart($order->id_cart);
        $products = $cart->getProducts();
      }

      $number_products = count($products); // para saber cuantos items tiene cada pedido y enviar el xml
      $customers = new CustomerCore();
      $customer = $customers->getCustomersByEmail($customerEmail);

      // self::logtxt("---AQUI---");


      self::logtxt("numero de productos: $number_products");

      // cabecera
      $NroPedido = $id_order;
      $Documento = $id_order;
      $customer_firstname = $customer[0]['firstname'];
      $customer_lastname = $customer[0]['lastname'];
      self::logtxt("$customer_firstname $customer_lastname"); //testing
      
      $FechaHora = $order->date_add;
      // formatear fecha
      $FechaPedido = substr($FechaHora, 0, 10);
      $FechaPedido = strtotime($FechaPedido);
      $FechaPedido = date('d-m-Y', $FechaPedido);
      // formatear hora
      $HoraPedido = substr($FechaHora, 11, 19);
      // obtenemos la direccion y datos del cliente
      $id_address_delivery = $order->id_address_delivery;
      $address = new Address((int) $id_address_delivery);
      if (Validate::isLoadedObject($address)) {
          $FullAddress = $address->address1.' - '.$address->address2;
          $TelefonoDestinatario = $address->phone;
          $CelularDestinatario = $address->phone_mobile;
          $CodigoDestinatario = $address->dni;
          $CiudadDes = $address->city;
          $other = $address->other;
          $id_state = $address->id_state;
      } else {
          self::logtxt("Dirección erronea, el objeto no es válido");
      }
      $ValorAsegurado = round($order->total_paid, 2);

      // obtenemos el codigo dane segun la ciudad del destinatario
      setlocale(LC_ALL, 'en_US.UTF8');
      $CiudadDes= preg_replace("/[^A-Za-z0-9 ]/", '', iconv('UTF-8', 'ASCII//TRANSLIT', $CiudadDes));
      $Ciudad = mb_strtoupper($CiudadDes);
      self::logtxt("Ciudad: $Ciudad");
      $CiudadDestinatario = $this->DaneCode($Ciudad);

      // detalle
      $OrdenCompra = $id_order;

      // Mensaje del chekout
      // $messageCore = new MessageCore();
      // $message = $messageCore->getMessagesByOrderId($id_order);
      // $observaciones = '';
      // if(isset($message)){
      //   $observaciones = substr($message[0]['message'], 0, 200);
      // }

      // dejar en porteria
      $observaciones = '';
      if($other == '1'){
        $observaciones = "Dejar el pedido en porteria!";
      }

      // obtenemos el Estado o Departamento
      $states = new StateCore();
      $DepartamentoDestinatario = $states->getNameById($id_state);

      // Armando el xml de envio:
      require_once 'ws/orden_client.php';

      // iteramos el pedido
      foreach ($products as $key => $product) {
        
        $Consecutivo = ($key+1);
        if(isset($product['codigo_producto'])){
          $CodigoProducto = trim($product['codigo_producto']);
        }else{
          $CodigoProducto = trim($product['reference']);
        }
        // replace -1 in reference before sent to open market
        $CodigoProducto = str_replace("-1", "", $CodigoProducto);
        $CodigoProducto = str_replace("-2", "", $CodigoProducto);
        $CodigoProducto = str_replace("-3", "", $CodigoProducto);
        $CodigoProducto = str_replace("-4", "", $CodigoProducto);
        $CodigoProducto = str_replace("-5", "", $CodigoProducto);
        if(isset($product['unidades'])){
          $UnidadesSolucitadas = $product['unidades'];
        }else{
          $UnidadesSolucitadas = trim($product['quantity']);
        }
        
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
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CodigoDestinatario'] = $CodigoDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['NombreDestinatario'] = $customer_firstname.' '.$customer_lastname;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['DireccionDestinatario'] = $FullAddress;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CiudadDestinatario'] = $CiudadDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['TelefonoDestinatario'] = $TelefonoDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['CelularDestinatario'] = $CelularDestinatario;
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMinimaEntrega'] = $FechaPedido; //fecha del pedido
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['DepartamentoDestinatario'] = $DepartamentoDestinatario; //departamento del destinatario
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['FechaMaximaEntrega'] = $FechaPedido; // fecha del pedido
        $DESPACHOS['Sdtrecoutbounddelivery']['SDTRecOutboundDeliveryItem']['Observaciones'] = $observaciones; // mensaje del checkout
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

        // self::logtxt("Consecutivo: $Consecutivo");

        $res = json_encode($DESPACHOS);
        self::logtxt("Json Enviado: $res");

        // $wsOrden = new Orden();
        // $orden = $wsOrden->getDataOrden($DESPACHOS);
        // self::logtxt("Resultado: $orden");

        // if($orden == '201_Información ingresó satisfactoriamente'){

        //   // update status
        //   $result = Db::getInstance()->update('wsopenmarket', array(
        //     'estado' => 1,
        //     'date_upd' => date("Y-m-d H:i:s"),
        //   ), 'id_orden = '.(int)$id_order);
        //   $error = Db::getInstance()->getMsgError();

        //   if ($result == true) {
        //       self::logtxt("Registro actualizado en wsopenmarket con exito");
        //   } else {
        //       if ($error != '') {
        //           self::logtxt($error);
        //       }
        //       self::logtxt("Hubo un error al intentar actualizar en wsopenmarket");
        //   }

        // }


      // if($Consecutivo > 1) {
      //   self::logtxt("$order");
      // }



      } //end foreach
        

    } catch (Exception $e) {
        self::logtxt("Exeptions on sendManuallyOrders: " . $e->getMessage());
    }

  } // end sandbox


 } // end sendManuallyOrders

 // hook para validar la orden
 public function hookActionValidateOrder($params)
 {

  self::logtxt("se ejecuto hookActionValidateOrder Successful!!");

  if (!$this->WSOPENMARKET_SANDBOX_MODE == 1) {
    try {
      // $params = json_encode($params);
      // self::logtxt("params: $params");

      $id_order = $params['order']->id;
      // self::logtxt("id_order: $id_order");
      $id_shop = $params['order']->id_shop;
      $id_lang = $params['order']->id_lang;
      $fecha_pedido = $params['order']->date_add;
      // self::logtxt("fecha_pedido: $fecha_pedido");
      $id_customer = $params['order']->id_customer;
      // self::logtxt("id_customer: $id_customer");
      $valor_pedido = $params['order']->total_paid;
      // self::logtxt("valor_pedido: $valor_pedido");
      $reference = $params['order']->reference;
      // self::logtxt("reference: $reference");
      
      $firstname = $params['customer']->firstname;
      $lastname = $params['customer']->lastname;
      $nombre_destinatario = $firstname.' '.$lastname;
      // self::logtxt("nombre_destinatario: $nombre_destinatario");
      
      $product_list = $params['order']->product_list;
      // we iterate items for save in table
      foreach ($product_list as $key => $value) {
        $id_product = $value['id_product'];
        $quantityOrder = $value['quantity'];

        $itemsPack = $this->getItemsPack($id_product);
        // $itemsPack = json_encode($itemsPack);
        // self::logtxt("itemsPack: $itemsPack");

        if($itemsPack){

          foreach ($itemsPack as $key => $item) {

            $id_product = $item['id_product_item'];
            $quantityPack = $item['quantity'];
      
            $packDetails = $this->getPackDetails($id_product, $id_lang, $id_shop);
            // $packDetails = json_encode($packDetails);
            // self::logtxt("packDetails: $packDetails");
      
            $producto = $packDetails[0]['name'];
            $codigo_producto = $packDetails[0]['reference'];
            $unidades = (int)$quantityOrder * (int)$quantityPack;
      
            // save data
            $result = Db::getInstance()->insert('wsopenmarket', array(
              'id_orden' => $id_order,
              'referencia' => $reference,
              'fecha_pedido' => $fecha_pedido,
              'id_cliente' => $id_customer,
              'nombre_destinatario' => $nombre_destinatario,
              'valor_pedido' => $valor_pedido,
              'id_producto' => $id_product,
              'codigo_producto' => $codigo_producto,
              'producto' => $producto,
              'unidades' => $unidades,
              'date_add' => date("Y-m-d H:i:s"),
            ));
            $error = Db::getInstance()->getMsgError();
      
            if ($result == true) {
                self::logtxt("Registro guardado en wsopenmarket con exito");
            } else {
                if ($error != '') {
                    self::logtxt($error);
                }
                self::logtxt("Hubo un error al intentar guardar en wsopenmarket");
            }
      
          }//end foreach itemsPack

        }else{

          $producto = $value['name'];
          $codigo_producto = $value['reference'];

          // save data
          $result = Db::getInstance()->insert('wsopenmarket', array(
            'id_orden' => $id_order,
            'referencia' => $reference,
            'fecha_pedido' => $fecha_pedido,
            'id_cliente' => $id_customer,
            'nombre_destinatario' => $nombre_destinatario,
            'valor_pedido' => $valor_pedido,
            'id_producto' => $id_product,
            'codigo_producto' => $codigo_producto,
            'producto' => $producto,
            'unidades' => $quantityOrder,
            'date_add' => date("Y-m-d H:i:s"),
          ));
          $error = Db::getInstance()->getMsgError();

          if ($result == true) {
              self::logtxt("Registros guardados en wsopenmarket con exito");
          } else {
              if ($error != '') {
                  self::logtxt($error);
              }
              self::logtxt("Hubo un error al intentar guardar en wsopenmarket");
          }

        } //end if exist packs


      } //end iterate items for save in table

    } catch (Exception $e) {
      self::logtxt("Exeptions on hookActionValidateOrder: " . $e->getMessage());
    }
  } // end production

 } //end hookActionValidateOrder

//  hook for page route
 public function hookModuleRoutes()
{
    return [
        'module-wsopenmarket-states' => [
            'rule' => 'wsopenmarket/states',
            'keywords' => [],
            'controller' => 'states',
            'params' => [
                'fc' => 'module',
                'module' => 'wsopenmarket'
            ]
        ]
    ];
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

  /**
     * Get items from pack
     *
     * @param int $id_product product id
     * @return array items pack
     */
    public static function getItemsPack($id_product) {

      $query = new DbQuery();
      $query->select("*");
      $query->from("pack");
      $query->where("id_product_pack = " . $id_product);
      $res = Db::getInstance()->executeS($query);

      return $res;
  }

  /**
     * Get pack details
     *
     * @param int $id_product product id
     * @param int $id_lang language id
     * @param int $id_shop shop id
     * @return array pack details
     */
    public static function getPackDetails($id_product, $id_lang, $id_shop) {

      $query = new DbQuery();
      $query->select("A.reference");
      $query->select("B.name");
      $query->from("product", "A");
      $query->innerJoin('product_lang', 'B', 'A.id_product = B.id_product AND B.id_lang = '.(int)$id_lang. ' AND B.id_shop = '.(int)$id_shop);
      $query->where("A.id_product = ".$id_product);
      $res = Db::getInstance()->executeS($query);

      return $res;
  }

  /**
     * Get items from ps_wsopenmarket table
     *
     * @param int $id_order order id
     * @return array items details
     */
    public static function getOrderDetails($id_order) {

      $query = new DbQuery();
      $query->select("*");
      $query->from("wsopenmarket");
      $query->where("id_orden = " . $id_order);
      $res = Db::getInstance()->executeS($query);

      return $res;
  }

}
