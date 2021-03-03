<?php declare(strict_types=1);

namespace App;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Nette\DI\Container;
use Nette\Http\IRequest;
use App\Controller\DocumentController;
use function FastRoute\cachedDispatcher;

final class Application
{

	/**
	 * @var \Nette\DI\Container
	 */
	private $container;

	/**
	 * @var \Nette\Http\IRequest
	 */
	private $request;

	/**
	 * @var \App\ResponsePerformer
	 */
	private $performer;

	public function __construct(Container $container, IRequest $request, ResponsePerformer $performer)
	{
		$this->container = $container;
		$this->request = $request;
		$this->performer = $performer;
	}

	public function run(): void
	{
		$dispatcher = cachedDispatcher(static function (RouteCollector $r) {
			$r->get('/document', [DocumentController::class, 'create']);
			$r->post('/document', [DocumentController::class, 'create']);
		}, [
			'cacheFile' => __DIR__ . '/../temp/cache/route.cache',
			'cacheDisabled' => file_exists(__DIR__ . '/../.dev'),
		]);

		$routeInfo = $dispatcher->dispatch($this->request->getMethod(), $this->request->getUrl()->getPath());

		switch ($routeInfo[0]) {
			case Dispatcher::NOT_FOUND:
				$this->performer->performError(404, 'Not Found');

				break;
			case Dispatcher::METHOD_NOT_ALLOWED:
				$allowedMethods = $routeInfo[1];
				$this->performer->performError(405, sprintf(
					'HTTP method "%s" for "%s" not allowed, must be one of %s',
					$this->request->getMethod(),
					$this->request->getUrl()->getPath(),
					implode(', ', $allowedMethods)
				));

				break;
			case Dispatcher::FOUND:
				$handler = $routeInfo[1];
				$this->perform($handler, $routeInfo);

				break;
		}
	}

	/**
	 * @param callable $handler
	 * @param mixed[] $routeInfo
	 */
	private function perform ($handler, array $routeInfo): void
	{
		$service = $this->container->getByType($handler[0], false);

		if (!$service) {
			$this->performer->performError(404, 'Not Found');
		}

		try {
			$this->performer->perform(
				$service->{$handler[1]}(...array_values($routeInfo[2]))
			);
		} catch (\App\Exception\ConflictException $e) {
			$this->performer->performError(409, $e->getMessage());
		} catch (\App\Exception\CreatedException $e) {
			$this->performer->performSuccess(201, $e->getMessage());
		} catch (\App\Exception\InvalidArgumentException $e) {
			$this->performer->performError(400, $e->getMessage());
		} catch (\App\Exception\NotFoundException $e) {
			$this->performer->performError(404, $e->getMessage());
		}
	}

}
