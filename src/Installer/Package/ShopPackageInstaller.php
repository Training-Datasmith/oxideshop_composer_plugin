<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Installer\Package;

use Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager\Copy_Glob_Filtered_File_Manager;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Webmozart\Glob\Iterator\Glob_Iterator;
/**
 * @inheritdoc
 */
class Shop_Package_Installer extends Abstract_Package_Installer
{
    public const SHOP_SOURCE_DIRECTORY = 'source';
    private const FILE_TO_CHECK_IF_PACKAGE_INSTALLED = 'index.php';
    private const FAVICON_FILE = 'favicon.ico';
    private const OFFLINE_FILE = 'offline.html';
    private const ENV_DIST_FILE = '.env.dist';
    private const HTACCESS_FILTER = '**/.htaccess';
    private const ROBOTS_EXCLUSION_FILTER = '**/robots.txt';
    public function is_installed(string $package_path): bool
    {
        return file_exists(Path::join($this->get_target_directory_of_shop_source(), self::FILE_TO_CHECK_IF_PACKAGE_INSTALLED));
    }
    /**
     * Copies all shop files from vendors to source directory.
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
     * Overwrites files in core directories.
     *
     * @param string $packagePath
     */
    public function update($package_path): void
    {
        $shop_source_directory = str_replace('source', $this->highlight_message('source'), $this->get_target_directory_of_shop_source());
        $this->write_updating_message($this->get_package_type_description());
        $question = sprintf('All files in the following directories will be overwritten:%s- %s%sDo you want to overwrite them? (y/N) ', PHP_EOL, $shop_source_directory, PHP_EOL);
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
    private function copy_package($package_path): void
    {
        $this->copy_shop_source_from_package_to_target($package_path);
        $this->copy_htaccess_files($package_path);
        $this->copy_favicon_file($package_path);
        $this->copy_offline_file($package_path);
        $this->copy_env_dist_file($package_path);
        $this->copy_robots_exclusion_files($package_path);
    }
    /**
     * Copy shop source files from package source to defined target path.
     *
     * @param string $packagePath
     */
    private function copy_shop_source_from_package_to_target($package_path): void
    {
        $filters_to_apply = [$this->get_blacklist_filter_value(), [self::HTACCESS_FILTER], [self::ROBOTS_EXCLUSION_FILTER], [self::FAVICON_FILE], [self::OFFLINE_FILE], $this->get_vcs_filter()];
        Copy_Glob_Filtered_File_Manager::copy($this->get_package_directory_of_shop_source($package_path), $this->get_target_directory_of_shop_source(), $this->get_combined_filters($filters_to_apply));
    }
    /**
     * Copy shop's htaccess files from package.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copy_htaccess_files($package_path): void
    {
        $this->copy_files_from_source_to_installation_by_filter($package_path, self::HTACCESS_FILTER);
    }
    /**
     * Copy shop's favicon from package.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copy_favicon_file($package_path): void
    {
        $this->copy_files_from_source_to_installation_by_filter($package_path, self::FAVICON_FILE);
    }
    /**
     * Copy shop's offline/maintenance page from package.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copy_offline_file($package_path): void
    {
        $this->copy_files_from_source_to_installation_by_filter($package_path, self::OFFLINE_FILE);
    }
    private function copy_env_dist_file(string $package_path): void
    {
        $source_file_path = Path::join($package_path, self::ENV_DIST_FILE);
        $project_root_path = dirname($this->get_root_directory());
        $target_file_path = Path::join($project_root_path, self::ENV_DIST_FILE);
        $this->copy_file_if_is_missing($source_file_path, $target_file_path);
    }
    /**
     * Copy shop's robots exclusion files from package.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copy_robots_exclusion_files($package_path): void
    {
        $this->copy_files_from_source_to_installation_by_filter($package_path, self::ROBOTS_EXCLUSION_FILTER);
    }
    /**
     * Return package directory which points to shop's source directory.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function get_package_directory_of_shop_source(string $package_path): string
    {
        return Path::join($package_path, self::SHOP_SOURCE_DIRECTORY);
    }
    /**
     * Return target directory where shop's source files needs to be copied.
     *
     * @return string
     */
    private function get_target_directory_of_shop_source()
    {
        return $this->get_root_directory();
    }
    /**
     * Copy files from source to installation by filter.
     *
     * @param string $packagePath
     */
    private function copy_files_from_source_to_installation_by_filter($package_path, string $filter): void
    {
        $source_directory = $this->get_package_directory_of_shop_source($package_path);
        $filtered_files = $this->get_filtered_files($source_directory, $filter);
        foreach ($filtered_files as $package_file_path) {
            $installation_file_path = $this->get_absolute_file_path_from_installation($source_directory, $package_file_path);
            $this->copy_file_if_is_missing($package_file_path, $installation_file_path);
        }
    }
    /**
     * Copy file if is missing.
     *
     * @param string $sourcePath
     * @param string $destinationPath
     */
    private function copy_file_if_is_missing($source_path, $destination_path): void
    {
        if (!file_exists($destination_path)) {
            Copy_Glob_Filtered_File_Manager::copy($source_path, $destination_path);
        }
    }
    /**
     * Return filtered files.
     *
     * @param   string $filter
     * @return  GlobIterator
     */
    private function get_filtered_files(string $directory, $filter)
    {
        return new Glob_Iterator(Path::join($directory, $filter));
    }
    /**
     * Return absolute path to file from installation.
     *
     *
     */
    private function get_absolute_file_path_from_installation(string $source_package_directory, string $absolute_path_to_file_from_package): string
    {
        $installation_directory_of_shop_source = $this->get_target_directory_of_shop_source();
        $relative_path_of_source_from_package = Path::make_relative($absolute_path_to_file_from_package, $source_package_directory);
        return Path::join($installation_directory_of_shop_source, $relative_path_of_source_from_package);
    }
    private function get_package_type_description(): string
    {
        return 'OXID eShop package';
    }
}