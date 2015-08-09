<?php
/**
 * PhpcsCodingStandardInstaller
 *
 * Ensures that a composer package with `type=phpcs-coding-standard` in its composer.json file will have any subfolders that contain a `ruleset.xml` file  copied into the `VENDOR/squizlabs/php_codesniffer/Standards/` folder.
 */

namespace Loadsys\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use \RecursiveCallbackFilterIterator;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Filesystem;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Installer and event handler.
 *
 *
 */
class PhpcsCodingStandardInstaller extends LibraryInstaller {

	/**
	 * Initializes base installer.
	 *
	 * @param IOInterface $io
	 * @param Composer $composer
	 */
	public function __construct(IOInterface $io, Composer $composer) {
		$this->io = $io;
		$this->composer = $composer;
	}

	/**
	 * Defines the `type`s of composer packages to which this installer applies.
	 *
	 * A project's composer.json file must specify `"type": "phpcs-coding-standard"`
	 * in order to trigger this installer.
	 *
	 * @param string $packageType The `type` specified in the consuming project's composer.json.
	 * @return bool True if this installer should be activated for the package in question, false if not.
	 */
	public function supports($packageType) {
		return ('phpcs-coding-standard' === $packageType);
	}

	/**
	 * Scan $basePath for folders that contain `ruleset.xml` files.
	 *
	 * Return an array of partial paths (from $basePath) for all matching folders found.
	 *
	 * @param string $basePath A filesystem path without trailing slash to scan for folders with `ruleset.xml` files.
	 * @return array Array of partial file paths (from $basePath) to folders containing a ruleset.xml, no leading or trailing slashes. Empty array if none found.
	 */
	protected function findRulesetFolders($basePath) {
		$rulesetFolders = array_map(function ($v) use ($basePath){
			return dirname(str_replace("{$basePath}/", '', $v));
		}, glob("{$basePath}/*/ruleset.xml"));

		return $rulesetFolders;
	}

	/**
	 * Attempt to locate the squizlabs/php_codesniffer/standards folder.
	 *
	 * Return the full system path if found, no trailing slash.
	 *
	 * @return string Full system path to the PHP CodeSniffer's "standards/" folder.
	 */
	protected function findCodesnifferRoot() {
		// approach 1
// 		$vendorDir = $this->composer->getConfig()->get('vendor-dir');
// 		return $vendorDir . DS . 'squizlabs/php_codesniffer/CodeSniffer/Standards';


		// approach 2
		$phpcsPackage = new \Composer\Package\Package('squizlabs/php_codesniffer', '~2.0', '2.0');
		return $this->composer->getInstallationManager()->getInstallPath($phpcsPackage). DS . 'CodeSniffer/Standards';

//@TODO: verify the folder exists.

		// approach 3
		// wet get ALL installed packages
// 		$packages = $event->getComposer()->getRepositoryManager()
// 			->getLocalRepository()->getPackages();
// 		$installationManager = $event->getComposer()->getInstallationManager();
//
// 		foreach ($packages as $package) {
// 			$installPath = $installationManager->getInstallPath($package);
// 			//do my process here
// 		}


		// approach 4
// 		$repositoryManager = $composer->getRepositoryManager();
// 		$installationManager = $composer->getInstallationManager();
// 		$localRepository = $repositoryManager->getLocalRepository();
//
// 		$packages = $localRepository->getPackages();
// 		foreach ($packages as $package) {
// 			if ($package->getName() === 'willdurand/geocoder') {
// 				$installPath = $installationManager->getInstallPath($package);
// 				break;
// 			}
// 		}
	}

	/**
	 * Override LibraryInstaller::installCode() to hook in additional post-download steps.
	 *
	 * @param InstalledRepositoryInterface $repo	repository in which to check
	 * @param PackageInterface			 $package package instance
	 */
	protected function installCode(\Composer\Package\PackageInterface $package) {
		parent::installCode($package);

		if (!$this->supports($package->getType())) {
			return;
		}

		$this->mirrorCodingStandardFolders($package);
	}

	/**
	 * Mirror (copy or delete, only as necessary) items from the installed
	 * package's release/ folder into the target directory.
	 *
	 */
	protected function mirrorCodingStandardFolders($package) {
		$packageBasePath = $this->getInstallPath($package);
		$rulesets = $this->findRulesetFolders($packageBasePath);
		$destDir = $this->findCodesnifferRoot();

		// Return true if the first part of the subpath for the current file exists in the accept array.
		$acceptFunc = function ($current, $key, $iterator) use ($rulesets) {
			$pathComponents = explode(DS, $iterator->getSubPathname());
			return in_array($pathComponents[0], $rulesets);
		};
		$dirIterator = new RecursiveDirectoryIterator($packageBasePath, RecursiveDirectoryIterator::SKIP_DOTS);
		$filterIterator = new RecursiveCallbackFilterIterator($dirIterator, $acceptFunc);
		$codingStandardsFolders = new RecursiveIteratorIterator($filterIterator, RecursiveIteratorIterator::SELF_FIRST);

		$filesystem = new Filesystem();
		$filesystem->mirror($packageBasePath, $destDir, $codingStandardsFolders, ['override' => true]);
	}
}
