<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Installer;

use Composer\Installer\Library_Installer;
use Composer\Package\Package_Interface;
use Oxid_Esales\Composer_Plugin\Installer\Package\Abstract_Package_Installer;
use Oxid_Esales\Composer_Plugin\Installer\Package\Component_Installer;
use Oxid_Esales\Composer_Plugin\Installer\Package\Module_Package_Installer;
use Oxid_Esales\Composer_Plugin\Installer\Package\Shop_Package_Installer;
use Oxid_Esales\Composer_Plugin\Installer\Package\Theme_Package_Installer;
use Symfony\Component\Filesystem\Path;
/**
 * Class responsible for triggering installation process.
 */
class Package_Installer_Trigger extends Library_Installer
{
    public const TYPE_ESHOP = 'oxideshop';
    public const TYPE_MODULE = 'oxideshop-module';
    public const TYPE_THEME = 'oxideshop-theme';
    public const TYPE_DEMODATA = 'oxideshop-demodata';
    public const TYPE_COMPONENT = 'oxideshop-component';
    /** @var array Available installers for packages. */
    private array $installers = [self::TYPE_ESHOP => Shop_Package_Installer::class, self::TYPE_MODULE => Module_Package_Installer::class, self::TYPE_THEME => Theme_Package_Installer::class, self::TYPE_COMPONENT => Component_Installer::class];
    /**
     * @var array configurations
     */
    protected $settings = [];
    /**
     * Decides if the installer supports the given type
     *
     * @param string $packageType
     * @return bool
     */
    public function supports($package_type)
    {
        return array_key_exists($package_type, $this->installers);
    }
    /**
     * @param array $settings Set additional settings.
     */
    public function set_settings($settings): void
    {
        $this->settings = $settings;
    }
    public function install_package(Package_Interface $package): void
    {
        $installer = $this->create_installer($package);
        $package_path = $this->get_install_path($package);
        if (!$installer->is_installed($package_path)) {
            $installer->install($package_path);
        }
    }
    public function update_package(Package_Interface $package): void
    {
        $installer = $this->create_installer($package);
        $installer->update($this->get_install_path($package));
    }
    public function uninstall_package(Package_Interface $package): void
    {
        $installer = $this->create_installer($package);
        $installer->uninstall($this->get_install_path($package));
    }
    /**
     * Get the path to shop's source directory.
     *
     * @return string
     */
    public function get_shop_source_path()
    {
        return $this->settings[Abstract_Package_Installer::EXTRA_PARAMETER_SOURCE_PATH] ?? Path::join(getcwd(), Shop_Package_Installer::SHOP_SOURCE_DIRECTORY);
    }
    /**
     * Creates package installer.
     *
     * @return AbstractPackageInstaller
     */
    protected function create_installer(Package_Interface $package)
    {
        return new $this->installers[$package->get_type()]($this->io, $this->get_shop_source_path(), $package, $this->settings);
    }
}