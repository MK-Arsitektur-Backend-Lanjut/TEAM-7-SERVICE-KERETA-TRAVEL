<?php

namespace App\Interfaces;

interface TrainRepositoryInterface
{
    public function getAllTrains();
    public function getTrainById($id);
    public function createTrain(array $data);
    public function updateTrain($id, array $data);
    public function deleteTrain($id);
}
