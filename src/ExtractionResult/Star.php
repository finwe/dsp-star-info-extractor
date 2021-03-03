<?php declare(strict_types=1);

namespace App\ExtractionResult;

use Nette\SmartObject;

final class Star implements StellarEntity
{

	use SmartObject;

	/**
	 * @param \App\ExtractionResult\Resource[] $resources
	 */
	public function __construct(
		private $name,
		private $type,
		private $mass,
		private $spectralClass,
		private $radius,
		private $luminosity,
		private $temperature,
		private $age,
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

	public function getMass(): float
	{
		return $this->mass;
	}

	public function getSpectralClass(): string
	{
		return $this->spectralClass;
	}

	public function getRadius(): float
	{
		return $this->radius;
	}

	public function getLuminosity(): float
	{
		return $this->luminosity;
	}

	public function getTemperature(): float
	{
		return $this->temperature;
	}

	public function getAge(): float
	{
		return $this->age;
	}

	public function getResources(): array
	{
		return $this->resources;
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

		$result[] = ['Mass', $this->mass];
		$result[] = ['Spectral class', $this->spectralClass];
		$result[] = ['Radius', $this->radius];
		$result[] = ['Luminosity', $this->luminosity];
		$result[] = ['Temperature', $this->temperature];
		$result[] = ['Age', $this->age];

		return $result;
	}

	public function getSystemName()
	{
		return $this->name;
	}

	public function getNumber()
	{
		return null;
	}

}
