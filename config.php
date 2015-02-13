<?php defined("PHRAPI") or die("Direct access not allowed!");

$config = [
	'gmt' => '-05:00',
	'locale' => 'es_MX',
	'php_locale' => 'es_US',
	'timezone' => 'America/Mexico_City',
	'offline' => false,
	'servers' => [
		'YOUR-DOMAIN' => [
			'url' => 'http://YOUR-DOMAIN/YOUR-INSTALL-PATH/',
			'db' => [
				'default' => [
					'host' => '',
					'name' => '',
					'user' => '',
					'pass' => ''
				],
			]
		],
	],
	'smtp' => [
		'host' => '',
		'sender_mail' => '',
		'sender_name' => '',
		'user' => '',
		'pass' => '',
	],
	'resources' => [
		'notas' => ['controller' => 'Notas'],

	],
	'credentials' => [
		'90d6aa44958fa4369c822f507bfc255924082a3d' => [
			'name' => 'Develop',
			'debug' => true
		],
	]
];