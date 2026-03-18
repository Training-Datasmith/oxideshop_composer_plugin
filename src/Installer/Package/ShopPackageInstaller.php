<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Installer\Package;

use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\CopyGlobFilteredFileManager;

use function sprintf;

use Symfony\Component\Filesystem\Path;

use Webmozart\Glob\Iterator\GlobIterator;

/**
 * @inheritdoc
 */
class ShopPackageInstaller extends AbstractPackageInstaller
{
    public const SHOP_SOURCE_DIRECTORY = 'source';
    private const FILE_TO_CHECK_IF_PACKAGE_INSTALLED = 'index.php';
    private const FAVICON_FILE = 'favicon.ico';
    private const OFFLINE_FILE = 'offline.html';
    private const ENV_DIST_FILE = '.env.dist';
    private const HTACCESS_FILTER = '**/.htaccess';
    private const ROBOTS_EXCLUSION_FILTER = '**/robots.txt';

    public function isInstalled(string $packagePath): bool
    {
        return file_exists(
            Path::join($this->getTargetDirectoryOfShopSource(), self::FILE_TO_CHECK_IF_PACKAGE_INSTALLED)
        );
    }

    /**
     * Copies all shop files from vendors to source directory.
     *
     * @param string $packagePath
     */
    public function install($packagePath): void
    {
        $this->writeInstallingMessage($this->getPackageTypeDescription());
        $this->writeCopyingMessage();
        $this->copyPackage($packagePath);
        $this->writeDoneMessage();
    }

    /**
     * Overwrites files in core directories.
     *
     * @param string $packagePath
     */
    public function update($packagePath): void
    {
        $shopSourceDirectory = str_replace(
            'source',
            $this->highlightMessage('source'),
            $this->getTargetDirectoryOfShopSource()
        );

        $this->writeUpdatingMessage($this->getPackageTypeDescription());
        $question = sprintf(
            'All files in the following directories will be overwritten:%s- %s%sDo you want to overwrite them? (y/N) ',
            PHP_EOL,
            $shopSourceDirectory,
            PHP_EOL
        );

        if ($this->askQuestionIfNotInstalled($question, $packagePath)) {
            $this->writeCopyingMessage();
            $this->copyPackage($packagePath);
            $this->writeDoneMessage();
        } else {
            $this->writeSkippedMessage();
        }
    }

    public function uninstall(string $packagePath): void
    {
        //not implemented yet
    }

    /**
     * @param string $packagePath
     */
    private function copyPackage($packagePath): void
    {
        $this->copyShopSourceFromPackageToTarget($packagePath);
        $this->copyHtaccessFiles($packagePath);
        $this->copyFaviconFile($packagePath);
        $this->copyOfflineFile($packagePath);
        $this->copyEnvDistFile($packagePath);
        $this->copyRobotsExclusionFiles($packagePath);
    }

    /**
     * Copy shop source files from package source to defined target path.
     *
     * @param string $packagePath
     */
    private function copyShopSourceFromPackageToTarget($packagePath): void
    {
        $filtersToApply = [
            $this->getBlacklistFilterValue(),
            [self::HTACCESS_FILTER],
            [self::ROBOTS_EXCLUSION_FILTER],
            [self::FAVICON_FILE],
            [self::OFFLINE_FILE],
            $this->getVCSFilter(),
        ];

        CopyGlobFilteredFileManager::copy(
            $this->getPackageDirectoryOfShopSource($packagePath),
            $this->getTargetDirectoryOfShopSource(),
            $this->getCombinedFilters($filtersToApply)
        );
    }

    /**
     * Copy shop's htaccess files from package.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copyHtaccessFiles($packagePath): void
    {
        $this->copyFilesFromSourceToInstallationByFilter(
            $packagePath,
            self::HTACCESS_FILTER
        );
    }

    /**
     * Copy shop's favicon from package.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copyFaviconFile($packagePath): void
    {
        $this->copyFilesFromSourceToInstallationByFilter(
            $packagePath,
            self::FAVICON_FILE
        );
    }

    /**
     * Copy shop's offline/maintenance page from package.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copyOfflineFile($packagePath): void
    {
        $this->copyFilesFromSourceToInstallationByFilter(
            $packagePath,
            self::OFFLINE_FILE
        );
    }

    private function copyEnvDistFile(string $packagePath): void
    {
        $sourceFilePath = Path::join($packagePath, self::ENV_DIST_FILE);
        $projectRootPath = dirname($this->getRootDirectory());
        $targetFilePath = Path::join($projectRootPath, self::ENV_DIST_FILE);

        $this->copyFileIfIsMissing($sourceFilePath, $targetFilePath);
    }

    /**
     * Copy shop's robots exclusion files from package.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copyRobotsExclusionFiles($packagePath): void
    {
        $this->copyFilesFromSourceToInstallationByFilter(
            $packagePath,
            self::ROBOTS_EXCLUSION_FILTER
        );
    }

    /**
     * Return package directory which points to shop's source directory.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function getPackageDirectoryOfShopSource(string $packagePath): string
    {
        return Path::join($packagePath, self::SHOP_SOURCE_DIRECTORY);
    }

    /**
     * Return target directory where shop's source files needs to be copied.
     *
     * @return string
     */
    private function getTargetDirectoryOfShopSource()
    {
        return $this->getRootDirectory();
    }

    /**
     * Copy files from source to installation by filter.
     *
     * @param string $packagePath
     */
    private function copyFilesFromSourceToInstallationByFilter($packagePath, string $filter): void
    {
        $sourceDirectory    = $this->getPackageDirectoryOfShopSource($packagePath);
        $filteredFiles      = $this->getFilteredFiles($sourceDirectory, $filter);

        foreach ($filteredFiles as $packageFilePath) {
            $installationFilePath = $this->getAbsoluteFilePathFromInstallation(
                $sourceDirectory,
                $packageFilePath
            );

            $this->copyFileIfIsMissing($packageFilePath, $installationFilePath);
        }
    }

    /**
     * Copy file if is missing.
     *
     * @param string $sourcePath
     * @param string $destinationPath
     */
    private function copyFileIfIsMissing($sourcePath, $destinationPath): void
    {
        if (!file_exists($destinationPath)) {
            CopyGlobFilteredFileManager::copy(
                $sourcePath,
                $destinationPath
            );
        }
    }

    /**
     * Return filtered files.
     *
     * @param   string $filter
     * @return  GlobIterator
     */
    private function getFilteredFiles(string $directory, $filter)
    {
        return new GlobIterator(Path::join($directory, $filter));
    }

    /**
     * Return absolute path to file from installation.
     *
     *
     */
    private function getAbsoluteFilePathFromInstallation(
        string $sourcePackageDirectory,
        string $absolutePathToFileFromPackage
    ): string {
        $installationDirectoryOfShopSource = $this->getTargetDirectoryOfShopSource();

        $relativePathOfSourceFromPackage = Path::makeRelative(
            $absolutePathToFileFromPackage,
            $sourcePackageDirectory
        );

        return Path::join(
            $installationDirectoryOfShopSource,
            $relativePathOfSourceFromPackage
        );
    }

    private function getPackageTypeDescription(): string
    {
        return 'OXID eShop package';
    }
}
