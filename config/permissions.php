<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Permission Catalogue
|--------------------------------------------------------------------------
| Master list of permissions grouped by module. Used by PermissionSeeder
| to populate the permissions table and keep permission strings consistent
| across the codebase (format: module.action).
*/

return [
    'dashboard' => ['view'],

    'user' => ['view', 'create', 'update', 'delete'],
    'role' => ['view', 'create', 'update', 'delete'],
    'permission' => ['view', 'assign'],

    'clinic' => ['view', 'create', 'update', 'delete'],
    'branch' => ['view', 'create', 'update', 'delete'],
    'department' => ['view', 'create', 'update', 'delete'],
    'polyclinic' => ['view', 'create', 'update', 'delete'],
    'room' => ['view', 'create', 'update', 'delete'],
    'position' => ['view', 'create', 'update', 'delete'],
    'specialization' => ['view', 'create', 'update', 'delete'],

    'employee' => ['view', 'create', 'update', 'delete'],
    'doctor' => ['view', 'create', 'update', 'delete'],

    'patient' => ['view', 'create', 'update', 'delete'],
    'appointment' => ['view', 'create', 'update', 'delete'],
    'registration' => ['view', 'create', 'update', 'delete'],
    'queue' => ['view', 'call', 'recall', 'complete', 'skip'],

    'medical_record' => ['view', 'create', 'update', 'finalize', 'amend'],
    'diagnosis' => ['view', 'create', 'update'],
    'icd10' => ['view', 'create', 'update', 'delete'],
    'medical_action' => ['view', 'create', 'update', 'delete'],
    'medical_action_category' => ['view', 'create', 'update', 'delete'],

    'lab_test' => ['view', 'create', 'update', 'delete'],
    'laboratory' => ['view', 'request', 'process', 'validate'],

    'medicine_category' => ['view', 'create', 'update', 'delete'],
    'medicine_unit' => ['view', 'create', 'update', 'delete'],
    'medicine' => ['view', 'create', 'update', 'delete'],
    'inventory' => ['view', 'adjust', 'transfer'],
    'prescription' => ['view', 'create', 'update', 'dispense'],

    'supplier' => ['view', 'create', 'update', 'delete'],
    'supplier_return' => ['view', 'create'],
    'purchase_order' => ['view', 'create', 'approve', 'receive'],

    'billing' => ['view', 'create', 'update'],
    'payment' => ['view', 'create', 'refund'],

    'insurance' => ['view', 'create', 'update'],
    'corporate' => ['view', 'create', 'update'],

    'medical_document' => ['view', 'create'],

    'report' => ['view', 'export'],
    'audit' => ['view'],
    'setting' => ['view', 'update'],
    'backup' => ['view', 'create'],
];
