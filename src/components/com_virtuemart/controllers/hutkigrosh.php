<?php

/**
 *
 * Controller for the Plugins Response
 *
 * @package	VirtueMart
 * @subpackage pluginResponse
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team and authors. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: cart.php 3388 2011-05-27 13:50:18Z alatak $
 */
// Check to ensure this file is included in Joomla!
use esas\cmsgate\hutkigrosh\controllers\ControllerHutkigroshCompletionPage;
use esas\cmsgate\hutkigrosh\utils\RequestParamsHutkigrosh;
use esas\cmsgate\Registry;
use esas\cmsgate\utils\Logger;
use Joomla\CMS\Factory;

defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/plugins/vmpayment/hutkigrosh/init.php');

/**
 * Controller for the plugin response view
 *
 * @package VirtueMart
 * @subpackage pluginresponse
 * @author Valérie Isaksen
 *
 */
class VirtueMartControllerHutkigrosh extends JControllerLegacy {

    /**
     * Construct the cart
     *
     * @access public
     */
    public function __construct() {
		parent::__construct();
    }

    function complete()
    {
        try {
            $user = Factory::getUser();
            if (empty($user) || $user->id == 0)
                throw new Exception("User is not logged in");
            $orderNumber = vRequest::getVar(RequestParamsHutkigrosh::ORDER_NUMBER);
            $orderWrapper = Registry::getRegistry()->getOrderWrapperByOrderNumber($orderNumber);
            if ($user->id != $orderWrapper->getClientId())
                throw new Exception("Incorrect order number");
            $controller = new ControllerHutkigroshCompletionPage();
            $completionPanel = $controller->process($orderWrapper);
            $completionPanel->render();
        } catch (Throwable $e) {
            Logger::getLogger("payment")->error("Exception:", $e);
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
    }
}
