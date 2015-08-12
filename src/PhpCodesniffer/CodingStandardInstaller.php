<?php
/**
 * PhpCodesniffer\CodingStandardInstaller
 *
 * Ensures that a composer package with `type=phpcs-coding-standard`
 * in its composer.json file will have any subfolders that contain
 * a `ruleset.xml` file  copied into the
 * `VENDOR/squizlabs/php_codesniffer/CodeSniffer/Standards/` folder
 * during installation, and removed during uninstallation.
 */

namespace Loadsys\Composer\PhpCodesniffer;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Util\Filesystem;
use Loadsys\Composer\PhpCodesniffer\CodingStandardHook;

/**
 * PHP CodeSniffer Coding Standard "Copying" Installer
 *
 * Hooks the ::installCode(), ::updateCode() and ::removeCode() steps
 * to perform additional detection of Coding Standard folders in the
 * current package, and copies them to the CodeSniffer/Standards folder.
 */
class CodingStandardInstaller extends LibraryInstaller {

    /**
     * Initializes library installer.
     *
     * @param IOInterface $io
     * @param Composer    $composer
     * @param string      $type
     * @param Filesystem  $filesystem
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'library', Filesystem $filesystem = null, $hook = null) {
    	parent::__construct($io, $composer, $type, $filesystem);
    	$this->hook = (!is_null($hook) ? $hook : new CodingStandardHook());
	}

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
		return ($packageType === CodingStandardHook::PHPCS_PACKAGE_TYPE);
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

		$installPath = $this->composer->getInstallationManager()->getInstallPath($package);
		$this->hook->mirrorCodingStandardFolders($this->composer, $installPath);
	}

	/**
	 * Override LibraryInstaller::updateCode() to hook in additional post-update steps.
	 *
	 * @param \Composer\Package\PackageInterface $initial Existing Package instance.
	 * @param \Composer\Package\PackageInterface $target New Package instance.
	 * @return void
	 */
	protected function updateCode(PackageInterface $initial, PackageInterface $target) {
		parent::updateCode($initial, $target);

		if (!$this->supports($package->getType())) {
			return;
		}

		$installPath = $this->composer->getInstallationManager()->getInstallPath($target);
		$this->hook->mirrorCodingStandardFolders($this->composer, $installPath);
	}

	/**
	 * Override LibraryInstaller::removeCode() to hook in additional post-update steps.
	 *
	 * @param \Composer\Package\PackageInterface $package Package instance.
	 * @return void
	 */
	protected function removeCode(PackageInterface $package) {
		if ($this->supports($package->getType())) {
			$installPath = $this->composer->getInstallationManager()->getInstallPath($package);
			$this->hook->deleteCodingStandardFolders($this->composer, $installPath);
		}

		parent::removeCode($package);
	}
}
