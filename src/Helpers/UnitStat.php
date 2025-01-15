<?php
namespace Scm\PluginBid\Helpers;

use JsonSerializable;

class UnitStat implements JsonSerializable
{
    public function __construct(
        protected string $date,
        protected int $number,
        protected float $sum_budget,
        protected array $covered_ids = []
    )
    {
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getSumBudget(): float
    {
        return $this->sum_budget;
    }

    public function getCoveredIds(): array
    {
        return $this->covered_ids;
    }


    public function jsonSerialize(): array
    {
        return [
          'date' => $this->date,
          'count' => $this->number,
          'sum_budget' => $this->sum_budget,
          'ids' => $this->covered_ids,
        ];
    }
}
