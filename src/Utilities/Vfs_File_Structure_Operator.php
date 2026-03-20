<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Utilities;

/**
 * Class VfsFileStructureOperator.
 */
class Vfs_File_Structure_Operator
{
    /**
     * Convert given flat file system structure into nested one.
     *
     * @param array|null $flatFileSystemStructure
     */
    public static function nest($flat_file_system_structure = null): array
    {
        if (null !== $flat_file_system_structure && false === is_array($flat_file_system_structure)) {
            throw new \InvalidArgumentException('Given input argument must be an array.');
        }
        if (null === $flat_file_system_structure) {
            return [];
        }
        $nested_file_system_structure = [];
        foreach ($flat_file_system_structure as $path_entry => $contents) {
            $path_entries = explode(DIRECTORY_SEPARATOR, (string) $path_entry);
            $pointer_to_branch =& $nested_file_system_structure;
            foreach ($path_entries as $single_path_entry) {
                $single_path_entry = trim($single_path_entry);
                if ($single_path_entry !== '') {
                    if (!is_array($pointer_to_branch)) {
                        $pointer_to_branch = [];
                    }
                    if (!key_exists($single_path_entry, $pointer_to_branch)) {
                        $pointer_to_branch[$single_path_entry] = [];
                    }
                    $pointer_to_branch =& $pointer_to_branch[$single_path_entry];
                }
            }
            if (substr((string) $path_entry, -1) !== DIRECTORY_SEPARATOR) {
                $pointer_to_branch = $contents;
            }
        }
        return $nested_file_system_structure;
    }
}