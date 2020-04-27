<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.inc.php');
require_once(dirname(__FILE__).'/../../../classes/order/Order.php');
require_once(dirname(__FILE__).'/../../../classes/order/OrderHistory.php');


// error_reporting(E_ALL);
// ini_set('display_errors', 1);


class Service
{
    /**
     * Get data from picking
       *
     * @return return array
     */
    public function pickingConfirmation($inPicking)
    {

        // Secrect Key o Token
        $token = 'PJFDW9M21JEYU2XJCQRU3199CJAQ4DYE';

        if ($inPicking['Token'] == $token) {
            
            //  instance this module
            $module = Module::getInstanceByName('wsopenmarket');
            $module::logtxt(json_encode('...Entro a pickingConfirmation...'));
            $module::logtxt(json_encode($inPicking));
            $state = false;

            $fecha = $inPicking['Fecha'];
            $hora = $inPicking['Hora'];


            //  validar si la fecha es correcta
            if (preg_match("/^([0-2][0-9]|(3)[0-1])(-)(((0)[0-9])|((1)[0-2]))(-)\d{4}$/", $fecha)) {
                $fechaCorrect = true;
                $fecha = strtotime($inPicking['Fecha']);
                $fecha = date('Y-m-d', $fecha);
            } else {
                $fechaCorrect = false;
            }

            //  validar si la hora es correcta
            if (preg_match("/^(?:[01]\d|2[0123]):(?:[012345]\d):(?:[012345]\d)$/", $hora)) {
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
            // 15 (007) – Devuelto
            // 16 (008) – Rechazado
            // 19 (0099) – No se Encontró el Encargado del Recibo del Pedido - #78fff0
            // 20 (0098) – Dirección Errada - #934b00
            // 21 (0097) – Mercancía en Mal Estado No Apta para su Entrega - #ff7620
            // 22 (0096) – Mercancía no solicitada por Destinatario - #ffad80
            // 23 (0095) – Falta documentos de Soporte y/o Aviso de Despacho - #e3b400
            // 24 (0094) – Cambio de Dirección y/o Ciudad de Entrega - #ffe410


            $pconfirmation = array(
                'Token' => $inPicking['Token'],
                'NroPedido' => $inPicking['NroPedido'],
                'Fecha' => $fecha,
                'Hora' => $hora,
                'Estado' => $inPicking['Estado'],
            );


            // evaluar los estados para mostrar la respuesta
            switch ($inPicking['Estado']) {
            case '003':
            case '03':
                $pconfirmation = '200 - Preparación en curso';
                $new_order_state = 3;
                $state = true;
                break;
            case '004':
            case '04':
                $pconfirmation = '200 - Empacado';
                $new_order_state = 14;
                $state = true;
                break;
            case '005':
            case '05':
                $pconfirmation = '200 - Enviado';
                $new_order_state = 4;
                $state = true;
                break;
            case '006':
            case '06':
                $pconfirmation = '200 - Entregado';
                $new_order_state = 5;
                $state = true;
                break;
            case '007':
            case '07':
                $pconfirmation = '200 - Devuelto';
                $new_order_state = 15;
                $state = true;
                break;
            case '008':
            case '08':
                $pconfirmation = '200 - Rechazado';
                $new_order_state = 16;
                $state = true;
                break;
            case '0099':
            case '099':
            case '99':
                $pconfirmation = '200 - No se Encontró el Encargado del Recibo del Pedido';
                $new_order_state = 19;
                $state = true;
                break;
            case '0098':
            case '098':
            case '98':
                $pconfirmation = '200 - Dirección Errada';
                $new_order_state = 20;
                $state = true;
                break;
            case '0097':
            case '097':
            case '97':
                $pconfirmation = '200 - Mercancía en Mal Estado No Apta para su Entrega';
                $new_order_state = 21;
                $state = true;
                break;
            case '0096':
            case '096':
            case '96':
                $pconfirmation = '200 - Mercancía no solicitada por Destinatario';
                $new_order_state = 22;
                $state = true;
                break;
            case '0095':
            case '095':
            case '95':
                $pconfirmation = '200 - Falta documentos de Soporte y/o Aviso de Despacho';
                $new_order_state = 23;
                $state = true;
                break;
            case '0094':
            case '094':
            case '94':
                $pconfirmation = '200 - Cambio de Dirección y/o Ciudad de Entrega';
                $new_order_state = 24;
                $state = true;
                break;

            default:
                $pconfirmation = '204 - Debe enviar un codigo de estado válido!';
                $new_order_state = 0;
                $state = false;
                break;
        }

            // consultar si existe el pedido actual
            $db = Db::getInstance();
            $sql = 'SELECT current_state FROM '._DB_PREFIX_.'orders WHERE id_order = '.$inPicking["NroPedido"];
            $current_stateid = $db->getValue($sql);


            // Validar campos
            if ($inPicking['NroPedido'] == null) {
                $pconfirmation = '204 - El numero del pedido no es válido o está vacio';
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
            if ($current_stateid >= 1 && $state == true) {
                $order = new Order($inPicking['NroPedido']);
                if (!Validate::isLoadedObject($order)) {
                    throw new PrestaShopException('No se ha podido cargar el estado de la orden');
                    $module::logtxt(json_encode('No se ha podido cargar el estado de la orden'));
                }

                if ((int) $order->getCurrentState() != (int)$new_order_state) {
                    try {
                        $order->setCurrentState($new_order_state, 9);
                        $module::logtxt('Estado de la orden '.$inPicking['NroPedido'].' actualizada a: ' . $new_order_state);
                    } catch (Exception $e) {
                        $module::logtxt('Error al completar la actualización del estado de la orden: ' . $inPicking['NroPedido'].', Mensaje: '.$e->getMessage());
                        $pconfirmation = '500 - Error al cambiar el estado de la orden';
                    }
                }else {
                    $module::logtxt('La orden ' . $inPicking['NroPedido'].' ya tiene el estado numero: '.$new_order_state);
                }

            }

            $pconfirmationRes = json_encode($pconfirmation, true);
            $module::logtxt('pconfirmationRes: '.$pconfirmationRes);
            return array(
                'Estado' => utf8_decode($pconfirmation),
            );
        } else {
            return array(
            'Estado' => utf8_decode('Acceso denegado, token inválido!!'),
            );
        }
    }
}
