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

  $fecha = strtotime($inPicking['Fecha']);
  $fecha = date('Y-m-d', $fecha);

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
   'Hora' => $inPicking['Hora'],
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

} elseif ($inPicking['Fecha'] == null) {
    $pconfirmation = '204 - La fecha es nula';

} elseif ($inPicking['Hora'] == null) {
    $pconfirmation = '204 - La hora es nula';
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