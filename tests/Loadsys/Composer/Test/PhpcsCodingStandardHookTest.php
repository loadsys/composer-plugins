<?php
/**
 * Tests for the PhpcsCodingStandardHook class.
 *
 * Verify the individual components available for a la carte use.
 */

namespace Loadsys\Composer\Test;

use Composer\Installer\PackageEvent;
use Loadsys\Composer\PhpcsCodingStandardHook;

/**
 * Child class to expose protected methods for direct testing.
 */
class TestPhpcsCodingStandardHook extends PhpcsCodingStandardHook {
	public static function findRulesetFolders($basePath) {
		return parent::findRulesetFolders($basePath);
	}
	public static function findCodesnifferRoot(PackageEvent $event) {
		return parent::findCodesnifferRoot($event);
	}
}

/**
 * PhpcsCodingStandardHook Test
 */
class PhpcsCodingStandardHookTest extends \PHPUnit_Framework_TestCase {
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
        $this->phpcsInstallDir = sys_get_temp_dir() . md5(__FILE__ . time());
        $this->standardsInstallDir = $this->phpcsInstallDir . '/CodeSniffer/Standards';
        mkdir($this->standardsInstallDir, 0777, true);

        $this->sampleDir = dirname(dirname(dirname(dirname(__FILE__)))) . '/samples';

        $this->package = new \Composer\Package\Package('CamelCased', '1.0', '1.0');
        $this->io = $this->getMock('\Composer\IO\IOInterface');
        $this->composer = new \Composer\Composer();
        $this->composer->setConfig(new \Composer\Config(false, $this->baseDir));
        $this->composer->setInstallationManager(new \Composer\Installer\InstallationManager());
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
        $this->removeDir($this->phpcsInstallDir);

        parent::tearDown();
    }

    /**
     * Helper function to recursively delete temporary directories created for tests.
     *
     * @return void
     */
	protected function removeDir($d) {
		$i = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($d, \FilesystemIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($i as $path) {
			$path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
		}
		rmdir($d);
	}

    /**
     * test postPackageInstall()
     *
     * @return void
     */
    public function testPostPackageInstallMatchingType() {
    	$this->marktestIncomplete('@TODO: Write a test where the package type is already phpcs-coding-standard');
    }

    /**
     * test postPackageInstall()
     *
     * @return void
     */
    public function testPostPackageInstallNoStandards() {
    	$this->marktestIncomplete('@TODO: Write a test where the package does not have any stanrds to install.');
    }

    /**
     * test postPackageInstall()
     *
     * @return void
     */
    public function testPostPackageInstallSuccessful() {
    	$this->marktestIncomplete('@TODO: Write a test where the package type is not phpcs-coding-standard and has standards to install.');
    }

    /**
     * test mirrorCodingStandardFolders()
     *
     * @return void
     */
    public function testMirrorCodingStandardFoldersSuccessful() {
        $event = $this->getMockBuilder('\Composer\Installer\PackageEvent')
			->disableOriginalConstructor()
			->setMethods(array('getOperation', 'getPackage', 'getComposer', 'getInstallationManager', 'getInstallPath'))
			->getMock();
		$event->method('getOperation')->will($this->returnSelf());
		$event->method('getPackage')->will($this->returnSelf());
		$event->method('getComposer')->will($this->returnSelf());
		$event->method('getInstallationManager')->will($this->returnSelf());
		$event->expects($this->at(4))
			->method('getInstallPath')
			->will($this->returnValue($this->sampleDir));
		$event->expects($this->at(7))
			->method('getInstallPath')
			->will($this->returnValue($this->phpcsInstallDir));

        $expected = array(
        	'CodingStandardOne',
        	'SecondStandard',
        );

        $result = TestPhpcsCodingStandardHook::mirrorCodingStandardFolders($event);

        foreach ($expected as $standard) {
			$this->assertTrue(
				is_readable($this->standardsInstallDir . DS . $standard . DS . 'ruleset.xml'),
				"Folder `$standard` containing ruleset.xml should be copied to Standards/ folder."
			);
        }
    }

    /**
     * test mirrorCodingStandardFolders()
     *
     * @return void
     */
    public function testMirrorCodingStandardFoldersNoDest() {
        $event = $this->getMockBuilder('\Composer\Installer\PackageEvent')
			->disableOriginalConstructor()
			->setMethods(array('getOperation', 'getPackage', 'getComposer', 'getInstallationManager', 'getInstallPath'))
			->getMock();
		$event->method('getOperation')->will($this->returnSelf());
		$event->method('getPackage')->will($this->returnSelf());
		$event->method('getComposer')->will($this->returnSelf());
		$event->method('getInstallationManager')->will($this->returnSelf());
		$event->expects($this->at(4))
			->method('getInstallPath')
			->will($this->returnValue($this->sampleDir));
		$event->expects($this->at(7))
			->method('getInstallPath')
			->will($this->returnValue(false));

        $expected = array(
        	'CodingStandardOne',
        	'SecondStandard',
        );

        $result = TestPhpcsCodingStandardHook::mirrorCodingStandardFolders($event);

        foreach ($expected as $standard) {
			$this->assertFalse(
				is_readable($this->standardsInstallDir . DS . $standard . DS . 'ruleset.xml'),
				'No folders should be copied when destination dir is not found.'
			);
        }
    }

    /**
     * test findRulesetFolders()
     *
     * @return void
     */
    public function testFindRulesetFolders() {
        $expected = array(
        	'CodingStandardOne',
        	'SecondStandard',
        );
        $result = TestPhpcsCodingStandardHook::findRulesetFolders($this->sampleDir);

        $this->assertEquals(
        	$expected,
        	$result,
        	'Only folders containing a ruleset.xml should be returned.'
        );
    }

    /**
     * test findCodesnifferRoot()
     *
     * @return void
     */
    public function testFindCodesnifferRootExists() {
        $event = $this->getMockBuilder('\Composer\Installer\PackageEvent')
			->disableOriginalConstructor()
			->setMethods(array('getComposer', 'getInstallationManager', 'getInstallPath'))
			->getMock();
		$event->method('getComposer')->will($this->returnSelf());
		$event->method('getInstallationManager')->will($this->returnSelf());
		$event->expects($this->once())
			->method('getInstallPath')
			->will($this->returnValue($this->phpcsInstallDir));

        $result = TestPhpcsCodingStandardHook::findCodesnifferRoot($event);

        $this->assertEquals(
        	$this->standardsInstallDir,
        	$result,
        	'Full path to the existing Standards/ folder should be returned.'
        );
    }

    /**
     * test findCodesnifferRoot()
     *
     * @return void
     */
    public function testFindCodesnifferRootDoesNotExist() {
        $event = $this->getMockBuilder('\Composer\Installer\PackageEvent')
			->disableOriginalConstructor()
			->setMethods(array('getComposer', 'getInstallationManager', 'getInstallPath'))
			->getMock();
		$event->method('getComposer')->will($this->returnSelf());
		$event->method('getInstallationManager')->will($this->returnSelf());
		$event->expects($this->once())
			->method('getInstallPath')
			->will($this->returnValue('does-not-exist'));

        $result = TestPhpcsCodingStandardHook::findCodesnifferRoot($event);

        $this->assertFalse(
        	$result,
        	'False shouldbe returned for a non-existent path.'
        );
    }
}
