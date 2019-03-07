<?php

class Service
{
 /**
  * Get data from picking
  *
  * @return return array
  */
 public function pickingConfirmation($inPicking)
 {

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

// Validar campos
if ($inPicking['NroPedido'] == null) {
    $pconfirmation = '204 - El numero del pedido no es válido o está vacio';

} elseif (!is_int($inPicking['NroPedido'])) {
    $pconfirmation = '406 - El numero de pedido no es entero';

} elseif ($fecha == null) {
    $pconfirmation = '204 - La fecha es nula';

} elseif ($fechaCorrect == false) {
    $pconfirmation = '204 - El formato de la fecha no es válido, ej: DD-MM-YYYY';

} elseif ($inPicking['Hora'] == null) {
    $pconfirmation = '204 - La hora es nula';

} elseif ($horaCorrect == false) {
    $pconfirmation = '204 - El formato de la hora no es válido, ej: HH:MM:SS';

}


//   $pconfirmation = json_encode($pconfirmation, true);
  return array(
   'Estado' => utf8_decode($pconfirmation),
  );

 }

}



// Db::getInstance()->insert('novonordisk_cart', array(
//                 'id_cart' => $order->id_cart,
//                 'id_order' => $order->id,
//                 'cardnumber' => self::getCustomerCard($order->id_customer),
//                 'transactionid' => self::getTransactionID($order->id_cart),
//                 'saleauthnumber' => $result->saleauthnumber,
//                 'errorid' => $result->errorid,
//                 'sended' => '1',
//                 'date_add' => date('Y:m:d H:i:s')
//                     )
//                     , false, true, Db::REPLACE);
//         }