<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager\Glob_Matcher\Iteration;

use Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager\Glob_Matcher\Glob_Matcher;
use Symfony\Component\Filesystem\Path;
/**
 * An iterator which iterates through given iterator of files/directories and filters out the items described in list of
 * glob filter definitions (black list filtering).
 */
class Blacklist_Filter_Iterator extends \Filter_Iterator
{
    /**
     * BlacklistFilterIterator constructor.
     *
     * @param \Iterator $iterator           An iterator which iterates through files/directories.
     * @param string    $rootPath           Absolute root path from the start of iteration.
     * @param array     $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     */
    public function __construct(\Iterator $iterator, private $root_path, private $glob_expression_list)
    {
        parent::__construct($iterator);
    }
    /**
     * {@inheritdoc}
     */
    public function accept(): bool
    {
        $path = $this->convert_from_spl_file_info_to_string(parent::current());
        return !Glob_Matcher::match_any($this->get_relative_path($path), $this->glob_expression_list);
    }
    /**
     * Get relative path from given item of iteration compared to provided root path.
     *
     * @param string $absolutePath Absolute path from iteration.
     */
    private function get_relative_path(string $absolute_path): string
    {
        return Path::make_relative($absolute_path, $this->root_path);
    }
    /**
     * Returns string to absolute path from an entry of SplFileInfo.
     *
     * @param \SplFileInfo $item Item from iteration.
     */
    private function convert_from_spl_file_info_to_string(\Spl_File_Info $item): string
    {
        return (string) $item;
    }
}