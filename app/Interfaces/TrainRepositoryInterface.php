<?php

namespace App\Interfaces;

use App\Models\Train;
use Illuminate\Database\Eloquent\Collection;

interface TrainRepositoryInterface
{
    /** @return Collection<int, Train> */
    public function getAllTrains(): Collection;

    public function getTrainById(int $id): Train;

    /** @param array<string, mixed> $data */
    public function createTrain(array $data): Train;

    /** @param array<string, mixed> $data */
    public function updateTrain(int $id, array $data): Train;

    public function deleteTrain(int $id): bool;
}
