<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.inc.php');
require_once(dirname(__FILE__).'/../../../classes/order/Order.php');
require_once(dirname(__FILE__).'/../../../classes/order/OrderHistory.php');


error_reporting(E_ALL);
ini_set('display_errors', 1);


class Service
{
 /**
  * Get data from picking
    *
  * @return return array
  */
 public function pickingConfirmation($inPicking)
 {
    //  instance this module
    $module = Module::getInstanceByName('wsopenmarket');


  $fecha = $inPicking['Fecha'];
  $hora = $inPicking['Hora'];


//  validar si la fecha es correcta
if (preg_match("/^([0-2][0-9]|(3)[0-1])(-)(((0)[0-9])|((1)[0-2]))(-)\d{4}$/",$fecha)) {
    $fechaCorrect = true;
        $fecha = strtotime($inPicking['Fecha']);
        $fecha = date('Y-m-d', $fecha);
} else {
    $fechaCorrect = false;
}

//  validar si la hora es correcta
if (preg_match("/^(?:[01]\d|2[0123]):(?:[012345]\d):(?:[012345]\d)$/",$hora)) {
    $horaCorrect = true;
        $hora = strtotime($inPicking['Hora']);
        $hora = date('Y-m-d', $hora);
} else {
    $horaCorrect = false;
}

// db id order states:
// 3 (003) - Preparación en curso
// 14 (004) – Empacado
// 4 (005) – Enviado
// 5 (006) – Entregado
// 7 (007) – Devuelto
// 15 (008) – Rechazado

// chequear cada estado disponible
if($inPicking['Estado'] == '003'){
    $new_order_state = 3;
}
if ($inPicking['Estado'] == '004') {
    $new_order_state = 14;
}
if ($inPicking['Estado'] == '005') {
    $new_order_state = 4;
}
if ($inPicking['Estado'] == '006') {
    $new_order_state = 5;
}
if ($inPicking['Estado'] == '007') {
    $new_order_state = 7;
}
if ($inPicking['Estado'] == '008') {
    $new_order_state = 15;
}


  $pconfirmation = array(
   'NroPedido' => $inPicking['NroPedido'],
   'Fecha' => $fecha,
   'Hora' => $hora,
   'Estado' => $inPicking['Estado'],
  );


// evaluar los estados para mostrar la respuesta
switch ($inPicking['Estado']) {
    case '003':
        $pconfirmation = '200 - Preparación en curso';
        break;
    case '004':
        $pconfirmation = '200 - Empacado';
        break;
    case '005':
        $pconfirmation = '200 - Enviado';
        break;
    case '006':
        $pconfirmation = '200 - Entregado';
        break;
    case '007':
        $pconfirmation = '200 - Devuelto';
        break;
    case '008':
        $pconfirmation = '200 - Rechazado';
        break;

    default:
        $pconfirmation = '204 - Debe enviar un codigo de estado válido!';
        break;
}

// consultar si existe el pedido actual
$db = Db::getInstance();
$sql = 'SELECT current_state FROM '._DB_PREFIX_.'orders WHERE id_order = '.$inPicking["NroPedido"];
$current_stateid = $db->getValue($sql);


// Validar campos
if ($inPicking['NroPedido'] == null) {
    $pconfirmation = '204 - El numero del pedido no es válido o está vacio';

} elseif (!is_int($inPicking['NroPedido'])) {
    $pconfirmation = '406 - El numero de pedido no es entero';

} elseif ($current_stateid < 1) {
    $pconfirmation = '204 - El pedido con numero: '.$inPicking['NroPedido'].' no existe';

} elseif ($fecha == null) {
    $pconfirmation = '204 - La fecha es nula';

} elseif ($fechaCorrect == false) {
    $pconfirmation = '204 - El formato de la fecha no es válido, ej: DD-MM-YYYY';

} elseif ($inPicking['Hora'] == null) {
    $pconfirmation = '204 - La hora es nula';

} elseif ($horaCorrect == false) {
    $pconfirmation = '204 - El formato de la hora no es válido, ej: HH:MM:SS';

}


// Actualizando estado
if($current_stateid >= 1){
    $order = new Order($inPicking['NroPedido']);
    if (!Validate::isLoadedObject($order)) {
        throw new PrestaShopException('No se ha podido cargar el estado de la orden');
    }

    $history = new OrderHistory();
    $history->id_order = (int) $order->id;
    $history->id_employee = 0;
    $history->id_order_state = $new_order_state;
     if ($history->save()) {
        $history->changeIdOrderState($new_order_state, $order);
         $module::logtxt('Estado de la orden '.$inPicking['NroPedido'].' actualizada a: ' . $new_order_state);
     } else {
         $module::logtxt('Error al completar la actualización del estado de la orden: ' . $inPicking['NroPedido']);
     }

}

//   $pconfirmation = json_encode($pconfirmation, true);
  return array(
   'Estado' => utf8_decode($pconfirmation),
  );

 }

}