<?php
use esas\cmsgate\virtuemart\InstallHelperVirtuemart;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class plgVmpaymentHutkigroshInstallerScript
{
    function install($parent)
    {
    }

    function uninstall($parent)
    {

    }

    function update($parent)
    {

    }

    function postflight($type, $parent)
    {
        try {
            //вручную копируем файлы из временной папки, в папку components, иначе не сработают require_once
            self::preInstall();
            self::req();
            InstallHelperVirtuemart::generateVmConfig();
//            InstallUtilsJoomshopping::dbAddPaymentMethod();
//            $this->dbAddCompletionText();
//            InstallUtilsJoomshopping::dbActivatePlugin();
        } catch (Exception $e) {
            echo JText::sprintf($e->getMessage());
            return false;
        }
    }

    public static function preInstall() {
        //вручную копируем файлы из временной папки, в папку components, т.к. для корректной работы cmsgate Registry
        //нужна Model, а она ищется ядром в JPATH_ADMINISTRATOR
        $installTmpPath = dirname(dirname(__FILE__)) . '/components';
        $newPath = JPATH_ADMINISTRATOR . '/components';
        if (!JFolder::copy($installTmpPath, $newPath, "", true)) {
            throw new Exception('Can not copy folder from[' . $installTmpPath . '] to [' . $newPath . ']');
        }

    }

    public static function req()
    {
//        require_once(PATH_JSHOPPING . 'lib/factory.php');
        require_once(dirname(dirname(__FILE__)) . '/init.php');
//        require_once(PATH_JSHOPPING . 'payments/pm_' . $paySystemName . '/init.php');
    }

}