<?php declare(strict_types=1);

namespace App\ExtractionResult;

interface StellarEntity
{

	public function getName();
	public function getNumber();
	public function getSystemName();
	public function toArray();

}
