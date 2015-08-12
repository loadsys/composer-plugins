<?php
namespace Loadsys\Composer\Test\TestCase\Puphpet;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Package\Link;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Package\Version\VersionParser;
use Composer\Repository\InstalledArrayRepository;
use Composer\Repository\RepositoryManager;
use Loadsys\Composer\Puphpet\ReleaseInstaller;

class ReleaseInstallerTest extends \PHPUnit_Framework_TestCase {
	private $package;
	private $composer;
	private $io;

	/**
	 * setUp
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->package = new Package('CamelCased', '1.0', '1.0');
		$this->io = $this->getMock('Composer\IO\PackageInterface');
		$this->composer = new Composer();
		$this->composer->setConfig(new Config(false));
	}

	/**
	 * tearDown
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->package);
		unset($this->io);
		unset($this->composer);

		parent::tearDown();
	}

	/**
	 * testNothing
	 *
	 * @return void
	 */
	public function testNothing() {
		$this->markTestIncomplete('@TODO: No tests written for Puphpet\ReleaseInstaller.');
	}
}
