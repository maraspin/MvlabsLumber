<?php

/**
 * Lumber Default configuration
 *
 * @copyright Copyright (c) 2010-2013 MV Labs (http://www.mvlabs.it)
 * @link      https://github.com/mvlabs/MvlabsLumber
 * @license   MIT - Please view the LICENSE file that was distributed with this source code
 * @author    Steve Maraspin <steve@mvlabs.it>
 * @package   MvlabsLumber
 */

namespace Zend\Mvc;

return array(

	'lumber' => array(

		// Mappings between ZF2 events and Lumber source streams
		'sources' => array(

			'application_errors' => array('events' => array(MvcEvent::EVENT_DISPATCH_ERROR, 'app_issue'),
								          'severity' => 'warning',
										  'log_request_params' => true,
										  'log_exception_trace' => true,
										  'log_url_request' => true,
					                      'log_source' => true,
										  'log_target_object' => true
						            ),

			'custom_messages' => array('events' => array('other_event'),
				                       'target' => 'Application\Controller\ErrorController',
						              ),
		),

		// One or more destinations (aka writers) can be specified here
		'writers' => array(

			'primary' => array(
				'type' => 'file',
				'destination' => __DIR__ . '/../../../../data/application-all.log',
				'min_severity' => 'warning',
				'propagate' => true,
			),

			'logAll' => array(
				'type' => 'file',
				'destination' => __DIR__ . '/../../../../data/application-err.log',
				'min_severity' => 'alert',
				'propagate' => true,
			),

		),

		// Each channel uses one or more writers
		'channels' => array(

			'default' => array(
				'writers' => array(
					'primary',
					'logAll'
				),
			),

			'custom' => array(
				'writers' => array(
							'logAll'
						),
				// If the sources parameters is present (as an array), only these sources will be taken into account
				// Otherwise all messages/events will be handled by channel
				'sources' => array('custom_messages')
			),
		),

	),



	'service_manager' => array(
			'factories' => array(
					'MvlabsLumber\Service\Logger' => 'MvlabsLumber\Service\LoggerFactory',
			),
	),



);
