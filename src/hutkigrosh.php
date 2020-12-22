<?php

use esas\cmsgate\hutkigrosh\controllers\ControllerHutkigroshAddBill;
use esas\cmsgate\hutkigrosh\controllers\ControllerHutkigroshCompletionPage;
use esas\cmsgate\Registry;

defined('_JEXEC') or die;

if (!class_exists('vmPSPlugin')) {
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}
require_once(dirname(__FILE__) . '/init.php');

class plgVMPaymentHutkigrosh extends vmPSPlugin
{
    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->_tablepkey = 'if';
        $this->_tableId = 'id';
        $varsToPush = $this->getVarsToPush();
        $this->setConfigParameterable($this->_configTableFieldName, $varsToPush);
        $this->_loadLibrary();
    }

    //+
    function getTableSQLFields()
    {
        $SQLfields = array(
            'id' => 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT', //к сожалению, обязательное поле virtuemart/administrator/components/com_virtuemart/plugins/vmplugin.php:488
            'virtuemart_order_id' => 'int(1) UNSIGNED',
            'ext_trx_id' => 'char(64)'
        );
        return $SQLfields;
    }


    function plgVmConfirmedOrder($cart, $order)
    {
        $orderWrapper = Registry::getRegistry()->getOrderWrapper($order['details']['BT']->virtuemart_order_id);
        $controller = new ControllerHutkigroshAddBill();
        $controller->process($orderWrapper); //$order['details']['BT']->order_number;
    }

    function plgVmOnShowOrderBEPayment($virtuemart_order_id, $virtuemart_payment_id)
    {
        if (!$this->selectedThisByMethodId($virtuemart_payment_id)) {
            return NULL;
        }

        if (!($paymentTable = $this->getDataByOrderId($virtuemart_order_id))) {
            return NULL;
        }
        VmConfig::loadJLang('com_virtuemart');

        $html = '<table class="adminlist table">' . "\n";
        $html .= $this->getHtmlHeaderBE();
        $html .= $this->getHtmlRowBE('COM_VIRTUEMART_PAYMENT_NAME', $paymentTable->payment_name);
        $html .= $this->getHtmlRowBE('STANDARD_PAYMENT_TOTAL_CURRENCY', $paymentTable->payment_order_total . ' ' . $paymentTable->payment_currency);
        if ($paymentTable->email_currency) {
            $html .= $this->getHtmlRowBE('STANDARD_EMAIL_CURRENCY', $paymentTable->email_currency);
        }
        $html .= '</table>' . "\n";
        return $html;
    }

    function checkConditions($cart, $method, $cart_prices)
    {
        //todo check configuration
        return true;
    }

    //+
    function plgVmOnStoreInstallPaymentPluginTable($jplugin_id)
    {
        return $this->onStoreInstallPluginTable($jplugin_id);
    }

    //+
    public function plgVmOnSelectCheckPayment(VirtueMartCart $cart, &$msg)
    {
        return $this->OnSelectCheck($cart);
    }

    //+
    public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn)
    {
        //todo добавить надпись про sandbox
        return $this->displayListFE($cart, $selected, $htmlIn);
    }

    //+
    public function plgVmonSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name)
    {
        return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
    }

    function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId)
    {
        if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
            return NULL;
        }
        if (!$this->selectedThisElement($method->payment_element)) {
            return FALSE;
        }
        $this->getPaymentCurrency($method);

        $paymentCurrencyId = $method->payment_currency;
        return;
    }

    //+
    function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array(), &$paymentCounter)
    {
        return $this->onCheckAutomaticSelected($cart, $cart_prices, $paymentCounter);
    }

    //+
    public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name)
    {
        $this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
        $controller = new ControllerHutkigroshCompletionPage();
        $completionPanel = $controller->process($virtuemart_order_id);
        $completionPanel->render();
    }

    //+
    public function plgVmOnCheckoutCheckDataPayment(VirtueMartCart $cart)
    {
        return null;
    }

    function plgVmonShowOrderPrintPayment($order_number, $method_id)
    {
        return $this->onShowOrderPrint($order_number, $method_id);
    }

    //+
    function plgVmDeclarePluginParamsPaymentVM3(&$data)
    {
        return $this->declarePluginParams('payment', $data);
    }

    //*
    function plgVmSetOnTablePluginParamsPayment($name, $id, &$table)
    {
        return $this->setOnTablePluginParams($name, $id, $table);
    }

    function plgVmOnPaymentNotification()
    {
        $webhook = new \BeGateway\Webhook;

        vmdebug('BEGATEWAY plgVmOnPaymentResponseReceived', print_r($webhook->getResponseArray(), true));

        if (!class_exists('VirtueMartModelOrders'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');

        $tracking_id = explode('|', $webhook->getTrackingId());
        $virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($tracking_id[1]);

        $modelOrder = new VirtueMartModelOrders();
        $order = $modelOrder->getOrder($virtuemart_order_id);

        if (!($method = $this->getVmPluginMethod($tracking_id[0]))) {
            return NULL;
        } // Another method was selected, do nothing

        if (!isset($order['details']['BT']->virtuemart_order_id)) {
            return NULL;
        }

        \BeGateway\Settings::$shopId = $method->ShopId;
        \BeGateway\Settings::$shopKey = $method->ShopKey;
        \BeGateway\Settings::$gatewayBase = 'https://' . $method->GatewayUrl;
        \BeGateway\Settings::$checkoutBase = 'https://' . $method->PageUrl;

        if ($webhook->isAuthorized() && $webhook->isSuccess() && $order['details']['BT']->order_status == $method->status_pending) {
            $message = 'UID: ' . $webhook->getUid() . '<br>';
            if (isset($webhook->getResponse()->transaction->three_d_secure_verification)) {
                $message .= '3-D Secure: ' . $webhook->getResponse()->transaction->three_d_secure_verification->pa_status . '<br>';
            }

            $order['order_status'] = $method->status_success;
            $order['customer_notified'] = 1;
            $order['comments'] = $message;
            $modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, true);
            die("OK");
        } else {
            die("ERROR");
        }
    }

    //+ может не надо чистить карту?
    function plgVmOnPaymentResponseReceived(&$html)
    {
        if (!class_exists('VirtueMartCart'))
            require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
        $cart = VirtueMartCart::getCart();
        $cart->emptyCart();
        return true;
    }

    function plgVmOnUserPaymentCancel()
    {
        if (!class_exists('VirtueMartModelOrders'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');

        $order_number = vRequest::getVar('on');
        if (!$order_number)
            return false;
        $db = JFactory::getDBO();
        $query = 'SELECT ' . $this->_tablename . '.`virtuemart_order_id` FROM ' . $this->_tablename . " WHERE  `order_number`= '" . $order_number . "'";

        $db->setQuery($query);
        $virtuemart_order_id = $db->loadResult();

        if (!$virtuemart_order_id) {
            return null;
        }
        $this->handlePaymentUserCancel($virtuemart_order_id);

        return true;
    }

    private function _loadLibrary()
    {
        require JPATH_SITE . DS . 'plugins' . DS . 'vmpayment' . DS . 'begateway' . DS . 'begateway-api-php' . DS . 'lib' . DS . 'BeGateway.php';
    }
}
