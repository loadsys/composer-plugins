<?php
/**
 * PhpcsCodingStandardInstaller
 *
 * Ensures that a composer package with `type=phpcs-coding-standard`
 * in its composer.json file will have any subfolders that contain
 * a `ruleset.xml` file  copied into the
 * `VENDOR/squizlabs/php_codesniffer/CodeSniffer/Standards/` folder.
 */

namespace Loadsys\Composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Loadsys\Composer\PhpcsCodingStandardHook;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

/**
 * PHP CodeSniffer Coding Standard Installer
 *
 * Hooks the ::installCode() step to perform additional detection
 * of Coding Standard folders in the current package, and copies
 * them to the CodeSniffer/Standards folder.
 */
class PhpcsCodingStandardInstaller extends LibraryInstaller {

	/**
	 * Defines the `type`s of composer packages to which this installer applies.
	 *
	 * A project's composer.json file must specify
	 * `"type": "phpcs-coding-standard"` in order to trigger this
	 * installer.
	 *
	 * @param string $packageType The `type` specified in the consuming project's composer.json.
	 * @return bool True if this installer should be activated for the package in question, false if not.
	 */
	public function supports($packageType) {
		return ($packageType === PhpcsCodingStandardHook::PHPCS_PACKAGE_TYPE);
	}

	/**
	 * Override LibraryInstaller::installCode() to hook in additional post-download steps.
	 *
	 * @param \Composer\Package\PackageInterface $package Package instance.
	 * @return void
	 */
	protected function installCode(PackageInterface $package) {
		parent::installCode($package);

		if (!$this->supports($package->getType())) {
			return;
		}

		PhpcsCodingStandardHook::mirrorCodingStandardFolders($package);
	}

	/**
	 * Override LibraryInstaller::updateCode() to hook in additional post-update steps.
	 *
	 * @param \Composer\Package\PackageInterface $initial Existing Package instance.
	 * @param \Composer\Package\PackageInterface $target New Package instance.
	 * @return void
	 */
	protected function updateCode(PackageInterface $initial, PackageInterface $target) {
		//@TODO: Adapt to re-copy the Standards folders.
		//parent::updateCode($initial, $target);

		$initialDownloadPath = $this->getInstallPath($initial);
		$targetDownloadPath = $this->getInstallPath($target);
		if ($targetDownloadPath !== $initialDownloadPath) {
			// if the target and initial dirs intersect, we force a remove + install
			// to avoid the rename wiping the target dir as part of the initial dir cleanup
			if (substr($initialDownloadPath, 0, strlen($targetDownloadPath)) === $targetDownloadPath
				|| substr($targetDownloadPath, 0, strlen($initialDownloadPath)) === $initialDownloadPath
			) {
				$this->removeCode($initial);
				$this->installCode($target);
				return;
			}
			$this->filesystem->rename($initialDownloadPath, $targetDownloadPath);
		}
		$this->downloadManager->update($initial, $target, $targetDownloadPath);
	}

	/**
	 * Override LibraryInstaller::removeCode() to hook in additional post-update steps.
	 *
	 * @param \Composer\Package\PackageInterface $package Package instance.
	 * @return void
	 */
	protected function removeCode(PackageInterface $package) {
		//@TODO: Adapt to remove the copied Standards folders.
		//parent::removeCode($package);

		$downloadPath = $this->getPackageBasePath($package);
		$this->downloadManager->remove($package, $downloadPath);
	}
}
