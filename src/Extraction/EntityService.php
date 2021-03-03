<?php declare(strict_types=1);

namespace App\Extraction;

use App\ExtractionResult\Planet;
use App\ExtractionResult\Resource;
use App\ExtractionResult\Star;
use App\ExtractionResult\StellarEntity;
use Aws\Result;
use Nette\SmartObject;
use Nette\Utils\Strings;

final class EntityService
{

	use SmartObject;

	public function entityFromTextractResult(Result $response): StellarEntity
	{
		$blocks = $response->get('Blocks');

		$name = null;
		$type = null;

		$blocks = array_filter($blocks, static function ($item) {
			return isset($item['BlockType']) && $item['BlockType'] === 'LINE';
		});

		$resourceTypes = [
			'Iron ore',
			'Copper ore',
			'Silicon ore',
			'Titanium ore',
			'Stone',
			'Coal',
			'Ocean type',
			'Crude oil',
			'Solar energy',
			'Wind energy',
			'Fire ice',
			'Hydrogen',
			'Deuterium',
			'Collectable Hydrogen',
			'Collectable Deuterium',
			'Optical grating',
			'Spiniform stalagmite',
			'Water',
			'Sulfuric acid',
			'Kimberlite ore',
			'Fractal silicon',
		];

		$parameters = [
			'Construction area' => 'constructionArea',
			'Orbit radius' => 'orbitRadius',
			'Orbital period' => 'orbitalPeriod',
			'Rotation period' => 'rotationPeriod',
			'Orbit inclination' => 'orbitInclination',
			'Planet\'s axial inclination' => 'axialInclination',
			'Mass' => 'mass',
			'Spectral class' => 'spectralClass',
			'Radius' => 'radius',
			'Luminosity' => 'luminosity',
			'Temperature' => 'temperature',
			'Age' => 'age',
		];

		$constructionArea = null;
		$orbitRadius = null;
		$orbitalPeriod = null;
		$rotationPeriod = null;
		$orbitInclination = null;
		$axialInclination = null;
		$orbitType = null;
		$mass = null;
		$spectralClass = null;
		$radius = null;
		$luminosity = null;
		$temperature = null;
		$age = null;
		$resources = [];

		while (count($blocks)) {

			$item = array_shift($blocks);

			$text = $item['Text'];

			if ($text === null || Strings::length($text) <= 1 ) {
				continue;
			}

			if (!$name) {
				$name = $text;
				continue;
			}

			if (!$type) {
				$type = $text;
				continue;
			}

			foreach ($resourceTypes as $resourceType) {
				if (Strings::startsWith($text, $resourceType)) {
					$item = array_shift($blocks);
					$text = $item['Text'];
					$resource = new Resource($resourceType, $text);
					$resources[] = $resource;
				}
			}

			foreach ($parameters as $parameterName => $parameterVariable) {

				if (Strings::startsWith($text, $parameterName)) {

					$parameterName = $text;

					if (Strings::startsWith($parameterName, 'Orbit radius')) {

						if (Strings::contains($text, 'Orbiting')) {
							$orbitType = Strings::substring($text, Strings::indexOf($text, 'Orbiting'));
							$text = Strings::substring($text, 0, Strings::indexOf($text, 'Orbiting') - 1);
						} elseif (Strings::startsWith($text, 'Orbit')) {
							$orbitType = $text;
							$item = array_shift($blocks);
							$text = $item['Text'];
						}

					} else {
						$item = array_shift($blocks);
						$text = $item['Text'];
					}

					$$parameterVariable = $text;
				}
			}
		}

		if (Strings::contains($type, 'star') || Strings::contains($type, 'dwarf') || Strings::contains($type, 'hole')) {
			$entity = new Star(
				$name,
				$type,
				$mass,
				$spectralClass,
				$radius,
				$luminosity,
				$temperature,
				$age,
				$resources
			);
		} else {
			$entity = new Planet(
				$name,
				$type,
				$constructionArea,
				$orbitType,
				$orbitRadius,
				$orbitalPeriod,
				$rotationPeriod,
				$orbitInclination,
				$axialInclination,
				$resources
			);
		}

		return $entity;
	}

}
