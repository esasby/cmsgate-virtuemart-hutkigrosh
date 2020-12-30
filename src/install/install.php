<?php

use esas\cmsgate\utils\Logger;
use esas\cmsgate\virtuemart\InstallHelperVirtuemart;
use Joomla\CMS\Log\Log;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class plgVmpaymentHutkigroshInstallerScript
{
    function install($parent)
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
            InstallHelperVirtuemart::generateVmConfig();
            InstallHelperVirtuemart::dbPaymentMethodAdd();
            InstallHelperVirtuemart::dbActivateExtension();
        } catch (Exception $e) {
            $trace = Logger::getStackTrace($e);
            Log::add($trace, Log::ERROR);
            echo JText::sprintf($trace);
            return false;
        }
    }

    public static function preInstall() {
        //вручную копируем файлы из временной папки, в папку components, т.к. для корректной работы cmsgate Registry
        //нужна Model, а она ищется ядром в JPATH_ADMINISTRATOR
        $installTmpPath = dirname(dirname(__FILE__)) . '/jpath_root';
        $newPath = JPATH_ROOT;
        if (!JFolder::copy($installTmpPath, $newPath, "", true)) {
            throw new Exception('Can not copy folder from[' . $installTmpPath . '] to [' . $newPath . ']');
        }
        self::req();
    }

    public static function req() {
        require_once(dirname(dirname(__FILE__)) . '/init.php');
    }

    public function uninstall($parent)
    {
        $ret = true;
        self::req();
        $ret = $ret && InstallHelperVirtuemart::dbPaymentMethodDelete();
        $ret = $ret && InstallHelperVirtuemart::deleteFiles();
        return $ret;
    }

}