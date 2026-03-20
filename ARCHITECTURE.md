# Architecture: oxideshop_composer_plugin

## Purpose

A Composer plugin for OXID eShop that handles the installation and deployment of OXID-specific package types (modules, themes, shop packages, components) into the correct shop directory structure.

## Directory Structure

```
src/
  Plugin.php                                   — Composer plugin entry point: registers installers
  Installer/
    Package_Installer_Trigger.php              — Routes install/update/uninstall events to the correct installer
    Package/
      Abstract_Package_Installer.php           — Base installer: file copying, blacklist filtering
      Module_Package_Installer.php             — Installs oxid-module packages into modules/ directory
      Theme_Package_Installer.php              — Installs oxid-theme packages into out/themes/
      Shop_Package_Installer.php               — Installs the shop core package
      Component_Installer.php                  — Installs generic oxid-component packages
  Utilities/
    CopyFileManager/
      Copy_Glob_Filtered_File_Manager.php      — Copies files while filtering by glob blacklist
      GlobMatcher/
        Glob_Matcher.php                       — Tests file paths against glob patterns
        GlobListMatcher/
          Glob_List_Matcher.php                — Tests against multiple glob patterns
        Integration/
          Webmozart_Glob_Matcher.php           — Adapter wrapping webmozart/glob
        Iteration/
          Blacklist_Filter_Iterator.php        — Iterator that skips blacklisted files
    Package_Update_Preference_Checker.php      — Determines if a package should overwrite existing files
    Vfs_File_Structure_Operator.php            — Virtual filesystem helpers (used in tests)
```

## Key Design Decisions

- **Composer plugin architecture**: Implements `Composer\Plugin\PluginInterface` and `Composer\Installer\InstallerInterface`; hooks into Composer events rather than running as a CLI tool
- **Package-type routing**: `Package_Installer_Trigger` checks the Composer package type (`oxid-module`, `oxid-theme`, etc.) and delegates to the appropriate `*PackageInstaller`
- **Glob-based blacklists**: Modules can declare file patterns to exclude from being overwritten on update (e.g., configuration files)

## Extension Points

- Add a new package type by creating a new `*PackageInstaller` extending `Abstract_Package_Installer` and registering it in `Package_Installer_Trigger`
