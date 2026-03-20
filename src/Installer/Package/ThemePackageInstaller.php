<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Installer\Package;

use Composer\Package\Package_Interface;
use Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager\Copy_Glob_Filtered_File_Manager;
use Symfony\Component\Filesystem\Path;
/**
 * @inheritdoc
 */
class Theme_Package_Installer extends Abstract_Package_Installer
{
    public const METADATA_FILE_NAME = 'theme.php';
    public const PATH_TO_THEMES = 'Application/views';
    public function is_installed(string $package_path): bool
    {
        return file_exists($this->form_theme_target_path() . '/' . static::METADATA_FILE_NAME);
    }
    /**
     * Copies theme files to shop directory.
     *
     * @param string $packagePath
     */
    public function install($package_path): void
    {
        $this->write_installing_message($this->get_package_type_description());
        $this->write_copying_message();
        $this->copy_package($package_path);
        $this->write_done_message();
    }
    /**
     * Overwrites theme files.
     *
     * @param string $packagePath
     */
    public function update($package_path): void
    {
        $this->write_updating_message($this->get_package_type_description());
        $theme_directory_name = $this->form_theme_directory_name($this->get_package());
        $templates_path = str_replace($theme_directory_name, $this->highlight_message($theme_directory_name), $this->form_theme_target_path());
        $assets_path = str_replace($theme_directory_name, $this->highlight_message($theme_directory_name), $this->form_assets_directory_name());
        $question = 'All files in the following directories will be overwritten:' . PHP_EOL . '- ' . $templates_path . PHP_EOL . '- ' . Path::join($this->get_root_directory(), $assets_path) . PHP_EOL . 'Do you want to overwrite them? (y/N) ';
        if ($this->ask_question_if_not_installed($question, $package_path)) {
            $this->write_copying_message();
            $this->copy_package($package_path);
            $this->write_done_message();
        } else {
            $this->write_skipped_message();
        }
    }
    public function uninstall(string $package_path): void
    {
        //not implemented yet
    }
    /**
     * @param string $packagePath
     */
    protected function copy_package($package_path)
    {
        $filters_to_apply = [[Path::join($this->form_assets_directory_name(), Abstract_Package_Installer::BLACKLIST_ALL_FILES)], $this->get_blacklist_filter_value(), $this->get_vcs_filter()];
        Copy_Glob_Filtered_File_Manager::copy($package_path, $this->form_theme_target_path(), $this->get_combined_filters($filters_to_apply));
        $this->install_assets($package_path);
    }
    protected function form_theme_target_path(): string
    {
        $package = $this->get_package();
        $theme_directory_name = $this->form_theme_directory_name($package);
        return "{$this->get_root_directory()}/" . static::PATH_TO_THEMES . "/{$theme_directory_name}";
    }
    protected function install_assets(string $package_path)
    {
        $package = $this->get_package();
        $target = $this->get_root_directory() . '/out/' . $this->form_theme_directory_name($package);
        $assets_directory = $this->form_assets_directory_name();
        $source = $package_path . '/' . $assets_directory;
        if (file_exists($source)) {
            Copy_Glob_Filtered_File_Manager::copy($source, $target, $this->get_blacklist_filter_value());
        }
    }
    /**
     * @param PackageInterface $package
     * @return string
     */
    protected function form_theme_directory_name($package)
    {
        $theme_path = $this->get_extra_parameter_value_by_key(static::EXTRA_PARAMETER_KEY_TARGET);
        if (is_null($theme_path)) {
            return explode('/', (string) $package->get_name())[1];
        }
        return $theme_path;
    }
    /**
     * @return null|string
     */
    protected function form_assets_directory_name()
    {
        $assets_directory = $this->get_extra_parameter_value_by_key(static::EXTRA_PARAMETER_KEY_ASSETS);
        if (is_null($assets_directory)) {
            return 'out';
        }
        return $assets_directory;
    }
    protected function get_package_type_description(): string
    {
        return 'theme package';
    }
}