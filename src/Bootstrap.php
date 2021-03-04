<?php declare(strict_types=1);

namespace App;

use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\SmartObject;
use Tracy\Debugger;

final class Bootstrap
{

	use SmartObject;

	/**
	 * @var string
	 */
	private $rootDir;

	/**
	 * @param string $rootDir
	 *
	 * @return \Nette\DI\Container
	 */
	public function createContainer(string $rootDir): Container
	{
		$this->rootDir = $rootDir;

		$mode = file_exists($rootDir . '/config.local/.dev')
			? Debugger::DEVELOPMENT
			: Debugger::PRODUCTION;
		Debugger::enable($mode, $rootDir . '/log');

		$loader = new ContainerLoader($rootDir . '/temp/cache', Debugger::DEVELOPMENT === $mode);
		$class = $loader->load([$this, 'loadConfig']);

		/** @var \Nette\DI\Container $container */
		$container = new $class();

		$debugMail = $container->getParameters()['debug_email'] ?? null;

		if ($debugMail) {
			Debugger::$email = $debugMail;
		}

		return $container;
	}

	public function loadConfig(Compiler $compiler): void
	{
		$compiler->addConfig([
			'parameters' => [
				'root_dir' => $this->rootDir,
			],
		]);

		$compiler->loadConfig($this->rootDir . '/config/config.neon');
		$localConfigFile = $this->rootDir . '/config.local/config.local.neon';

		if (file_exists($localConfigFile)) {
			$compiler->loadConfig($localConfigFile);
		}
	}

}
