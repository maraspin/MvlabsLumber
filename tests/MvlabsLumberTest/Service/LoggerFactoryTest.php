<?php

/**
 * Tests for Lumber main logger factory class
 *
 * @copyright Copyright (c) 2010-2013 MV Labs (http://www.mvlabs.it)
 * @link      https://github.com/mvlabs/MvlabsLumber
 * @license   MIT - Please view the LICENSE file that was distributed with this source code
 * @author    Steve Maraspin <steve@mvlabs.it>
 * @package   MvlabsLumber
 */

namespace MvlabsLumberTest;

use PHPUnit_Framework_TestCase;
use MvlabsLumber\Service\LoggerFactory;
use MvlabsLumberTest\MockConfigs\MockConfigs;


class LoggerFactoryTest extends \PHPUnit_Framework_TestCase {


    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $I_mockSM;


    /**
	 * Object containing mock configurations, used to test factory
	 *
	 * @var \MvlabsLumberTest\Service\MockConfigs\MockConfigs
	 */
    protected $I_mockConfig;


    /**
     * Prepare the objects to be tested.
     */
    protected function setUp() {

    	$this->I_mockSM =  \Mockery::mock('Zend\ServiceManager\ServiceManager');
    	$this->I_mockConfig = new MockConfigs();

    }


    /**
     * Empty configuration is passed, an exception is thrown
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage There seems to be no configuration for Lumber. Cannot continue execution
     */
    public function testMissingConf() {

    	$this->I_factory = new LoggerFactory();
    	$this->I_factory->createService($this->getMockSM());

    }


    /**
     * Invalid configuration is passed, an exception is thrown
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Channel configuration for Lumber ("lumber" key) seems to be empty or invalid
     */
    public function testInvalidConf() {

    	$this->I_factory = new LoggerFactory();
    	$this->I_factory->createService($this->getMockSM());

    }


    /**
     * Logging to all valid writers
     *
     */
    public function testWorkingWriters() {

    	$this->I_factory = new LoggerFactory();
    	$I_logger = $this->I_factory->createService($this->getMockSM());

    	// Have we created an instance of our Logger?
    	$this->assertInstanceOf('MvlabsLumber\Service\Logger', $I_logger);

    	// Have we created the default channel?
    	$I_channel = $I_logger->getChannel('default');
    	$this->assertInstanceOf('Monolog\Logger', $I_channel);

    	$as_expectedWriters = array(
    			'Monolog\Handler\ZendMonitor',
    			'Monolog\Handler\RotatingFileHandler',
    			'Monolog\Handler\SyslogHandler',
    			'Monolog\Handler\ErrorLogHandler',
    			'Monolog\Handler\CouchDBHandler',
    			'Monolog\Handler\ChromePHPHandler',
    			'Monolog\Handler\FirePHPHandler',
    			'Monolog\Handler\StreamHandler',
    			'Monolog\Handler\StreamHandler',
    	);

    	foreach ($as_expectedWriters as $s_expectedWriter) {

    		if ('Monolog\Handler\ZendMonitor' == $s_expectedWriter &&
    		    !function_exists('zend_monitor_custom_event')) {
    			break;
    		}

    		// Does it have a writer?
    		$I_handler = $I_channel->popHandler();
    		$this->assertInstanceOf($s_expectedWriter, $I_handler);

    		// Is it the writer configured to bubble?
    		$this->assertTrue($I_handler->getBubble());


    	}

    }


    /**
     * Logger is configured to write to Zend Monitor (not installed)
     *
     * @expectedException \Monolog\Handler\MissingExtensionException
     * @expectedExceptionMessage You must have Zend Server installed in order to use this handler
     */
    public function testZendMonitorWriterWithoutExtension() {

    	if (function_exists('zend_monitor_custom_event')) {
    		$this->markTestSkipped('ZendServer is installed');
    	}

    	$this->I_factory = new LoggerFactory();
    	$I_logger = $this->I_factory->createService($this->getMockSM());

    	// Have we created an instance of our Logger?
    	$this->assertInstanceOf('MvlabsLumber\Service\Logger', $I_logger);

    	// Have we created the default channel?
    	$I_channel = $I_logger->getChannel('default');
    	$this->assertInstanceOf('Monolog\Logger', $I_channel);

    	// Does it have a writer?
    	$I_handler = $I_channel->popHandler();
    	$this->assertInstanceOf('Monolog\Handler\ZendMonitor', $I_handler);

    }


    /**
     * Logger is configured to write to an unwritable directory
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Can not continue writing to
     */
    public function testWrongFileLocationFileWriter() {

    	$this->I_factory = new LoggerFactory();
    	$this->I_factory->createService($this->getMockSM());

    }


    /**
     * Logger is configured to write/rotate to an unwritable file
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Can not continue writing to
     */
    public function testWrongFileLocationRotatingFileWriter() {

    	$this->I_factory = new LoggerFactory();
    	$this->I_factory->createService($this->getMockSM());

    }


    /**
     * Writer is configured with a not existing level
     *
     * @expectedException \OutOfRangeException
     * @expectedExceptionMessage Invalid logging level fun for writer
     */
    public function testWrongLogLevel() {

    	$this->I_factory = new LoggerFactory();
    	$this->I_factory->createService($this->getMockSM());

    }


    /**
     * Writers param in conf contains invalid data
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Writers configuration argument is not an array as expected
     */
    public function testInvalidWriters() {

    	$this->I_factory = new LoggerFactory();
    	$this->I_factory->createService($this->getMockSM());

    }


    /**
     * Invalid writer type specified
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid type (someWritersWhichDoesntExistAndNeverWill) for writer default in Lumber configuration
     */
    public function testInvalidWriterType() {

    	$this->I_factory = new LoggerFactory();
    	$this->I_factory->createService($this->getMockSM());

    }


    /**
     * Configured writer has not been defined
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Requested writer writerdoesnotexist not found in Lumber configuration
     */
    public function testWriterNotExisting() {

    	$this->I_factory = new LoggerFactory();
    	$this->I_factory->createService($this->getMockSM());

    }


    /**
     * No severity has been set for a writer
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Writer default needs parameter min_severity to be set
     */
    public function testNoSeveritySet() {

    	$this->I_factory = new LoggerFactory();
    	$this->I_factory->createService($this->getMockSM());

    }


    /**
     * Verbosity is set to false
     */
    public function testVerbosityFalse() {

    	$this->I_factory = new LoggerFactory();
    	$this->I_factory->createService($this->getMockSM());

    }


    /**
     * Invalid sources have been specified for a certain channel
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage sources parameter in channels configuration is not an array
     */
    public function testInvalidSources() {

    	$this->I_factory = new LoggerFactory();
    	$this->I_factory->createService($this->getMockSM());

    }


    /**
     * Multiple channels are created
     */
    public function testMultipleChannels() {

    	$this->I_factory = new LoggerFactory();
    	$I_logger = $this->I_factory->createService($this->getMockSM());

    	$aI_channels = $I_logger->getChannels();

		$I_channel1 = $aI_channels['default'];

		// Propagate is true
		$I_writerOne = $I_channel1->popHandler();
		$this->assertFalse($I_writerOne->getBubble());

		// Propagate is false
		$I_writerTwo = $I_channel1->popHandler();
		$this->assertTrue($I_writerTwo->getBubble());

		$I_channel2 = $aI_channels['secondary'];

		// Default (propagate is true)
		$I_writerOne = $I_channel2->popHandler();
		$this->assertFalse($I_writerOne->getBubble());

    }


    /**
     * Multiple writers with propagation is set to on (first) and off (next two)
     */
    public function testMultipleWriters() {

    	$this->I_factory = new LoggerFactory();
    	$I_logger = $this->I_factory->createService($this->getMockSM());

    	$aI_channels = $I_logger->getChannels();

		$I_channel = $aI_channels['default'];

		// Propagate is true
		$I_writerOne = $I_channel->popHandler();
		$this->assertFalse($I_writerOne->getBubble());

		// Propagate is false
		$I_writerTwo = $I_channel->popHandler();
		$this->assertTrue($I_writerTwo->getBubble());

		// Default (propagate is true)
		$I_writerThree = $I_channel->popHandler();
		$this->assertFalse($I_writerThree->getBubble());

		// Propagate is false
		$I_writerFour = $I_channel->popHandler();
		$this->assertTrue($I_writerFour->getBubble());

    }


    /**
     * Constructs a mock service manager with Lumber configuration for a specific test
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    private function getMockSM() {

    	$am_trace = debug_backtrace();
    	$s_confToLoad = lcfirst(substr($am_trace[1]['function'], 4));

    	$am_serviceConf = $this->I_mockConfig->getConf($s_confToLoad);

    	$this->I_mockSM->shouldReceive('get')->with('Config')->once()->andReturn($am_serviceConf);

    	return $this->I_mockSM;

    }

}
