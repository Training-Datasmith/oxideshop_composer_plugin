<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Utilities\Copy_File_Manager\Glob_Matcher\Integration;

use InvalidArgumentException;
use Symfony\Component\Filesystem\Path;
/**
 * Class AbstractGlobMatcher.
 *
 * Abstract which defines API for matching a path against a glob expression.
 */
abstract class Abstract_Glob_Matcher
{
    /**
     * Returns true if given path matches a glob expression.
     *
     * @param string $relativePath
     * @param string $globExpression Glob filter expressions, e.g. "*.txt" or "*.pdf".
     *
     * @throws \InvalidArgumentException If given $globExpression is not a valid string.
     * @throws \InvalidArgumentException If given $globExpression is an absolute path.
     *
     * @return bool
     */
    public function match($relative_path, $glob_expression)
    {
        if (!is_string($glob_expression) && !is_null($glob_expression)) {
            $message = "Given value \"{$glob_expression}\" is not a valid glob expression. " . 'Valid expression must be a string e.g. "*.txt".';
            throw new InvalidArgumentException($message);
        }
        if (Path::is_absolute((string) $glob_expression)) {
            $message = "Given value \"{$glob_expression}\" is an absolute path. " . "Glob expression can only be accepted if it's a relative path.";
            throw new InvalidArgumentException($message);
        }
        if (is_null($glob_expression)) {
            return true;
        }
        return static::is_glob_match($relative_path, $glob_expression);
    }
    /**
     * Implementation details for matching a given path against glob expression.
     *
     * @param string $relativePath
     * @param string $globExpression Glob filter expressions, e.g. "*.txt" or "*.pdf".
     */
    abstract protected function is_glob_match($relative_path, $glob_expression);
}