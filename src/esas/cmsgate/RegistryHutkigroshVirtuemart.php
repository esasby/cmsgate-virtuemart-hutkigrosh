<?php
/**
 * Created by IntelliJ IDEA.
 * User: nikit
 * Date: 15.07.2019
 * Time: 11:22
 */

namespace esas\cmsgate;

use esas\cmsgate\descriptors\ModuleDescriptor;
use esas\cmsgate\descriptors\VendorDescriptor;
use esas\cmsgate\descriptors\VersionDescriptor;
use esas\cmsgate\hutkigrosh\ConfigFieldsHutkigrosh;
use esas\cmsgate\hutkigrosh\PaysystemConnectorHutkigrosh;
use esas\cmsgate\hutkigrosh\RegistryHutkigrosh;
use esas\cmsgate\view\admin\AdminViewFields;
use esas\cmsgate\view\admin\ConfigFormVirtuemart;

class RegistryHutkigroshVirtuemart extends RegistryHutkigrosh
{
    public function __construct()
    {
        $this->cmsConnector = new CmsConnectorVirtuemart();
        $this->paysystemConnector = new PaysystemConnectorHutkigrosh();
    }

    /**
     * Переопделение для упрощения типизации
     * @return RegistryHutkigroshVirtuemart
     */
    public static function getRegistry()
    {
        return parent::getRegistry();
    }

    /**
     * Переопделение для упрощения типизации
     * @return ConfigFormVirtuemart
     */
    public function getConfigForm()
    {
        return parent::getConfigForm();
    }

    public function createConfigForm()
    {
        $managedFields = $this->getManagedFieldsFactory()->getManagedFieldsExcept(AdminViewFields::CONFIG_FORM_COMMON,
            [
                ConfigFieldsHutkigrosh::shopName()
            ]);
        $configForm = new ConfigFormVirtuemart(
            AdminViewFields::CONFIG_FORM_COMMON,
            $managedFields);
        return $configForm;
    }

    public function getCompletionPanel($orderWrapper)
    {
        return null; //not implementes
    }

    function getUrlAlfaclick($orderWrapper)
    {
        return null; //not implementes
    }

    function getUrlWebpay($orderWrapper)
    {
        return null; //not implementes
    }

    public function createModuleDescriptor()
    {
        return new ModuleDescriptor(
            "hutkigrosh",
            new VersionDescriptor("1.13.0", "2020-12-08"),
            "Прием платежей через ЕРИП (сервис Hutkirosh)",
            "https://bitbucket.esas.by/projects/CG/repos/cmsgate-virtuemart-hutkigrosh/browse",
            VendorDescriptor::esas(),
            "Выставление пользовательских счетов в ЕРИП"
        );
    }
}