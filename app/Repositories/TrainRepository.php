<?php

namespace App\Repositories;

use App\Interfaces\TrainRepositoryInterface;
use App\Models\Train;
use Illuminate\Database\Eloquent\Collection;

class TrainRepository implements TrainRepositoryInterface
{
    /** @return Collection<int, Train> */
    public function getAllTrains(): Collection
    {
        return Train::all();
    }

    public function getTrainById(int $id): Train
    {
        return Train::findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function createTrain(array $data): Train
    {
        return Train::create($data);
    }

    /** @param array<string, mixed> $data */
    public function updateTrain(int $id, array $data): Train
    {
        $train = Train::findOrFail($id);
        $train->update($data);

        return $train->fresh();
    }

    public function deleteTrain(int $id): bool
    {
        $train = Train::findOrFail($id);

        return $train->delete();
    }
}
