<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Coalize',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'senha',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','192.168.240.1'),
		),
		*/
	),

	// application components
	'components'=>array(

		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),

		//Url manager		
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'cliente/<id:\d+>/<title:.*?>'=>'cliente/view',
				'clientes/<tag:.*?>'=>'cliente/index',
				'produto/<id:\d+>/<title:.*?>'=>'produto/view',
				'produtos/<tag:.*?>'=>'produto/index',
				// REST patterns
				array('api/list', 'pattern'=>'api/<model:\w+>', 'verb'=>'GET'),
				array('api/list', 'pattern'=>'api/<model:\w+>/cliente/<cliente:\d+>', 'verb'=>'GET'),
				array('api/list', 'pattern'=>'api/<model:\w+>/offset/<offset:\d+>/limit/<limit:\d+>', 'verb'=>'GET'),
				array('api/view', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'GET'),
				array('api/update', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'PUT'),
				array('api/delete', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'DELETE'),
				array('api/create', 'pattern'=>'api/<model:\w+>', 'verb'=>'POST'),
				// Other controllers
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		
		// database settings are configured in database.php
		'db'=>require(dirname(__FILE__).'/database.php'),

		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>YII_DEBUG ? null : 'site/error',
		),

		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),

	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
	),
	
);
