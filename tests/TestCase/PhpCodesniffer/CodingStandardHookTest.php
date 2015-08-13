<?php
/**
 * Tests for the PhpCodesniffer\CodingStandardHook class.
 *
 * Verify the individual components available for a la carte use.
 */

namespace Loadsys\Composer\Test\TestCase\PhpCodesniffer;

use Composer\Composer;
use Composer\Installer\PackageEvent;
use Loadsys\Composer\PhpCodesniffer\CodingStandardHook;

/**
 * Child class to expose protected methods for direct testing.
 */
class TestCodingStandardHook extends CodingStandardHook {
	public static function findRulesetFolders($basePath) {
		return parent::findRulesetFolders($basePath);
	}
	public static function findStandardsFolder(Composer $composer) {
		return parent::findStandardsFolder($composer);
	}
}

/**
 * CodingStandardHook Test
 */
class CodingStandardHookTest extends \PHPUnit_Framework_TestCase {
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

		$this->sampleDir = $this->baseDir . '/tests/samples';

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
	 * Helper method to recursively delete temporary directories created for tests.
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
	 * Helper method to set up the proper paths for the
	 * CodeSniffer/Standards/ and currently-being-installed-package
	 * directories.
	 *
	 * The returned $composer mock will return each path for the
	 * matching method call to the mock. Remember that PHPUnit counts
	 * ALL mocked methods in sequence!
	 *
	 * @param array $installedPaths Numeric keys are the `at()` calls to `getInstallPath` where the matching string value is returned as the path.
	 * @return array [mocked \Composer\Composer, mocked \Composer\Installer\PackageEvent]
	 */
	protected function mockComposerAndEvent($getInstallPaths) {
		$composer = $this->getMock('\Composer\Composer', ['getInstallationManager', 'getInstallPath']);
		$composer->method('getInstallationManager')->will($this->returnSelf());
		foreach ($getInstallPaths as $at => $path) {
			$composer->expects($this->at($at))
				->method('getInstallPath')
				->willReturn($path);
		}

		$event = $this->getMockBuilder('\Composer\Script\Event')
			->disableOriginalConstructor()
			->setMethods(['getComposer'])
			->getMock();
		$event->method('getComposer')->willReturn($composer);

		return [$composer, $event];
	}
	/**
	 * test postPackageInstall()
	 *
	 * @return void
	 */
	public function testPostInstallMatchingType() {
		$this->marktestIncomplete('@TODO: Write a test where the package type is already phpcs-coding-standard');
	}

	/**
	 * test postPackageInstall()
	 *
	 * @return void
	 */
	public function testPostInstallNoStandards() {
		$this->marktestIncomplete('@TODO: Write a test where the package does not have any stanrds to install.');
	}

	/**
	 * test postPackageInstall()
	 *
	 * @return void
	 */
	public function testPostInstallSuccessful() {
		$this->marktestIncomplete('@TODO: Write a test where the package type is not phpcs-coding-standard and has standards to install.');
	}

	/**
	 * test mirrorCodingStandardFolders()
	 *
	 * @return void
	 */
	public function testMirrorCodingStandardFoldersSuccessful() {
		list($composer, $event) = $this->mockComposerAndEvent([
			1 => $this->phpcsInstallDir,
		]);

		$expected = [
			'CodingStandardOne',
			'SecondStandard',
		];

		$result = TestCodingStandardHook::mirrorCodingStandardFolders($composer, $this->sampleDir);

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
		list($composer, $event) = $this->mockComposerAndEvent([
			1 => false,
		]);

		$expected = [
			'CodingStandardOne',
			'SecondStandard',
		];

		$result = TestCodingStandardHook::mirrorCodingStandardFolders($composer, $this->sampleDir);

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
		$expected = [
			'CodingStandardOne',
			'SecondStandard',
		];
		$result = TestCodingStandardHook::findRulesetFolders($this->sampleDir);

		$this->assertEquals(
			$expected,
			$result,
			'Only folders containing a ruleset.xml should be returned.'
		);
	}

	/**
	 * test findStandardsFolder()
	 *
	 * @return void
	 */
	public function testFindStandardsFolderExists() {
		list($composer, $event) = $this->mockComposerAndEvent([
			1 => $this->phpcsInstallDir,
		]);

		$result = TestCodingStandardHook::findStandardsFolder($composer);

		$this->assertEquals(
			$this->standardsInstallDir,
			$result,
			'Full path to the existing Standards/ folder should be returned.'
		);
	}

	/**
	 * test findStandardsFolder()
	 *
	 * @return void
	 */
	public function testFindStandardsFolderDoesNotExist() {
		list($composer, $package) = $this->mockComposerAndEvent([
			1 => 'does-not-exist',
		]);

		$result = TestCodingStandardHook::findStandardsFolder($composer);

		$this->assertFalse(
			$result,
			'False shouldbe returned for a non-existent path.'
		);
	}

	/**
	 * test configInstalledPathAdd()
	 *
	 * @return void
	 */
	public function testConfigInstalledPathAdd() {
		$this->marktestIncomplete('@TODO: Write tests for configInstalledPathAdd()');
	}

	/**
	 * test configInstalledPathRemove()
	 *
	 * @return void
	 */
	public function testConfigInstalledPathRemove() {
		$this->marktestIncomplete('@TODO: Write tests for configInstalledPathRemove()');
	}

	/**
	 * test readInstalledPaths()
	 *
	 * @return void
	 */
	public function testReadInstalledPaths() {
		$this->marktestIncomplete('@TODO: Write tests for readInstalledPaths()');
	}

	/**
	 * test saveInstalledPaths()
	 *
	 * @return void
	 */
	public function testSaveInstalledPaths() {
		$this->marktestIncomplete('@TODO: Write tests for saveInstalledPaths()');
	}

	/**
	 * test codeSnifferInit()
	 *
	 * @return void
	 */
	public function testcodeSnifferInit() {
		$this->marktestIncomplete('@TODO: Write tests for codeSnifferInit()');
	}
}
