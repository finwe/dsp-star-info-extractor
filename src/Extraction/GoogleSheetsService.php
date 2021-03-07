<?php declare(strict_types=1);

namespace App\Extraction;

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_ValueRange;
use Nette\SmartObject;
use Nette\Utils\Json;

final class GoogleSheetsService
{

	use SmartObject;

	public function __construct(
		private string $clientConfigPath,
		private string $tokenConfigPath,
		private string $spreadSheetId,
	) {}

	public function sendEntityToSheet(Google_Client $client, $entity)
	{
		$service = new Google_Service_Sheets($client);

		$sheetInfo = $service->spreadsheets->get($this->spreadSheetId);

		$exists = false;
		foreach ($sheetInfo->getSheets() as $sheet) {
			if ($sheet->getProperties()->getTitle() === $entity->getSystemName()) {
				$exists = true;
			}
		}

		if (!$exists) {
			$body = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
				'requests' => array(
					'addSheet' => array(
						'properties' => array(
							'title' => $entity->getSystemName(),
						)
					)
				)
			));

			$service->spreadsheets->batchUpdate($this->spreadSheetId, $body);
		}

		$rangeTopLeft = 'A';
		$rangeTopRight = 'B';

		$number = $entity->getNumber();
		if ($number) {
			switch ($number) {

				case 'II':
					$rangeTopLeft = 'I';
					$rangeTopRight = 'J';
					break;
				case 'III':
					$rangeTopLeft = 'M';
					$rangeTopRight = 'N';
					break;
				case 'IV':
					$rangeTopLeft = 'Q';
					$rangeTopRight = 'R';
					break;
				case 'V':
					$rangeTopLeft = 'U';
					$rangeTopRight = 'V';
					break;
				case 'VI':
					$rangeTopLeft = 'Y';
					$rangeTopRight = 'Z';
					break;
				case 'I':
				default:
					$rangeTopLeft = 'E';
					$rangeTopRight = 'F';
					break;
			}
		}

		$range = "'" . $entity->getSystemName() . "'" . '!' . $rangeTopLeft . ':' . $rangeTopRight;

		$body = new Google_Service_Sheets_ValueRange([
			'values' => $entity->toArray(),
		]);

		$params = [
			'valueInputOption' => 'RAW'
		];

		$service->spreadsheets_values->update($this->spreadSheetId, $range, $body, $params);
	}

	/**
	 * @param string|null $code
	 *
	 * @return \Google_Client|string
	 */
	public function getClient(?string $code)
	{
		$client = new Google_Client();
		$client->setApplicationName('Google Docs API PHP Quickstart');
		$client->setScopes(Google_Service_Sheets::SPREADSHEETS);
		$client->setAuthConfig($this->clientConfigPath);
		$client->setAccessType('offline');

		// Load previously authorized credentials from a file.
		if (file_exists($this->tokenConfigPath)) {

			$accessToken = Json::decode(file_get_contents($this->tokenConfigPath), Json::FORCE_ARRAY);

		} else {

			if (!$code) {
				// Request authorization from the user.
				$authUrl = $client->createAuthUrl();
				header(sprintf("Location: %s", $authUrl));
				die;
			}

			// Exchange authorization code for an access token.
			$accessToken = $client->fetchAccessTokenWithAuthCode($code);

			// Store the credentials to disk.
			file_put_contents($this->tokenConfigPath, Json::encode($accessToken));

			return $client;
		}

		$client->setAccessToken($accessToken);

		// Refresh the token if it's expired.
		if ($client->isAccessTokenExpired()) {
			try {
				$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
				file_put_contents($this->tokenConfigPath, Json::encode($client->getAccessToken()));
			} catch (\LogicException $e) {
				return sprintf('Unable to refresh Google access token: %s', $e->getMessage());
			}

		}

		return $client;
	}
}
