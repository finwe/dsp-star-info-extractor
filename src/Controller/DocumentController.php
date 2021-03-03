<?php declare(strict_types=1);

namespace App\Controller;

use App\Exception\InvalidArgumentException;
use App\Extraction\EntityService;
use App\Extraction\GoogleSheetsService;
use App\Extraction\TextractService;
use Nette\Http\Request;
use Nette\SmartObject;

final class DocumentController
{

	use SmartObject;

	public function __construct(
		private Request $request,
		private TextractService $textractService,
		private EntityService $entityService,
		private GoogleSheetsService $sheetsService
	) {}

	public function create()
	{
		try {
			$dataUrl = $this->request->getPost('dataurl');

			if (!$dataUrl) {
				throw new InvalidArgumentException('Missing parameter dataurl');
			}

			$file = fopen($dataUrl, 'rb');

			$response = $this->textractService->getExtractedTextFromFile($file);
			$entity = $this->entityService->entityFromTextractResult($response);

			$client = $this->sheetsService->getClient(null);

			if (is_string($client)) {
				throw new InvalidArgumentException('Use /access endpoint to configure Google API Client');
			}

			$this->sheetsService->sendEntityToSheet($client, $entity);

		} catch (\Aws\Exception\AwsException $e) {
			throw new InvalidArgumentException($e->getMessage());
		}

		return '';
	}

	public function access()
	{
		$result = $this->sheetsService->getClient($this->request->getQuery('code'));

		if (is_string($result)) {
			return $result;
		}

		return '';
	}

}
