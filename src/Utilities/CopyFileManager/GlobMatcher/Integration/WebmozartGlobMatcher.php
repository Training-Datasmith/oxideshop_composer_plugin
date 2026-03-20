<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager\Glob_Matcher\Integration;

use Webmozart\Glob\Glob;
/**
 * Class WebmozartGlobMatcher.
 *
 * An integration of "webmozart/glob" package to match AbstractGlobMatcher.
 */
class Webmozart_Glob_Matcher extends Abstract_Glob_Matcher
{
    /**
     * Check if given path matches provided glob expression using "webmozart/glob" package.
     *
     * @param string $relativePath
     * @param string $globExpression Glob filter expressions, e.g. "*.txt" or "*.pdf".
     *
     * @return bool True in case the path matches the given glob expression.
     */
    protected function is_glob_match($relative_path, $glob_expression)
    {
        return Glob::match("/{$relative_path}", "/{$glob_expression}");
    }
}