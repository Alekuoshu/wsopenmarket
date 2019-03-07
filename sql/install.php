<?php
/**
 * 2007-2019 Farmalisto
 *
 *  @author    Farmalisto SA <alejandro.villegas@farmalisto.com.co>
 *  @copyright 2007-2019 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'wsopenmarket` (
    `id_wsopenmarket` int(11) NOT NULL AUTO_INCREMENT,
    PRIMARY KEY  (`id_wsopenmarket`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
 if (Db::getInstance()->execute($query) == false) {
  return false;
 }
}
