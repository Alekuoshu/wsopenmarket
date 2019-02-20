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

  $pconfirmation = array();

  $pconfirmation[] = array(
   '# Factura' => $inPicking['Conf_DocFacDel'],
   'Fecha' => $inPicking['Conf_Fecha'],
   'Cod. Producto' => $inPicking['Conf_CodProd'],
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
