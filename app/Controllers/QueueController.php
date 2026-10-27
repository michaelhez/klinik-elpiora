<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Polyclinic;
use App\Services\QueueService;

class QueueController extends Controller
{
    public function __construct(private readonly QueueService $service = new QueueService())
    {
    }

    public function index(Request $request): never
    {
        $polyclinicId = $request->query('polyclinic_id');

        $queues = $polyclinicId
            ? $this->service->todayForPolyclinic($polyclinicId)
            : $this->service->todayAll();

        $this->view('queue.index', [
            'title' => 'Manajemen Antrian',
            'queues' => $queues,
            'polyclinics' => Polyclinic::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
            'selectedPolyclinic' => $polyclinicId,
        ]);
    }

    public function display(Request $request): never
    {
        $this->view('queue.display', [
            'title' => 'Papan Antrian',
            'polyclinics' => Polyclinic::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }
}
