<?php

/**
 * Lumber channel
 *
 * This class represent one Lumber logging channel. One or more sources can be handled by it.
 * It also can have one or more writers.
 *
 * @copyright Copyright (c) 2010-2013 MV Labs (http://www.mvlabs.it)
 * @link      https://github.com/mvlabs/MvlabsLumber
 * @license   MIT - Please view the LICENSE file that was distributed with this source code
 * @author    Steve Maraspin <steve@mvlabs.it>
 * @package   MvlabsLumber
 */


namespace MvlabsLumber\Service;

use Monolog\Logger as Monolog;
use Psr\Log\LoggerInterface;


class Channel extends Monolog {

	/**
	 * Sources to be matched in order for action to be taken
	 *
	 * @var array
	 */
	private $as_filters = array();


	/**
	 * Constructor
	 *
	 * @param string Channel name
	 * @param array Monolog Handlers
	 * @param array Monolog Processors
	 * @param array Filters (IE sources this channel will take care of)
	 */
	public function __construct($s_name,
			                    array $am_handlers = array(),
			                    array $am_processors = array(),
			                    array $as_filters = array()) {
		parent::__construct($s_name, $am_handlers, $am_processors);
		$this->setFilters($as_filters);
	}


	/**
	 * Source to be taken into account
	 *
	 * @param string Source name
	 */
	public function addFilter($s_filter) {
		if (!is_scalar($s_filter)) {
			throw new \InvalidArgumentException( __FUNCTION__ .
					                            ' expects a source name to be provided. ' .
					                            gettype($s_filter).' provided instead');
		}
		$this->as_filters[$s_filter] = true;
	}


	/**
	 * Source won't be taken into consideration anymore
	 *
	 * @param string Source name
	 * @throws \OutOfBoundsException
	 */
	public function removeFilter($s_filter) {
		if (!array_key_exists($s_filter, $this->as_filters)) {
			throw new \OutOfBoundsException('Invalid filter: ' . $s_filter);
		}
		unset($this->as_filters[$s_filter]);
		return true;
	}


	/**
	 * Source will be removed if present
	 *
	 * @param string Source name
	 * @return boolean true if source had been found
	 */
	public function removeFilterIfPresent($s_filter) {
		if (!array_key_exists($s_filter, $this->as_filters)) {
			return false;
		}
		$this->removeFilter($s_filter);
		return true;
	}


	/**
	 * Replaces current set of sources
	 *
	 * @param array $as_filters
	 */
	public function setFilters(array $as_filters) {
		$this->as_filters = array();
		foreach ($as_filters as $s_filter) {
			$this->as_filters[$s_filter] = true;
		}
	}


	/**
	 * Returns currently registered sources
	 *
	 * @return array registered sources
	 */
	public function getFilters() {
		return array_keys($this->as_filters);
	}


	/**
	 * Adds a record to channel (IE a Monolog Logger)
	 *
	 * @see \Monolog\Logger::addRecord()
	 */
	public function addRecord($i_level, $s_message, array $am_context = array()) {

		// We add record only if it matches source filtering rules
		if (count($this->as_filters) == 0 ||
			(array_key_exists('lumber-source',$am_context) &&
			 array_key_exists($am_context['lumber-source'], $this->as_filters))) {

			// Taking into account preference on whether to log lumber source
			if (!array_key_exists('log_lumber_source', $am_context) || !$am_context['log_lumber_source']) {
				unset($am_context['lumber-source']);
			}

			unset($am_context['log_lumber_source']);
			return parent::addRecord($i_level, $s_message, $am_context);

		}
		return false;
	}

}