<?php declare(strict_types=1);

namespace App\ExtractionResult;

use Nette\SmartObject;
use Nette\Utils\Strings;

final class Planet implements StellarEntity
{

	use SmartObject;

	/**
	 * @param \App\ExtractionResult\Resource[] $resources
	 */
	public function __construct(
		private $name,
		private $type,
		private $constructionArea,
		private $orbitType,
		private $orbitRadius,
		private $orbitalPeriod,
		private $rotationPeriod,
		private $orbitInclination,
		private $axialInclination,
		private $resources
	) {}

	public function getName(): string
	{
		return $this->name;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getConstructionArea(): int
	{
		return $this->constructionArea;
	}

	public function getOrbitType(): string
	{
		return $this->orbitType;
	}

	public function getOrbitRadius(): float
	{
		return $this->orbitRadius;
	}

	public function getOrbitalPeriod(): float
	{
		return $this->orbitalPeriod;
	}

	public function getRotationPeriod(): ?float
	{
		return $this->rotationPeriod;
	}

	public function getOrbitInclination(): float
	{
		return $this->orbitInclination;
	}

	public function getAxialInclination(): float
	{
		return $this->axialInclination;
	}

	public function toArray()
	{
		$result = [
			[$this->name, ''],
			[$this->type, ''],
			['', ''],
		];

		foreach ($this->resources as $resource) {
			$result[] = [$resource->getType(), $resource->getAmount()];
		}

		$result[] = ['', ''];

		if ($this->constructionArea) {
			$result[] = ['Construction area', (string) $this->constructionArea];
		}

		$result[] = ['Orbit type', (string) $this->orbitType];
		$result[] = ['Orbit radius', (string) $this->orbitRadius];
		$result[] = ['Orbital period', (string) $this->orbitalPeriod];
		$result[] = ['Rotation period', (string) $this->rotationPeriod];
		$result[] = ['Orbit inclination', (string) $this->orbitInclination];
		$result[] = ['Planet\'s axial inclination', (string) $this->axialInclination];

		return $result;
	}

	public function getSystemName()
	{
		return Strings::substring($this->name, 0, Strings::indexOf($this->name, ' ', -1));
	}

	public function getNumber()
	{
		return Strings::substring($this->name, Strings::indexOf($this->name, ' ', -1) + 1);
	}

}
