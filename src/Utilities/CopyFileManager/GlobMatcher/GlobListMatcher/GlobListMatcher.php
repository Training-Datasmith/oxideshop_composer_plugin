<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\GlobListMatcher;

use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Integration\AbstractGlobMatcher;

/**
 * Class GlobListMatcher.
 *
 * Enables glob matching for a relative path against a list of glob expressions.
 */
class GlobListMatcher
{
    /**
     * GlobListMatcher constructor.
     *
     * @param AbstractGlobMatcher $globMatcher Instance of a variant from AbstractGlobMatcher.
     */
    public function __construct(protected $globMatcher)
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
    public function matchAny($relativePath, $globExpressionList)
    {
        if (
            !is_array($globExpressionList) && (!$globExpressionList instanceof \Traversable)
            && (!is_null($globExpressionList))
        ) {
            $message = "Given value \"$globExpressionList\" is not a valid glob expression list. " .
                "Valid entry must be a list of glob expressions e.g. [\"*.txt\", \"*.pdf\"].";

            throw new \InvalidArgumentException($message);
        }

        if (count($globExpressionList) > 0) {
            return $this->isMatchInList($relativePath, $globExpressionList);
        }

        return false;
    }

    /**
     * Returns true if the supplied globMatcher indicates a match for at least one item in given glob expression list.
     *
     * @param string $relativePath
     * @param array  $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     */
    private function isMatchInList($relativePath, array $globExpressionList): bool
    {
        foreach ($globExpressionList as $globExpression) {
            if ($this->globMatcher->match($relativePath, $globExpression)) {
                return true;
            }
        }

        return false;
    }
}
