<?php

namespace App\Repositories;

use App\Interfaces\TrainRepositoryInterface;
use App\Models\Train;

class TrainRepository implements TrainRepositoryInterface
{
    public function getAllTrains()
    {
        return Train::all();
    }

    public function getTrainById($id)
    {
        return Train::findOrFail($id);
    }

    public function createTrain(array $data)
    {
        return Train::create($data);
    }

    public function updateTrain($id, array $data)
    {
        $train = Train::findOrFail($id);
        $train->update($data);
        return $train;
    }

    public function deleteTrain($id)
    {
        $train = Train::findOrFail($id);
        return $train->delete();
    }
}
