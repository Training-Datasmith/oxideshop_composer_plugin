<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Installer\Package;

use Oxid_Esales\Eshop_Community\Core\Di\Container_Facade;
use Oxid_Esales\Eshop_Community\Internal\Container\Bootstrap_Container_Factory;
use Oxid_Esales\Eshop_Community\Internal\Framework\Module\Install\Data_Object\Oxid_Eshop_Package;
use Oxid_Esales\Eshop_Community\Internal\Framework\Module\Install\Service\Module_Installer_Interface;
/**
 * @inheritdoc
 */
class Module_Package_Installer extends Abstract_Package_Installer
{
    /**
     * @return bool
     */
    public function is_installed(string $package_path)
    {
        return $this->get_bootstrap_module_installer()->is_installed($this->get_oxid_shop_package($package_path));
    }
    /**
     * Copies module files to shop directory.
     *
     * @param string $packagePath
     */
    public function install($package_path): void
    {
        $this->get_io()->write("Installing module {$this->get_package_name()} package.");
        $this->get_bootstrap_module_installer()->install($this->get_oxid_shop_package($package_path));
    }
    public function uninstall(string $package_path): void
    {
        $module_installer = $this->get_module_installer();
        $module_installer->uninstall($this->get_oxid_shop_package($package_path));
    }
    /**
     * @param string $packagePath
     */
    public function update($package_path): void
    {
        $package = $this->get_oxid_shop_package($package_path);
        if ($this->get_bootstrap_module_installer()->is_installed($package)) {
            $this->get_io()->write("Updating module {$this->get_package_name()} files...");
            $this->get_bootstrap_module_installer()->install($package);
        } else {
            $this->install($package_path);
        }
    }
    private function get_module_installer(): Module_Installer_Interface
    {
        try {
            return Container_Facade::get(Module_Installer_Interface::class);
        } catch (\Exception) {
            return $this->get_bootstrap_module_installer();
        }
    }
    private function get_oxid_shop_package(string $package_path): Oxid_Eshop_Package
    {
        return new Oxid_Eshop_Package($package_path);
    }
    private function get_bootstrap_module_installer(): Module_Installer_Interface
    {
        return Bootstrap_Container_Factory::get_bootstrap_container()->get('oxid_esales.module.install.service.bootstrap_module_installer');
    }
    /**
     * returns module's installation target direcory
     */
    protected function get_module_target_dir(): string
    {
        return $this->get_package()->get_extra()['oxideshop']['target-directory'] ?? '';
    }
}