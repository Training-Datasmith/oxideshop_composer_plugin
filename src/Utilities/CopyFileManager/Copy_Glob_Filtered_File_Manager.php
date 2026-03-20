<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager;

use Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager\Glob_Matcher\Glob_Matcher;
use Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager\Glob_Matcher\Iteration\Blacklist_Filter_Iterator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
/**
 * Class CopyGlobFilteredFileManager.
 *
 * Copies files/directories from source to destination which matches the criteria described in a glob filter.
 */
class Copy_Glob_Filtered_File_Manager
{
    /**
     * Copy files/directories from source to destination.
     *
     * @param string $sourcePath         Absolute path to file or directory.
     * @param string $destinationPath    Absolute path to file or directory.
     * @param array  $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     *
     * @throws \InvalidArgumentException If given $sourcePath is not a string.
     * @throws \InvalidArgumentException If given $destinationPath is not a string.
     */
    public static function copy($source_path, $destination_path, $glob_expression_list = []): void
    {
        if (!is_string($source_path)) {
            $message = "Given value \"{$source_path}\" is not a valid source path entry. " . 'Valid entry must be an absolute path to an existing file or directory.';
            throw new \InvalidArgumentException($message);
        }
        if (!is_string($destination_path)) {
            $message = "Given value \"{$destination_path}\" is not a valid destination path entry. " . 'Valid entry must be an absolute path to an existing directory.';
            throw new \InvalidArgumentException($message);
        }
        if (!file_exists($source_path)) {
            return;
        }
        if (is_dir($source_path)) {
            self::copy_directory($source_path, $destination_path, $glob_expression_list);
        } else {
            self::copy_file($source_path, $destination_path, $glob_expression_list);
        }
    }
    /**
     * Returns relative path from an absolute path to a file.
     *
     * @param string $sourcePath Absolute path to a file.
     */
    private static function get_relative_path_for_single_file(string $source_path): string
    {
        return Path::make_relative($source_path, Path::get_directory($source_path));
    }
    /**
     * Return an iterator which iterates through a given directory tree in a one-dimensional fashion.
     *
     * Consider the following file/directory structure as an example:
     *
     *   * directory_a
     *     * file_a_a
     *   * directory_b
     *     * file_b_a
     *     * file_b_b
     *   * file_c
     *
     * RecursiveDirectoryIterator would iterate through:
     *   * directory_a [iterator]
     *   * directory_b [iterator]
     *   * file_c [SplFileInfo]
     *
     * In contrast current method would iterate through:
     *   * directory_a [SplFileInfo]
     *   * directory_a/file_a_a [SplFileInfo]
     *   * directory_b [SplFileInfo]
     *   * directory_b/file_b_a [SplFileInfo]
     *   * directory_b/file_b_b [SplFileInfo]
     *   * file_c [SplFileInfo]
     *
     * @param string $sourcePath Absolute path to directory.
     *
     * @return \Iterator
     */
    private static function get_flat_file_list_iterator($source_path): \Recursive_Iterator_Iterator
    {
        $recursive_file_iterator = new \Recursive_Directory_Iterator($source_path, \Filesystem_Iterator::SKIP_DOTS);
        return new \Recursive_Iterator_Iterator($recursive_file_iterator);
    }
    /**
     * Copy whole directory using given glob filters.
     *
     * @param string $sourcePath         Absolute path to directory.
     * @param string $destinationPath    Absolute path to directory.
     * @param array  $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     */
    private static function copy_directory(string $source_path, string $destination_path, $glob_expression_list): void
    {
        $filesystem = new Filesystem();
        $flat_file_list_iterator = self::get_flat_file_list_iterator($source_path);
        $filtered_file_list_iterator = new Blacklist_Filter_Iterator($flat_file_list_iterator, $source_path, $glob_expression_list);
        $filesystem->mirror($source_path, $destination_path, $filtered_file_list_iterator, ['override' => true]);
    }
    /**
     * Copy file using given glob filters.
     *
     * @param string $sourcePathOfFile   Absolute path to file.
     * @param string $destinationPath    Absolute path to directory.
     * @param array  $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     */
    private static function copy_file(string $source_path_of_file, string $destination_path, $glob_expression_list): void
    {
        $filesystem = new Filesystem();
        $relative_source_path = self::get_relative_path_for_single_file($source_path_of_file);
        if (!Glob_Matcher::match_any($relative_source_path, $glob_expression_list)) {
            $filesystem->copy($source_path_of_file, $destination_path, true);
        }
    }
}