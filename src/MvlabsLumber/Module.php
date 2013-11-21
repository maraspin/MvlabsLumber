<?php

/**
 * Lumber module entry point within ZF2
 *
 * @copyright Copyright (c) 2010-2013 MV Labs (http://www.mvlabs.it)
 * @link      https://github.com/mvlabs/MvlabsLumber
 * @license   MIT - Please view the LICENSE file that was distributed with this source code
 * @author    Steve Maraspin <steve@mvlabs.it>
 * @package   MvlabsLumber
 */


namespace MvlabsLumber;

use Zend\EventManager\Event;
use Zend\Mvc\ApplicationInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;


/**
 * MvlabsLumber Module
 */
class Module {


	/**
	 * {@inheritDoc}
	 */
	public function onBootstrap(MvcEvent $I_e)	{

		// Application configuration
		$I_application = $I_e->getApplication();
		$this->handleEvents($I_application);

	}


	 /**
     * {@inheritDoc}
     */
	public function getConfig() {
		return  include __DIR__ . '/../../config/module.config.php';
	}


	/**
	 * {@inheritDoc}
	 */
	public function getAutoloaderConfig() {
		return array(
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
						),
				),
		);
	}


	/**
	 * Takes care of registering events to Lumber
	 *
	 * @throws \InvalidArgumentException
	 */
	private function handleEvents(\Zend\Mvc\ApplicationInterface $I_application) {

		// Event Manager is loaded
		$I_eventManager = $I_application->getEventManager();
		$I_sharedManager = $I_application->getEventManager()->getSharedManager();

		$I_moduleRouteListener = new ModuleRouteListener();
		$I_moduleRouteListener->attach($I_eventManager);

		// Lumber is loaded
		$I_sm = $I_application->getServiceManager();
		$I_lumber = $I_sm->get('MvlabsLumber\Service\Logger');

		// Lumber configuration is loaded
		$am_config = $I_application->getConfig();

		// Do we need to do something at all?
		if(!array_key_exists('lumber', $am_config) ||
		   !array_key_exists('sources', $am_config['lumber'])) {
			return;
		}

		$am_sources = $am_config['lumber']['sources'];

		foreach ($am_sources as $s_sourceName => $am_sourceInfo) {

			$am_eventConf = $this->getEventInfo($am_sourceInfo);
			// list($s_target, $am_registeredEvents, $s_severity, $b_verbose) = $am_eventConf;

			// Check for problems upon app initialization, rather than when event is triggered
			if (!$I_lumber->isValidSeverityLevel($am_eventConf['severity'])) {
				throw new \InvalidArgumentException('Severity ' . $am_eventConf['severity'] . ' is invalid');
			}

			$I_request = $I_sm->get('Request');

			foreach ($am_eventConf['events'] as $s_event) {

				\Zend\EventManager\StaticEventManager::getInstance()->attach($am_eventConf['target'], $s_event,
				function($I_event) use ($I_lumber, $I_request, $s_sourceName, $am_sourceInfo, $am_eventConf) {

				    // list($s_target, $am_events, $s_severity, $b_verbose) = $am_eventConf;

					$s_message = '';

					if ($I_event instanceof \Zend\EventManager\Event) {

						$s_target = $I_event->getTarget();
						$s_name = $I_event->getName();
						$am_params = $I_event->getParams();

						$am_additionalInfo = array('lumber-source' => $s_sourceName);
						$as_messages = array();

						if ($am_eventConf['log_url_request']) {
							$s_requestUri = $I_request->getUriString();
							$am_additionalInfo['request'] = $s_requestUri;
						}

						if ($am_eventConf['log_request_params']) {
							$s_queryParams = json_encode($I_request->getQuery());
							$am_additionalInfo['query_params'] = $s_queryParams;

							$s_postParams = json_encode($I_request->getPost());
							$am_additionalInfo['post_params'] = $s_postParams;
						}

						if (array_key_exists('message', $am_params)) {
							$as_messages[] = $am_params['message'];
						}

						// Exceptions need to be made human readable
						if (array_key_exists('exception', $am_params) &&
						    $am_params['exception'] instanceof \Exception) {

							$I_exception = $am_params['exception'];
							do {

								$as_messages[] = $I_exception->getMessage();

								// Shall we also include exception traces?
								if($am_eventConf['log_exception_trace']) {
									$am_traces = $I_exception->getTrace();
									foreach ($am_traces as $am_trace) {

										$s_tempMessage = 'Error';
										$as_toCheck = array('file', 'line', 'class', 'method');
										foreach ($as_toCheck as $s_check) {
											$s_tempMessage .= (array_key_exists($s_check, $am_trace)?' '.$s_check.': '.$am_trace[$s_check]:'');
										}

										$as_messages[] = $s_tempMessage;
									}
								}

							}
							while($I_exception = $I_exception->getPrevious());

						}
					}	// Is Event an instance of \Zend\EventManager\Event?

					$am_additionalInfo['event'] = $s_name;

					if ($am_eventConf['log_target_object']) {
						$am_additionalInfo['target'] = $s_target;
					}

					$am_additionalInfo['log_lumber_source'] = $am_eventConf['log_lumber_source'];

					foreach ($as_messages as $s_message) {
						$I_lumber->log($am_eventConf['severity'], $s_message, $am_additionalInfo);
					}

				});

			}

		}	// Foreach
	}


	/**
	 * Support function extracting all event configuration information
	 *
	 * @param array event configuration record $am_eventInfo
	 * @return array configured event info record
	 */
	private function getEventInfo(array $am_eventInfo) {

		$am_defaultParams = array('target' => '*',
				                  'events' => '*',
				                  'severity' => 'notice',
				                  'log_lumber_source' => false,
				                  'log_request_params' => false,
				                  'log_url_request' => false,
				                  'log_exception_trace' => false,
		                          'log_target_object' => false,
				                  'log_lumber_source' => false
		);

		foreach ($am_defaultParams as $s_paramName => $m_defaultValue) {
			if (!array_key_exists($s_paramName, $am_eventInfo)) {
				$am_eventInfo[$s_paramName] = $m_defaultValue;
			}
			$s_severity = $am_eventInfo[$s_paramName];
		}

		if ('*' == $am_eventInfo['events']) {
			$am_eventInfo['events'] = array('*');
		}

		return $am_eventInfo;

	}


}
