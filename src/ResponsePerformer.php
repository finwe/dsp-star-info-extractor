<?php declare(strict_types=1);

namespace App;

use Nette\SmartObject;
use Nette\Utils\Json;

/**
 * @SuppressWarnings(CS.OutputFunctions)
 */
final class ResponsePerformer
{

	use SmartObject;

	/**
	 * @param mixed $content
	 */
	public function perform($content): void
	{
		if (is_string($content)) {
			header('Content-type: text/plain');
			echo $content;
		} elseif (null !== $content) {
			header('Content-type: application/json');
			echo Json::encode($content);
		} else {
			header('HTTP/2 204 No Content');
		}

		die;
	}

	/**
	 * @param int $code
	 * @param mixed $content
	 */
	public function performSuccess(int $code, $content): void
	{
		$codes = [
			201 => 'Created',
		];

		$header = sprintf('HTTP/2 %d %s', $code, $codes[$code]);
		header($header);

		$this->perform($content);
	}

	/**
	 * @param int $code
	 * @param string $description
	 */
	public function performError(int $code, string $description): void
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

		$this->perform([
			'error' => $header,
			'description' => $description,
		]);
	}

}
