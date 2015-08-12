<?php

namespace Loadsys\Composer;

// Needed for PluginInterface:
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Plugin entry point.
 *
 */
class LoadsysComposerPlugin implements PluginInterface {

	/**
	 * Activate the plugin (called from {@see \Composer\Plugin\PluginManager})
	 *
	 * All we need to do is register our custom installer classes.
	 *
	 * @param \Composer\Composer $composer The active instance of the composer base class.
	 * @param \Composer\IO\IOInterface $io The I/O instance.
	 * @return void
	 */
	public function activate(Composer $composer, IOInterface $io) {
		$puphpetReleaseInstaller = new Puphpet\ReleaseInstaller($io, $composer);
		$composer->getInstallationManager()->addInstaller($puphpetReleaseInstaller);

		$phpcsCodingStandardInstaller = new PhpCodesniffer\CodingStandardInstaller($io, $composer);
		$composer->getInstallationManager()->addInstaller($phpcsCodingStandardInstaller);
	}
}
