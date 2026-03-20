<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager\Glob_Matcher;

use Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager\Glob_Matcher\Glob_List_Matcher\Glob_List_Matcher;
use Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager\Glob_Matcher\Integration\Webmozart_Glob_Matcher;
/**
 * Expose multiple glob matching interface for given relative path.
 */
class Glob_Matcher
{
    /**
     * @param string $relativePath       Relative path to match against.
     * @param array  $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     *
     * @return bool True if given path matches any of given glob expression.
     */
    public static function match_any($relative_path, $glob_expression_list)
    {
        return (new Glob_List_Matcher(new Webmozart_Glob_Matcher()))->match_any($relative_path, $glob_expression_list);
    }
}