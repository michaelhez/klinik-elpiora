<?php

declare(strict_types=1);

use App\Controllers\Api\QueueController;
use App\Controllers\Api\SearchController;
use App\Core\Router;

/** @var Router $router */

$router->group(['AuthMiddleware'], function (Router $router) {
    $router->get('/api/patients/search', [SearchController::class, 'patients'], ['PermissionMiddleware:patient.view']);
    $router->get('/api/doctors/search', [SearchController::class, 'doctors'], ['PermissionMiddleware:doctor.view']);
    $router->get('/api/icd10/search', [SearchController::class, 'icd10'], ['PermissionMiddleware:icd10.view']);
    $router->get('/api/medical-actions/search', [SearchController::class, 'medicalActions'], ['PermissionMiddleware:medical_action.view']);
    $router->get('/api/lab-tests/search', [SearchController::class, 'labTests'], ['PermissionMiddleware:laboratory.request']);
    $router->get('/api/medicines/search', [SearchController::class, 'medicines'], ['PermissionMiddleware:medicine.view']);
    $router->get('/api/medicines/{id}/batches', [SearchController::class, 'medicineBatches'], ['PermissionMiddleware:medicine.view']);

    $router->get('/api/queue/board-data', [QueueController::class, 'boardData'], ['PermissionMiddleware:queue.view']);
    $router->post('/api/queue/call/{id}', [QueueController::class, 'call'], ['CsrfMiddleware', 'PermissionMiddleware:queue.call']);
    $router->post('/api/queue/recall/{id}', [QueueController::class, 'recall'], ['CsrfMiddleware', 'PermissionMiddleware:queue.recall']);
    $router->post('/api/queue/complete/{id}', [QueueController::class, 'complete'], ['CsrfMiddleware', 'PermissionMiddleware:queue.complete']);
    $router->post('/api/queue/skip/{id}', [QueueController::class, 'skip'], ['CsrfMiddleware', 'PermissionMiddleware:queue.skip']);
});
