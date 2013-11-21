<?php

/**
 * Tests for Lumber main logger class
 *
 * @copyright Copyright (c) 2010-2013 MV Labs (http://www.mvlabs.it)
 * @link      https://github.com/mvlabs/MvlabsLumber
 * @license   MIT - Please view the LICENSE file that was distributed with this source code
 * @author    Steve Maraspin <steve@mvlabs.it>
 * @package   MvlabsLumber
 */

namespace MvlabsLumberTest\Service;

use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceManager;
use MvlabsLumber\Service\LoggerFactory;
use MvlabsLumber\Service\Logger;
use MvlabsLumber\Service\Channel;
use PHPUnit_Framework_TestCase;
use MvlabsLumber;


class ChannelTest extends \PHPUnit_Framework_TestCase {


    /**
     * The object to be tested.
     *
     * @var Channel
     */
    protected $I_channel;


    /**
     * Prepare the objects to be tested.
     */
    protected function setUp() {
    	$this->I_channel = new Channel('default');
    }


    /**
     * @covers MvlabsLumber\Service\Channel::__construct
     * @test
     */
    public function testCreationSuccessfull() {

    	$this->assertInstanceOf('MvlabsLumber\Service\Channel', $this->I_channel);
    	$this->assertEquals('default', $this->I_channel->getName());

    }


    /**
     * @covers MvlabsLumber\Service\Channel::setFilters
     * @covers MvlabsLumber\Service\Channel::getFilters
     * @covers MvlabsLumber\Service\Channel::addFilter
     * @covers MvlabsLumber\Service\Channel::removeFilter
     * @covers MvlabsLumber\Service\Channel::removeFilterIfPresent
     * @test
     */
    public function setAddRemoveFilters() {

    	$this->assertNull($this->I_channel->getFilters());
    	$this->I_channel->setFilters(array('test'));
    	$this->assertCount(1, $this->I_channel->getFilters());
    	$this->assertTrue(in_array('test',$this->I_channel->getFilters()));
    	$this->I_channel->addFilter('test2');
    	$this->assertCount(2, $this->I_channel->getFilters());
    	$this->assertTrue(in_array('test',$this->I_channel->getFilters()));
    	$this->assertTrue(in_array('test2',$this->I_channel->getFilters()));
    	$this->assertFalse($this->I_channel->removeFilterIfPresent('test3'));
    	$this->assertCount(2, $this->I_channel->getFilters());
    	$this->assertTrue(in_array('test',$this->I_channel->getFilters()));
    	$this->assertTrue(in_array('test2',$this->I_channel->getFilters()));
    	$this->assertTrue($this->I_channel->removeFilterIfPresent('test'));
    	$this->assertCount(1, $this->I_channel->getFilters());
    	$this->assertTrue(in_array('test2',$this->I_channel->getFilters()));
    	$this->assertTrue($this->I_channel->removeFilter('test2'));
    	$this->assertNull($this->I_channel->getFilters());

    }


    /**
     *
     * @covers MvlabsLumber\Service\Channel::removeFilter
     * @expectedException OutOfBoundsException
     * @expectedExceptionMessage Invalid filter: test
     * @test
     */
    public function removeNotExistingFilter() {

    	$this->assertNull($this->I_channel->getFilters());
    	$this->I_channel->removeFilter('test2');

    }



    /**
     *
     * @covers MvlabsLumber\Service\Channel::addFilter
     * @covers MvlabsLumber\Service\Channel::removeFilter
     * @test
     */
    public function clearFilters() {

    	$this->assertNull($this->I_channel->getFilters());
    	$this->I_channel->addFilter('test');
    	$this->assertCount(1, $this->I_channel->getFilters());
    	$this->assertTrue(in_array('test',$this->I_channel->getFilters()));

    	$this->I_channel->setFilters(null);
    	$this->assertNull($this->I_channel->getFilters());
    }



    /**
     *
     * @covers MvlabsLumber\Service\Channel::log
     * @covers MvlabsLumber\Service\Channel::addRecord
     * @test
     */
    public function logStuff() {

    	$this->assertTrue($this->I_channel->log('info', 'Something', array('lumber-source' => 'test2')));

    	$this->I_channel->addFilter('test');
    	$this->assertTrue($this->I_channel->log('info', 'Something', array('lumber-source' => 'test')));
    	$this->assertFalse($this->I_channel->log('info', 'Something', array('lumber-source' => 'test2')));

    }

}
