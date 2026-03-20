<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Installer\Package;

use Composer\IO\Io_Interface;
use Composer\Package\Package_Interface;
use Oxid_Esales\Composer_Plugin\Utilities\Package_Update_Preference_Checker;
/**
 * Class is responsible for preparing project structure.
 * It copies necessary files to specific directories.
 */
abstract class Abstract_Package_Installer
{
    public const EXTRA_PARAMETER_KEY_ROOT = 'oxideshop';
    public const EXTRA_PARAMETER_KEY_TARGET = 'target-directory';
    public const EXTRA_PARAMETER_KEY_ASSETS = 'assets-directory';
    public const EXTRA_PARAMETER_SOURCE_PATH = 'source-path';
    public const EXTRA_PARAMETER_FILTER_BLACKLIST = 'blacklist-filter';
    public const BLACKLIST_ALL_FILES = '**/*';
    public const BLACKLIST_VCS_DIRECTORY = '.git';
    public const BLACKLIST_VCS_IGNORE_FILE = '.gitignore';
    public const BLACKLIST_VCS_DIRECTORY_FILTER = self::BLACKLIST_VCS_DIRECTORY . DIRECTORY_SEPARATOR . self::BLACKLIST_ALL_FILES;
    private readonly Package_Update_Preference_Checker $package_update_preference_checker;
    /**
     * AbstractInstaller constructor.
     */
    public function __construct(private readonly Io_Interface $io, private readonly string $root_directory, private readonly Package_Interface $package, array $settings = [])
    {
        $this->package_update_preference_checker = new Package_Update_Preference_Checker($settings);
    }
    /**
     * Run package installation procedure. After installation files should be moved to correct location.
     *
     * @param string $packagePath Path to downloaded package in vendors directory.
     */
    abstract public function install($package_path);
    /**
     * Run update procedure to keep package files up to date.
     *
     * @param string $packagePath Path to downloaded package in vendors directory.
     */
    abstract public function update($package_path);
    abstract public function uninstall(string $package_path): void;
    /**
     * @return string
     */
    protected function get_root_directory()
    {
        return $this->root_directory;
    }
    /**
     * @return string
     */
    protected function get_package_name()
    {
        return $this->get_package()->get_name();
    }
    /**
     * Return the value defined in composer extra parameters for blacklist filtering.
     *
     * @return array
     */
    protected function get_blacklist_filter_value()
    {
        return $this->get_extra_parameter_value_by_key(static::EXTRA_PARAMETER_FILTER_BLACKLIST, []);
    }
    /**
     * Search for parameter with specific key in "extra" composer configuration block
     *
     * @param string $extraParameterKey
     * @param string $defaultValue
     *
     * @return array|string|null
     */
    protected function get_extra_parameter_value_by_key($extra_parameter_key, $default_value = null)
    {
        $extra_parameters = $this->get_package()->get_extra();
        $extra_parameter_value = $extra_parameters[static::EXTRA_PARAMETER_KEY_ROOT][$extra_parameter_key] ?? null;
        return !empty($extra_parameter_value) ? $extra_parameter_value : $default_value;
    }
    /**
     * @return PackageInterface
     */
    public function get_package()
    {
        return $this->package;
    }
    /**
     * Get VCS glob filter expression
     *
     * @return array
     */
    protected function get_vcs_filter()
    {
        return [self::BLACKLIST_VCS_DIRECTORY_FILTER, self::BLACKLIST_VCS_IGNORE_FILE];
    }
    /**
     * Combine multiple glob expression lists into one list
     *
     * @param array $listOfGlobExpressionLists E.g. [["*.txt", "*.pdf"], ["*.md"]]
     *
     * @return array
     */
    protected function get_combined_filters($list_of_glob_expression_lists)
    {
        $filters = [];
        foreach ($list_of_glob_expression_lists as $filter) {
            $filters = array_merge($filters, $filter);
        }
        return $filters;
    }
    /**
     *
     * @return bool
     */
    protected function ask_question_if_not_installed(string $message_to_ask, string $package_path)
    {
        return $this->is_installed($package_path) ? $this->ask_question($message_to_ask) : true;
    }
    /**
     * Check whether given package is already installed.
     *
     *
     * @return bool
     */
    public function is_installed(string $package_path)
    {
        return false;
    }
    /**
     * Returns true if the human answer to the given question was answered with a positive value (Yes/yes/Y/y).
     *
     * @param string $messageToAsk
     *
     * @return bool
     */
    protected function ask_question($message_to_ask)
    {
        $preference_value = $this->package_update_preference_checker->get_update_preference_value($this->get_package_name());
        if (!is_null($preference_value)) {
            return $preference_value;
        }
        $user_input = $this->get_io()->ask($message_to_ask, 'N');
        return $this->is_positive_user_input($user_input);
    }
    /**
     * @return IOInterface
     */
    protected function get_io()
    {
        return $this->io;
    }
    /**
     * Return true if the input from user is a positive answer (Yes/yes/Y/y)
     *
     * @param string $userInput Raw user input
     */
    private function is_positive_user_input($user_input): bool
    {
        $positive_answers = ['yes', 'y'];
        return in_array(strtolower(trim($user_input)), $positive_answers, true);
    }
    protected function write_installing_message(string $package_type)
    {
        $this->get_io()->write($this->get_installing_message($package_type));
    }
    protected function get_installing_message(string $package_type): string
    {
        return $this->get_message_prefix() . "Installing {$package_type} {$this->get_package()->get_name()}";
    }
    protected function get_message_prefix(): string
    {
        return '<info>oxid-esales/oxideshop-composer-plugin:</info> ';
    }
    protected function write_updating_message(string $package_type)
    {
        $this->get_io()->write($this->get_updating_message($package_type));
    }
    protected function get_updating_message(string $package_type): string
    {
        $package_name = $this->highlight_message($this->get_package()->get_name());
        return $this->get_message_prefix() . "Updating {$package_type} {$package_name}";
    }
    /**
     * @return mixed
     */
    protected function write_copying_message()
    {
        return $this->get_io()->write($this->get_copying_message());
    }
    protected function get_copying_message(): string
    {
        return 'Copying files ...';
    }
    /**
     * @return mixed
     */
    protected function write_done_message()
    {
        return $this->get_io()->write($this->get_done_message());
    }
    protected function get_done_message(): string
    {
        return 'Done';
    }
    /**
     * @return mixed
     */
    protected function write_skipped_message()
    {
        return $this->get_io()->write($this->get_skipped_message());
    }
    protected function get_skipped_message(): string
    {
        return 'Skipped';
    }
    protected function highlight_message(string $message): string
    {
        return '<options=bold>' . $message . '</>';
    }
}