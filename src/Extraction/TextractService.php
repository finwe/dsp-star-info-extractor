<?php declare(strict_types=1);

namespace App\Extraction;

use Aws\Textract\TextractClient;
use Nette\Caching\Cache;
use Nette\SmartObject;

final class TextractService
{

	use SmartObject;

	public function __construct(
		private TextractClient $textract,
		private Cache $cache,
		private string $keyId,
		private string $keySecret,
		private string $region,
	) {}

	public function getExtractedTextFromFile($file)
	{
		$contents = fread($file, fstat($file)['size']);

		$options = [
			'Document' => [
				'Bytes' => $contents
			],
		];

		$cacheKey = md5($contents);

		$response = $this->cache->load($cacheKey);
		if (!$response) {
			$response = $this->textract->detectDocumentText($options);
			$this->cache->save($cacheKey, $response);
		}

		$blocks = array_filter($response->get('Blocks'), static function ($item) {
			return isset($item['BlockType']) && $item['BlockType'] === 'LINE';
		});

		$texts = array_reduce($blocks, static function ($all, $item) {
			$all[] = $item['Text'];

			return $all;
		}, []);

		file_put_contents(__DIR__ . '/../../log/' . $cacheKey . '.txt', implode("\n", $texts));
		rewind($file);
		file_put_contents(__DIR__ . '/../../log/' . $cacheKey . '.png', $file);

		fclose($file);

		return $response;
	}


}
