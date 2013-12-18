<?php

return array(
	'services' => array(
		'behave' => array(
            'class'   => 'Behave\Client',
			
			'params' => array(
				// TODO: put a /v1 after the base_url
				'base_url' => \Behave\Behave::API_ROOT_URL . '/',
				'description' => 'Behave API',
			),			
		)
	)
);
