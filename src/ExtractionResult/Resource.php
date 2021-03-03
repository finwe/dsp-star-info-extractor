<?php declare(strict_types=1);

namespace App\ExtractionResult;

use Nette\SmartObject;

final class Resource
{

	use SmartObject;

	public function __construct(
		private string $type,
		private $amount,
	) {}

	public function getType(): string
	{
		return $this->type;
	}

	public function getAmount()
	{
		return $this->amount;
	}

}
