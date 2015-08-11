<?php
/**
 * Tests for the PhpcsCodingStandardInstaller.
 *
 * Verify that the installer engages for the correct plugin type, is
 * able to locate coding standard folders correctly, and can locate
 * the proper destination folder for them.
 */

namespace Loadsys\Composer\Test;

use Composer\Package\PackageInterface;
use Loadsys\Composer\PhpcsCodingStandardHook;
use Loadsys\Composer\PhpcsCodingStandardInstaller;

/**
 * Test stub of the PhpcsCodingStandardHook class.
 *
 * Instead of `use Loadsys\Composer\PhpcsCodingStandardHook;`, define
 * a class with known operations to isolate the installer from the
 * filesystem side-effects for testing. Methods set internal
 * properties for test inspection.
 *
 * This class **MUST** be declared before the "real" class would be
 * autoloaded by our SUT.
 */
class StubPhpcsCodingStandardHook {
	public $calls = array();
	public function __call($name, $arguments) {
		$this->calls[$name] = $arguments;
		return $name;
	}
}

/**
 * Expose protected methods for direct testing, since this class doesn't
 * otherwise expose public interfaces to us.
 */
class TestPhpcsCodingStandardInstaller extends PhpcsCodingStandardInstaller {
	public function installCode(PackageInterface $package) {
		return parent::installCode($package);
	}
	public function updateCode(PackageInterface $initial, PackageInterface $target) {
		return parent::updateCode($initial, $target);
	}
	public function removeCode(PackageInterface $package) {
		return parent::removeCode($package);
	}
}

/**
 * PhpcsCodingStandardInstaller Test
 */
class PhpcsCodingStandardInstallerTest extends \PHPUnit_Framework_TestCase {
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

        $this->baseDir = getcwd();
        $this->io = $this->getMock('\Composer\IO\IOInterface');
        $this->package = $this->getMock('\Composer\Package\Package',
        	array('getType'),
        	array('CamelCased', '1.0', '1.0')
        );
        $downloadManager = $this->getMock('\Composer\Downloader\DownloadManager', array(), array($this->io));
        $installationManager = $this->getMock('\Composer\Installer\InstallationManager', array(), array());
        $this->hook = new StubPhpcsCodingStandardHook();
        $this->composer = new \Composer\Composer();
        $this->composer->setConfig(new \Composer\Config(false, $this->baseDir));
        $this->composer->setInstallationManager($installationManager);
        $this->composer->setDownloadManager($downloadManager);
        $this->Installer = new TestPhpcsCodingStandardInstaller(
        	$this->io,
        	$this->composer,
        	'library',
        	new \Composer\Util\Filesystem(), $this->hook
        );
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
        unset($this->hook);
        unset($this->Installer);

        parent::tearDown();
    }

    /**
     * test supports()
     *
     * @return void
     */
    public function testSupports() {
        $this->assertTrue(
        	$this->Installer->supports('phpcs-coding-standard'),
        	'Coding standard installer should activate for `type=phpcs-coding-standard`.'
        );
        $this->assertFalse(
        	$this->Installer->supports('anything-else'),
        	'Coding standard installer should not activate for unrecognized package types.'
        );
    }

    /**
     * test installCode()
     *
     * @return void
     */
    public function testInstallCodeCorrectType() {
        $this->package->expects($this->any())
        	->method('getType')
        	->willReturn(PhpcsCodingStandardHook::PHPCS_PACKAGE_TYPE);

        $result = $this->Installer->installCode($this->package);

        $this->assertEquals(
        	null,
        	$result,
        	'Return value should always be null.'
        );
        $this->assertEquals(
        	array('mirrorCodingStandardFolders' => array($this->composer, null)),
        	$this->Installer->hook->calls,
        	'Our mocked static class should have registered a single call to mirrorCodingStandardFolders().'
        );
    }

    /**
     * test installCode()
     *
     * @return void
     */
    public function testInstallCodeIncorrectType() {
        $this->package->expects($this->any())
        	->method('getType')
        	->willReturn('not-the-correct-type');

        $result = $this->Installer->installCode($this->package);

        $this->assertEquals(
        	null,
        	$result,
        	'Return value should always be null.'
        );
        $this->assertEquals(
        	array(),
        	$this->Installer->hook->calls,
        	'There should be no call to our stubbed static class.'
        );
    }

    /**
     * test updateCode()
     *
     * @return void
     */
    public function testUpdateCode() {
    }

    /**
     * test removeCode()
     *
     * @return void
     */
    public function testRemoveCode() {
    }
}
