<?php
/**
* Fetchr
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* It is also available through the world-wide-web at this URL:
* https://fetchr.zendesk.com/hc/en-us/categories/200522821-Downloads
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to ws@fetchr.us so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Fetchr PrestaShop Module to newer
* versions in the future. If you wish to customize Fetchr PrestaShop Module (Fetchr Shiphappy) for your
* needs please refer to http://www.fetchr.us for more information.
*
* @author     Danish Kamal
* @package    Fetchr Shiphappy
* Used in pusing order from PrestaShop Store to Fetchr
* @copyright  Copyright (c) 2015 Fetchr (http://www.fetchr.us)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

if (!defined('_PS_VERSION_'))
    exit;
class Fetchr extends Module
{
    private $html = '';
    private $option = '';

    protected static $class_aliases = array(
      'Collection' => 'PrestaShopCollection',
      'Autoload' => 'PrestaShopAutoload',
      'Backup' => 'PrestaShopBackup',
      'Logger' => 'PrestaShopLogger'
   );
    
    public function __construct()
    {
        $this->name          = 'fetchr';
        $this->tab           = 'shipping_logistics';
        $this->version       = 1.0;
        $this->author        = 'Danish Kamal';
        $this->need_instance = 0;
        parent::__construct();
        $this->displayName = $this->l('Fetchr - Ship Happy');
        $this->description = $this->l('Fetchr - Leading Logistic service provider in UAE, is a key player in ecommerce Connection.');
        $this->_checkContent();
        $this->FETCHR_PROCESS();
        $this->OrderData();
        $this->context->smarty->assign('module_name', $this->name);
    }
    public function install()
    {
        if (!parent::install() || !$this->_createContent()) {
            return false;
        } else {
            return true;
        }
    }
    public function uninstall()
    {
        if (!parent::uninstall() || !$this->_deleteContent()) {
            return false;
        } else {
            return true;
        }
    }
    public function getContent()
    {
        $message = '';
        if (Tools::isSubmit('submit_' . $this->name))
            $message = $this->_saveContent();
        $this->_displayContent($message);

        return $this->display(__FILE__, 'settings.tpl');
    }
    private function _saveContent()
    {
        $message = '';

        if (Configuration::updateValue('fetchr_username', Tools::getValue('fetchr_username')) && Configuration::updateValue('fetchr_password', Tools::getValue('fetchr_password')) && Configuration::updateValue('fetchr_servcie_type', Tools::getValue('fetchr_servcie_type')) && Configuration::updateValue('fetchr_account_type', Tools::getValue('fetchr_account_type')) && Configuration::updateValue('fetchr_liveurl', Tools::getValue('fetchr_liveurl')) && Configuration::updateValue('fetchr_stagingurl', Tools::getValue('fetchr_stagingurl')))
            $message = $this->displayConfirmation($this->l('Your settings have been saved'));
        else
            $message = $this->displayError($this->l('There was an error while saving your settings'));

        return $message;
    }
    private function _displayContent($message)
    {
        $this->context->smarty->assign(array(
            'message' => $message,
            'fetchr_username' => Configuration::get('fetchr_username'),
            'fetchr_password' => Configuration::get('fetchr_password'),
            'fetchr_servcie_type' => Configuration::get('fetchr_servcie_type'),
            'fetchr_account_type' => Configuration::get('fetchr_account_type')
        ));
    }
    private function _checkContent()
    {
        if (!Configuration::get('fetchr_username') && !Configuration::get('fetchr_password') && !Configuration::get('fetchr_servcie_type') && !Configuration::get('fetchr_account_type') && !Configuration::get('fetchr_liveurl') && !Configuration::get('fetchr_stagingurl'))
            $this->warning = $this->l('You need to configure this module.');
    }

    private function _createContent()
    {
        if (!Configuration::updateValue('fetchr_username', '') || !Configuration::updateValue('fetchr_password', '') || !Configuration::updateValue('fetchr_servcie_type', '') || !Configuration::updateValue('fetchr_account_type', '') || !Configuration::updateValue('fetchr_liveurl', '') || !Configuration::updateValue('fetchr_stagingurl', ''))
            return false;
        return true;
    }
    private function _deleteContent()
    {
        if (!Configuration::deleteByName('fetchr_username') || !Configuration::deleteByName('fetchr_password') || !Configuration::deleteByName('fetchr_servcie_type') || !Configuration::deleteByName('fetchr_account_type') || !Configuration::deleteByName('fetchr_liveurl') || !Configuration::deleteByName('fetchr_stagingurl'))
            return false;
        return true;

    }
    public function FETCHR_PROCESS()
    {
        if (!defined('FETCHR_PROCESS')) {

            $rq = Db::getInstance()->getRow('SELECT id_order_state FROM ' . _DB_PREFIX_ . 'order_state_lang WHERE id_lang = \'1\' AND  name = \'Fetchr Shipping\'');
            if ($rq && isset($rq['id_order_state']) && intval($rq['id_order_state']) > 0) {

                define('FETCHR_PROCESS', $rq['id_order_state']);
            } else {
                Db::getInstance()->Execute('INSERT INTO ' . _DB_PREFIX_ . 'order_state (unremovable, color) VALUES(0, \'#ff7b47\')');
                $stateid = Db::getInstance()->Insert_ID();
                Db::getInstance()->Execute('INSERT INTO ' . _DB_PREFIX_ . 'order_state_lang (id_order_state, id_lang, name)
                                                                VALUES(' . intval($stateid) . ', 1, \'Fetchr Shipping\')');
                define('FETCHR_PROCESS', $stateid);
            }
        }
    }
    public function OrderData()
    {
        $get_order_details = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'orders o LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON (c.id_customer = o.id_customer) LEFT JOIN ' . _DB_PREFIX_ . 'address a ON (a.id_address = o.id_address_delivery) WHERE o.current_state = ' . (int) Configuration::get('PS_OS_PREPARATION'));
        foreach ($get_order_details as $order) {
            $get_product_details = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'order_detail od WHERE od.id_order = ' . (int) ($order['id_order']));
            foreach ($get_product_details as $item) {
                $itemArray[] = array(
                    'client_ref' => $order['id_order'],
                    'name' => $item['product_name'],
                    'sku' => $item['product_reference'],
                    'quantity' => $item['product_quantity'],
                    'merchant_details' => array(
                        'mobile' => 'test',
                        'phone' => 'test',
                        'name' => 'test',
                        'address' => 'Dubai'
                    ),
                    'COD' => $order['total_shipping'],
                    'price' => $item['product_price'],
                    'is_voucher' => 'No'
                );
            }
            $accountType = Configuration::get('fetchr_account_type');
            switch ($accountType) {
                case 'live':
                    $baseurl = Configuration::get('fetchr_liveurl');
                    break;
                case 'staging':
                    $baseurl = Configuration::get('fetchr_stagingurl');
            }
            switch ($order['module']) {
                case 'cashondelivery':
                    $paymentType = 'COD';
                    $grandtotal  = $order['total_paid'];
                    break;
                default:
                    $paymentType = 'CD';
                    $grandtotal  = 0;
            }
            $ServiceType = Configuration::get('fetchr_servcie_type');
            $order['phone'] = trim($order['phone']);
            $order['phone_mobile'] = trim($order['phone_mobile']);
            
            switch ($ServiceType) {
                case 'fulfilment':
                    $dataErp[] = array(
                        "order" => array(
                            "items" => $itemArray,
                            "details" => array(
                                "status" => "",
                                "discount" => 0,
                                "grand_total" => $grandtotal,
                                "customer_email" => $order['email'],
                                "order_id" => $order['id_order'],
                                "customer_firstname" => $order['firstname'],
                                "payment_method" => $paymentType,
                                "customer_mobile" => !empty($order['phone']) ? $order['phone'] : $order['phone_mobile'],
                                "customer_lastname" => $order['lastname'],
                                "order_country" => $address['country'],
                                "order_address" => $order['address1'] . ', ' . $order['city'] . ', ' . $order['country']
                            )
                        )
                    );
                    break;
                case 'delivery':
                    $dataErp = array(
                        "username" => Configuration::get('fetchr_username'),
                        "password" => Configuration::get('fetchr_password'),
                        "method" => 'create_orders',
                        "data" => array(
                            array(
                                "order_reference" => $order['id_order'],
                                "name" => $order['firstname'] . ' ' . $order['firstname'],
                                "email" => $order['email'],
                                "phone_number" => $order['phone_mobile'],
                                "address" => $order['address1'],
                                "city" => $order['city'],
                                "payment_type" => $paymentType,
                                "amount" => $grandtotal,
                                "description" => 'No',
                                "comments" => 'No'
                            )
                        )
                    );
            }
            switch ($ServiceType) {
                case 'fulfilment':
                    $ERPdata = "ERPdata=" . json_encode($dataErp);
                    $ch      = curl_init();
                    $url     = $baseurl . "/client/gapicurl/";
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $ERPdata . "&erpuser=" . Configuration::get('fetchr_username') . "&erppassword=" . Configuration::get('fetchr_password') . "&merchant_name=" . Configuration::get('fetchr_username'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    curl_close($ch);
//                    print_r($response);
                    if ($response['response']['tracking_no'] != '0') {

                        $get_order_state = Db::getInstance()->executeS('SELECT id_order_state FROM ' . _DB_PREFIX_ . 'order_state_lang osl WHERE osl.name = "Fetchr Shipping"');
                        foreach ($get_order_state as $order_state_id) {
                            $fetchr_order_state_id = $order_state_id['id_order_state'];
                        }

                        $update_order_status = Db::getInstance()->executeS('UPDATE ' . _DB_PREFIX_ . 'orders o SET o.current_state = ' . (int) $fetchr_order_state_id . ' WHERE o.id_order = ' . (int) ($order['id_order']));
                        $cdate               = date("Y-m-d H:i:s");

                        Db::getInstance()->executeS('INSERT INTO ' . _DB_PREFIX_ . 'order_history (id_employee, id_order, id_order_state, date_add) VALUES (1, ' . (int) ($order['id_order']) . ', ' . (int) $fetchr_order_state_id . ', "' . date('Y-m-d H:i:s') . '")');
                    }

                    break;
                case 'delivery':
                    $data_string = "args=" . json_encode($dataErp);
                    $ch          = curl_init();
                    $url         = $baseurl . "/client/api/";
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                    $response = curl_exec($ch);
//                    print_r($response);
                    $varshipID     = $response;
                    $shipidexpStr  = explode("status", $varshipID);
                    $ResValShip    = $shipidexpStr[0];
                    $RevArray      = array(
                        '"',
                        '{'
                    );
                    $varshipIDTrim = str_replace($RevArray, '', $ResValShip);
                    $datas         = rtrim($varshipIDTrim, ',');
                    $array1        = explode(",", $datas);
                    foreach ($array1 as $val) {
                        $array2 = explode(":", $val);
                        if ($array2['1'] != '') {
                            $get_order_state = Db::getInstance()->executeS('SELECT id_order_state FROM ' . _DB_PREFIX_ . 'order_state_lang osl WHERE osl.name = "Fetchr Shipping"');
                            foreach ($get_order_state as $order_state_id) {
                                $fetchr_order_state_id = $order_state_id['id_order_state'];
                            }
                            Db::getInstance()->executeS('UPDATE ' . _DB_PREFIX_ . 'orders o SET o.current_state = ' . (int) $fetchr_order_state_id . ' WHERE o.id_order = ' . (int) ($order['id_order']));
                            Db::getInstance()->executeS('INSERT INTO ' . _DB_PREFIX_ . 'order_history (id_employee, id_order, id_order_state, date_add) VALUES (1, ' . (int) ($order['id_order']) . ', ' . (int) $fetchr_order_state_id . ', "' . date('Y-m-d H:i:s') . '")');
                        }
                    }
            }
        }
    }
}
?>
