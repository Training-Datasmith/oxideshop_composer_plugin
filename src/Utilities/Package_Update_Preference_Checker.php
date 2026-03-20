<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
declare (strict_types=1);
namespace Oxid_Esales\Composer_Plugin\Utilities;

class Package_Update_Preference_Checker
{
    public const UPDATE_EXTRA_KEY_YES = 'update-answer-yes';
    public const UPDATE_EXTRA_KEY_NO = 'update-answer-no';
    public const PREFERENCE_MISSCONFIGURED_ERROR = 'Missconfigured update preference value, check documentation.';
    public function __construct(private readonly array $extras)
    {
    }
    public function get_update_preference_value(string $package_name): ?bool
    {
        if (isset($this->extras[self::UPDATE_EXTRA_KEY_YES]) && $this->check_preference_configuration_is_array(self::UPDATE_EXTRA_KEY_YES) && in_array($package_name, $this->extras[self::UPDATE_EXTRA_KEY_YES])) {
            return true;
        }
        if (isset($this->extras[self::UPDATE_EXTRA_KEY_NO]) && $this->check_preference_configuration_is_array(self::UPDATE_EXTRA_KEY_NO) && in_array($package_name, $this->extras[self::UPDATE_EXTRA_KEY_NO])) {
            return false;
        }
        return null;
    }
    /**
     * @throws \Exception
     */
    private function check_preference_configuration_is_array(string $key): bool
    {
        if (!is_array($this->extras[$key])) {
            throw new \Exception(self::PREFERENCE_MISSCONFIGURED_ERROR);
        }
        return true;
    }
}