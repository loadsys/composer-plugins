<?php
/**
 * Tests for the PhpcsCodingStandardInstaller.
 *
 * Verify that the installer engages for the correct plugin type, is
 * able to locate coding standard folders correctly, and can locate
 * the proper destination folder for them.
 */

namespace Loadsys\Composer\Test;

use Loadsys\Composer\PhpcsCodingStandardInstaller;

/**
 * Child class to expose protected methods for direct testing.
 */
class TestPhpcsCodingStandardInstaller extends PhpcsCodingStandardInstaller {
	public function findRulesetFolders($basePath) {
		return parent::findRulesetFolders($basePath);
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
        $this->package = new \Composer\Package\Package('CamelCased', '1.0', '1.0');
        $this->io = $this->getMock('\Composer\IO\IOInterface');
        $this->composer = new \Composer\Composer();
        $this->composer->setConfig(new \Composer\Config(false, $this->baseDir));
        $this->composer->setInstallationManager(new \Composer\Installer\InstallationManager());
        $this->Installer = new TestPhpcsCodingStandardInstaller($this->io, $this->composer);
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
        unset($this->composer);

        parent::tearDown();
    }

    /**
     * testNothing
     *
     * @return void
     */
    public function testNothing() {
        $this->markTestIncomplete('@TODO: No tests written for PhpcsCodingStandardInstaller.');
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
    public function testInstallCode() {
        $sampleDir = dirname(dirname(dirname(dirname(__FILE__)))) . '/samples';
        $expected = array(
        	'CodingStandardOne',
        	'SecondStandard',
        );
        $package = $this->getMock('@TODO');

        $result = $this->Installer->installCode($package);

        $this->assertEquals(
        	$expected,
        	$result,
        	'Only folders containing a ruleset.xml should be returned.'
        );
    }
}
