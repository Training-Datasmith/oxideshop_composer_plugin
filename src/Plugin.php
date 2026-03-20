<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin;

use Composer\Composer;
use Composer\Event_Dispatcher\Event_Subscriber_Interface;
use Composer\Installer\Package_Event;
use Composer\IO\Io_Interface;
use Composer\Package\Alias_Package;
use Composer\Plugin\Plugin_Interface;
use Composer\Util\Package_Sorter;
use Oxid_Esales\Composer_Plugin\Installer\Package\Abstract_Package_Installer;
use Oxid_Esales\Composer_Plugin\Installer\Package_Installer_Trigger;
use Oxid_Esales\Eshop_Community\Core\Autoload\Module_Autoload;
use Oxid_Esales\Eshop_Community\Internal\Container\Bootstrap_Container_Factory;
use Oxid_Esales\Eshop_Community\Internal\Framework\File_System\Project_Directories_Locator;
use Oxid_Esales\Eshop_Community\Internal\Framework\Module\Configuration\Dao\Shop_Configuration_Dao_Interface;
use Symfony\Component\Filesystem\Path;
class Plugin implements Plugin_Interface, Event_Subscriber_Interface
{
    private ?\Composer\Composer $composer = null;
    private ?\Oxid_Esales\Composer_Plugin\Installer\Package_Installer_Trigger $package_installer_trigger = null;
    /**
     * Register events.
     */
    public static function get_subscribed_events(): array
    {
        return ['post-install-cmd' => 'installPackages', 'post-update-cmd' => 'updatePackages', 'pre-package-uninstall' => 'uninstallPackage'];
    }
    /**
     * Register shop packages installer.
     */
    public function activate(Composer $composer, Io_Interface $io): void
    {
        $package_installer_trigger = new Package_Installer_Trigger($io, $composer);
        $composer->get_installation_manager()->add_installer($package_installer_trigger);
        $this->composer = $composer;
        $this->package_installer_trigger = $package_installer_trigger;
        $extra_settings = $this->composer->get_package()->get_extra();
        if (isset($extra_settings[Abstract_Package_Installer::EXTRA_PARAMETER_KEY_ROOT])) {
            $this->package_installer_trigger->set_settings($extra_settings[Abstract_Package_Installer::EXTRA_PARAMETER_KEY_ROOT]);
        }
    }
    public function deactivate(Composer $composer, Io_Interface $io)
    {
    }
    public function uninstall(Composer $composer, Io_Interface $io)
    {
    }
    public function install_packages(): void
    {
        $this->autoload_installed_packages();
        $this->bootstrap_oxid_shop_component();
        $this->generate_default_project_configuration_if_missing();
        foreach ($this->get_packages_sorted() as $package) {
            if ($package instanceof Alias_Package) {
                continue;
            }
            if ($this->package_installer_trigger->supports($package->get_type())) {
                $this->package_installer_trigger->install_package($package);
            }
        }
    }
    public function update_packages(): void
    {
        $this->autoload_installed_packages();
        $this->bootstrap_oxid_shop_component();
        $this->generate_default_project_configuration_if_missing();
        foreach ($this->get_packages_sorted() as $package) {
            if ($package instanceof Alias_Package) {
                continue;
            }
            if ($this->package_installer_trigger->supports($package->get_type())) {
                $this->package_installer_trigger->update_package($package);
            }
        }
    }
    public function uninstall_package(Package_Event $event): void
    {
        $this->autoload_installed_packages();
        $package = $event->get_operation()->get_package();
        if ($this->package_installer_trigger->supports($package->get_type())) {
            $this->bootstrap_oxid_shop_component();
            $this->package_installer_trigger->uninstall_package($package);
        }
    }
    /**
     * Composer autoloads classes needed for its own tasks only. Classes of other packages installed need to be loaded
     * separately.
     */
    private function autoload_installed_packages(): void
    {
        $vendor_dir = $this->composer->get_config()->get('vendor-dir');
        require_once $vendor_dir . '/autoload.php';
    }
    private function bootstrap_oxid_shop_component(): void
    {
        $bootstrap_file_path = Path::join((new Project_Directories_Locator())->get_source_path(), 'bootstrap.php');
        if (file_exists($bootstrap_file_path)) {
            require_once $bootstrap_file_path;
            spl_autoload_unregister([Module_Autoload::class, 'autoload']);
        }
    }
    private function generate_default_project_configuration_if_missing(): void
    {
        $bootstrap_container = Bootstrap_Container_Factory::get_bootstrap_container();
        if (count($bootstrap_container->get(Shop_Configuration_Dao_Interface::class)->get_all()) === 0) {
            $bootstrap_container->get('oxid_esales.module.install.service.installed_shop_project_configuration_generator')->generate();
        }
    }
    private function get_packages_sorted(): array
    {
        return Package_Sorter::sort_packages($this->composer->get_repository_manager()->get_local_repository()->get_packages());
    }
}