<?php declare(strict_types=1);

use Nette\Utils\Json;

function dd(...$params)
{
	dump(...$params);
	die;
}

function dieWithHeader($code, $description = '')
{
	$codes = [
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		409 => 'Conflict',
		501 => 'Not Implemented',
	];

	$header = sprintf('HTTP/2 %d %s', $code, $codes[$code]);
	header($header);

	response([
		'error' => $header,
		'description' => $description,
	]);

	die;
}

function response($response)
{
	header('Content-type: application/json');
	echo Json::encode($response);
	exit;
}

function validateConfig(array $config)
{
	if (empty($config['api_key'])) {
		dieWithHeader(501, 'Insufficient configuration (api_key)');
	}

	if (empty($config['data_root']) || !file_exists($config['data_root'])) {
		dieWithHeader(501, 'Insufficient configuration (data_root)');
	}

	if (empty($config['template_project']) || !file_exists($config['template_project'])) {
		dieWithHeader(501, 'Insufficient configuration (template_project)');
	}

	if (empty($config['template_project_fee']) || !file_exists($config['template_project_fee'])) {
		dieWithHeader(501, 'Insufficient configuration (template_project_fee)');
	}
}


