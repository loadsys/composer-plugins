<?php
/**
 * PhpcsCodingStandardHook
 *
 * Provide both utility functions and end-user hooks for post-processing
 * composer packages that contain Coding Standards folders to assist
 * with "installing" them into the
 * `VENDOR/squizlabs/php_codesniffer/CodeSniffer/Standards/` folder.
 *
 * Used by the PhpcsCodingStandardInstaller class.
 */

namespace Loadsys\Composer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;


use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use Composer\Util\Filesystem;
use \RecursiveCallbackFilterIterator;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesytem;




if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

/**
 * PHP CodeSniffer Coding Standard Hook
 *
 * Provides post-install-cmd hook actions to allow the automatic
 * installation of non-compatible composer packages containing PHPCS
 * Coding Standards that don't define the `phpcs-coding-standard` type.
 */
class PhpcsCodingStandardHook {

	/**
	 * The `type` used in a composer.json file to identify packages
	 * containing PHPCS Coding Standards.
	 *
	 * @var string
	 */
	const PHPCS_PACKAGE_TYPE = 'phpcs-coding-standard';

	/**
	 * Intended for use as a post-install-cmd script.
	 *
	 * Scans each package as it is installed for subfolders containing
	 * `ruleset.xml` files, and mirrors those folders into the
	 * CodeSniffer/Standards/ folder of the squizlabs/php_codesniffer
	 * package, if present.
	 *
	 * No-op if there are no `ruleset.xml` files or the PHP CodeSniffer
	 * package is not installed, making it safe to run on every package.
	 *
	 * @param \Composer\Installer\PackageEvent $event The composer Package event being fired.
	 * @return void
	 */
//@TODO: This should really be set to trigger on post-install and post-update, and loop over ALL locally installed packages, instead of firing repeatedly as each package goes in. That would make it more likely that the squizlabs/php_codesniffer folder will already be present.
	public static function postPackageInstall(PackageEvent $event) {
		$installedPackage = $event->getOperation()->getPackage();

		// If the package defines the correct type,
		// it will have already been copied by the Installer.
		if ($installedPackage->getType() === self::PHPCS_PACKAGE_TYPE) {
			return;
		}

		// Otherwise, check for Coding Standard folders and copy them.
		// (This is a relatively quick no-op if there are no
		// `ruleset.xml` files in the package.)
		self::mirrorCodingStandardFolders($event);

//@TODO: Experiment with alternate approach of writing the "current" packages path into CodeSniffer.conf using new self::configInstalledPathAdd() method below.
	}

	/**
	 * Mirror (copy or delete, only as necessary) items from the installed
	 * package's release/ folder into the target directory.
	 *
	 * @param \Composer\Installer\PackageEvent $event The composer Package event being fired.
	 * @return void
	 */
	public static function mirrorCodingStandardFolders(PackageEvent $event) {
		$package = $event->getOperation()->getPackage();
		$packageBasePath = $package->getComposer()->getInstallationManager()->getInstallPath($package);
		$rulesets = self::findRulesetFolders($packageBasePath);
		$destDir = self::findCodesnifferRoot($event);

		// No-op if no ruleset.xml's found or squizlabs/php_codesniffer not installed.
		if (empty($rulesets) || !$destDir) {
			return;
		}

		// Return true if the first part of the subpath for
		// the current file exists in the accept array.
		$acceptFunc = function ($current, $key, $iterator) use ($rulesets) {
			$pathComponents = explode(DS, $iterator->getSubPathname());
			return in_array($pathComponents[0], $rulesets);
		};

		// Build up an iterator that will only select files
		// within folders containing `ruleset.xml files.
		$dirIterator = new RecursiveDirectoryIterator(
			$packageBasePath,
			RecursiveDirectoryIterator::SKIP_DOTS
		);
		$filterIterator = new RecursiveCallbackFilterIterator(
			$dirIterator,
			$acceptFunc
		);
		$codingStandardsFolders = new RecursiveIteratorIterator(
			$filterIterator,
			RecursiveIteratorIterator::SELF_FIRST
		);

		// Iterate over all of the select files,
		// copying them to the CodeSniffer/Standards/ folder.
		$filesystem = new SymfonyFilesytem();
		$filesystem->mirror(
			$packageBasePath,
			$destDir,
			$codingStandardsFolders,
			array('override' => true)
		);
	}

	//@TODO: Write the removeStandards() method. Must take a single PackageEvent, scan that package for ruleset.xml folders, then remove the same ones from CodeSniffer/Standards/ if present. (Alternate approach is to remove the path to "this" package from CodeSniffer.conf using the new self::configInstalledPathRemove() method below.
	public function removeStandards(PackageEvent $event) {
	}

	//@TODO: doc block
	public static function configInstalledPathAdd($path) {
		//@TODO: write and test this:
		$installedPaths = self::readInstalledPaths();
		$installedPaths[] = $path;
		return self::saveInstalledPaths($installedPaths);
	}

	//@TODO: doc block
	public static function configInstalledPathRemove($path) {
		//@TODO: write and test this:
		$installedPaths = self::readInstalledPaths();
		if ($key = array_search($path, $installedPaths)) {
			unset($installedPaths[$key]);
		}
		return self::saveInstalledPaths($installedPaths);
	}

	/**
	 * Scan $basePath for folders that contain `ruleset.xml` files.
	 *
	 * Return an array of partial paths (from $basePath) for all
	 * matching folders found.
	 *
	 * @param string $basePath A filesystem path without trailing slash to scan for folders with `ruleset.xml` files.
	 * @return array Array of partial file paths (from $basePath) to folders containing a ruleset.xml, no leading or trailing slashes. Empty array if none found.
	 */
	protected static function findRulesetFolders($basePath) {
		$rulesetFolders = array_map(function ($v) use ($basePath){
			return dirname(str_replace($basePath . DS, '', $v));
		}, glob($basePath . DS . '*' . DS . 'ruleset.xml'));

		return $rulesetFolders;
	}

	/**
	 * Attempt to locate the squizlabs/php_codesniffer/standards folder.
	 *
	 * Return the full system path if found, no trailing slash.
	 *
	 * @param Composer\Installer\PackageEvent $event Current composer event. Used to get access to the InstallationManager.
	 * @return string|false Full system path to the PHP CodeSniffer's "standards/" folder, false if not found.
	 */
	protected static function findCodesnifferRoot(PackageEvent $event) {
		$phpcsPackage = new Package('squizlabs/php_codesniffer', '2.0', '');
		$path = $event->getComposer()->getInstallationManager()->getInstallPath($phpcsPackage);

		$path .= DS . 'CodeSniffer' . DS . 'Standards';
		if (!is_readable($path)) {
			return false;
		}

		return $path;
	}

	//@TODO: doc block
	protected static function readInstalledPaths() {
		self::codeSnifferInit();
		$pathsString = PHP_CodeSniffer::getInstalledStandardPaths();
		if (is_null($pathsString)) {
			return array();
		}
		return explode(',', $pathsString);
	}

	//@TODO: doc block
	protected static function saveInstalledPaths(array $paths) {
		return PHP_CodeSniffer::setConfigData('installed_paths', implode(',', array_unique($paths)));
	}

	//@TODO: doc block
	protected static function codeSnifferInit() {
		if (!class_exists('PHP_CodeSniffer')) {
			$composerInstall = dirname(dirname(dirname(__FILE__))) . '/vendor/squizlabs/php_codesniffer/CodeSniffer.php';
			if (file_exists($composerInstall)) {
				require_once $composerInstall;
			} else {
				require_once 'PHP/CodeSniffer.php';
			}
		}
	}
}
