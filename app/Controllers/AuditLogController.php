<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\AuditLogService;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogService $service = new AuditLogService())
    {
    }

    public function index(Request $request): never
    {
        $filters = [
            'module' => (string) $request->query('module', ''),
            'action' => (string) $request->query('action', ''),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
            'search' => (string) $request->query('search', ''),
        ];

        $page = max(1, (int) $request->query('page', 1));
        $result = $this->service->paginate($filters, $page, 25);

        $this->view('audit-logs.index', [
            'title' => 'Audit Log',
            'logs' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'modules' => $this->service->moduleOptions(),
            'actions' => $this->service->actionOptions(),
        ]);
    }

    public function show(Request $request, string $id): never
    {
        $log = $this->service->find($id);

        $this->success('OK', $log);
    }
}
