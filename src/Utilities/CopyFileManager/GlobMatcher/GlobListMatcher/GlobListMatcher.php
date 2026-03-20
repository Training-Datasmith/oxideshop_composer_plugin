<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager\Glob_Matcher\Glob_List_Matcher;

use Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager\Glob_Matcher\Integration\Abstract_Glob_Matcher;
/**
 * Class GlobListMatcher.
 *
 * Enables glob matching for a relative path against a list of glob expressions.
 */
class Glob_List_Matcher
{
    /**
     * GlobListMatcher constructor.
     *
     * @param AbstractGlobMatcher $globMatcher Instance of a variant from AbstractGlobMatcher.
     */
    public function __construct(protected $glob_matcher)
    {
    }
    /**
     * Returns true if given relative path matches against at least one glob expression from provided list.
     *
     * @param string $relativePath
     * @param array  $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     *
     * @throws \InvalidArgumentException If $globExpressionList is not a \Traversable instance.
     *
     * @return bool
     */
    public function match_any($relative_path, $glob_expression_list)
    {
        if (!is_array($glob_expression_list) && !$glob_expression_list instanceof \Traversable && !is_null($glob_expression_list)) {
            $message = "Given value \"{$glob_expression_list}\" is not a valid glob expression list. " . 'Valid entry must be a list of glob expressions e.g. ["*.txt", "*.pdf"].';
            throw new \InvalidArgumentException($message);
        }
        if (count($glob_expression_list) > 0) {
            return $this->is_match_in_list($relative_path, $glob_expression_list);
        }
        return false;
    }
    /**
     * Returns true if the supplied globMatcher indicates a match for at least one item in given glob expression list.
     *
     * @param string $relativePath
     * @param array  $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     */
    private function is_match_in_list($relative_path, array $glob_expression_list): bool
    {
        foreach ($glob_expression_list as $glob_expression) {
            if ($this->glob_matcher->match($relative_path, $glob_expression)) {
                return true;
            }
        }
        return false;
    }
}