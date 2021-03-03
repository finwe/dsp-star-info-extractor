<?php declare(strict_types=1);

namespace App\Controller;

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
				$file = fopen(__DIR__ . '/../../data/Lambda Virginis I.png', 'rb');
			} else {
				$file = fopen($dataUrl, 'rb');
			}

			$response = $this->textractService->getExtractedTextFromFile($file);
			$entity = $this->entityService->entityFromTextractResult($response);
			$this->sheetsService->sendEntityToSheet($entity);

		} catch (\Aws\Exception\AwsException $e) {
			echo $e->getMessage();
		}

		return '';
	}

}
