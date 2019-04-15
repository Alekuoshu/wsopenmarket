<?php
/**
* 2007-2019 Farmalisto
*
*  @author    Farmalisto <alejandro.villegas@farmalisto.com.co>
*  @copyright 2007-2019 Farmalisto
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once __DIR__ . '/../AbstractRestController.php';

class WsopenmarketstatesModuleFrontController extends AbstractRestController
{
    protected function processGetRequest()
    {
        // do something then output the result
        $this->ajaxDie(json_encode([
            'success' => true,
            'operation' => 'get'
        ]));
    }

    protected function processPostRequest()
    {
        // do something then output the result
        $this->ajaxDie(json_encode([
            'success' => true,
            'operation' => 'post'
        ]));
    }

    protected function processPutRequest()
    {
        // do something then output the result
        $this->ajaxDie(json_encode([
            'success' => true,
            'operation' => 'put'
        ]));
    }

    protected function processDeleteRequest()
    {
        // do something then output the result
        $this->ajaxDie(json_encode([
            'success' => true,
            'operation' => 'delete'
        ]));
    }
}


?>