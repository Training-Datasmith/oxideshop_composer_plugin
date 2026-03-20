<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Installer\Package;

use Oxid_Esales\Eshop_Community\Internal\Container\Bootstrap_Container_Factory;
use Oxid_Esales\Eshop_Community\Internal\Framework\Di_Container\Service\Project_Yaml_Import_Service_Interface;
class Component_Installer extends Abstract_Package_Installer
{
    public function install($package_path): void
    {
        $this->write_installing_message('component');
        $this->import_service_file($package_path);
    }
    public function update($package_path): void
    {
        $this->write_updating_message('component');
        $this->import_service_file($package_path);
    }
    public function uninstall(string $package_path): void
    {
        //not implemented yet
    }
    /**
     * @param $packagePath
     */
    protected function import_service_file($package_path)
    {
        $project_yaml_import_service = Bootstrap_Container_Factory::get_bootstrap_container()->get(Project_Yaml_Import_Service_Interface::class);
        $project_yaml_import_service->remove_non_existing_imports();
        $project_yaml_import_service->add_import($package_path);
    }
}