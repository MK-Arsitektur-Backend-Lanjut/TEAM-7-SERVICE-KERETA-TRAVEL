<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrainController extends Controller
{
    protected $trainRepository;

    public function __construct(\App\Interfaces\TrainRepositoryInterface $trainRepository)
    {
        $this->trainRepository = $trainRepository;
    }

    public function index()
    {
        $trains = $this->trainRepository->getAllTrains();
        return response()->json($trains);
    }

    public function show($id)
    {
        $train = $this->trainRepository->getTrainById($id);
        return response()->json($train);
    }
}
