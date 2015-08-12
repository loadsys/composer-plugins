<?php
namespace Loadsys\Composer\Test\TestCase;

use Loadsys\Composer\LoadsysComposerPlugin;
use Composer\IO\IOInterface;
use Composer\Repository\RepositoryManager;
use Composer\Repository\InstalledArrayRepository;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Package\Link;
use Composer\Package\Version\VersionParser;
use Composer\Composer;
use Composer\Config;

class LoadsysComposerPluginTest extends \PHPUnit_Framework_TestCase {
    private $package;
    private $io;
    private $composer;
    private $plugin;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();

        $this->package = new Package('CamelCased', '1.0', '1.0');
        $this->io = $this->getMock('Composer\IO\IOInterface');
        $this->composer = new Composer();
        $this->plugin = new LoadsysComposerPlugin();
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
        unset($this->plugin);

        parent::tearDown();
    }

    /**
     * All we can do is confirm that the plugin tried to register the
     * correct installer class during ::activate().
     *
     * @return void
     */
    public function testActivate() {
        $this->composer = $this->getMock('Composer\Composer', [
        	'getInstallationManager',
        	'addInstaller'
        ]);
        $this->composer->setConfig(new Config(false));

        $this->composer->expects($this->any())
            ->method('getInstallationManager')
            ->will($this->returnSelf());

        $this->composer->expects($this->at(1))
            ->method('addInstaller')
            ->with($this->isInstanceOf('Loadsys\Composer\Puphpet\ReleaseInstaller'));
        $this->composer->expects($this->at(3))
            ->method('addInstaller')
            ->with($this->isInstanceOf('Loadsys\Composer\PhpCodesniffer\CodingStandardInstaller'));

        $this->plugin->activate($this->composer, $this->io);
    }
}
