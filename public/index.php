<?php declare(strict_types=1);

namespace App;

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Bootstrap();
$container = $bootstrap->createContainer(__DIR__ . '/..');

$container->getByType(Application::class)
	->run();
