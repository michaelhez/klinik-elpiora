<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;
use App\Core\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /** @var array<string, array<int, string>> */
    private const ROLE_PERMISSIONS = [
        'admin' => ['*'],
        'receptionist' => [
            'dashboard.view',
            'patient.view', 'patient.create', 'patient.update',
            'appointment.view', 'appointment.create', 'appointment.update', 'appointment.delete',
            'registration.view', 'registration.create', 'registration.update',
            'queue.view', 'queue.call', 'queue.recall', 'queue.complete', 'queue.skip',
            'doctor.view', 'polyclinic.view', 'room.view',
        ],
        'doctor' => [
            'dashboard.view',
            'patient.view',
            'medical_record.view', 'medical_record.create', 'medical_record.update', 'medical_record.finalize', 'medical_record.amend',
            'diagnosis.view', 'diagnosis.create', 'diagnosis.update',
            'icd10.view',
            'medical_action.view', 'medical_action.create', 'medical_action.update',
            'prescription.view', 'prescription.create', 'prescription.update',
            'medicine.view',
            'laboratory.view', 'laboratory.request',
            'lab_test.view',
            'medical_document.view', 'medical_document.create',
            'queue.view', 'polyclinic.view', 'room.view',
        ],
        'nurse' => [
            'dashboard.view',
            'patient.view',
            'queue.view', 'queue.call', 'queue.complete',
            'medical_record.view', 'medical_record.create',
        ],
        'pharmacist' => [
            'dashboard.view',
            'medicine.view', 'medicine.create', 'medicine.update', 'medicine.delete',
            'medicine_category.view', 'medicine_category.create', 'medicine_category.update',
            'medicine_unit.view', 'medicine_unit.create', 'medicine_unit.update',
            'inventory.view', 'inventory.adjust', 'inventory.transfer',
            'prescription.view', 'prescription.dispense',
            'supplier.view', 'supplier.create', 'supplier.update',
            'supplier_return.view', 'supplier_return.create',
            'purchase_order.view', 'purchase_order.create', 'purchase_order.approve', 'purchase_order.receive',
        ],
        'lab_officer' => [
            'dashboard.view',
            'laboratory.view', 'laboratory.request', 'laboratory.process', 'laboratory.validate',
            'lab_test.view', 'patient.view',
        ],
        'cashier' => [
            'dashboard.view',
            'billing.view', 'billing.create', 'billing.update',
            'payment.view', 'payment.create',
            'insurance.view', 'corporate.view',
        ],
        'finance' => [
            'dashboard.view',
            'billing.view', 'billing.update',
            'payment.view', 'payment.refund',
            'insurance.view', 'insurance.create', 'insurance.update',
            'corporate.view', 'corporate.create', 'corporate.update',
            'report.view', 'report.export',
        ],
        'hr' => [
            'dashboard.view',
            'employee.view', 'employee.create', 'employee.update', 'employee.delete',
            'doctor.view', 'doctor.create', 'doctor.update', 'doctor.delete',
            'branch.view', 'department.view', 'department.create', 'department.update',
            'position.view', 'position.create', 'position.update',
            'polyclinic.view', 'room.view', 'specialization.view',
        ],
        'manager' => [
            'dashboard.view',
            'patient.view', 'medical_record.view',
            'report.view', 'report.export',
            'audit.view',
        ],
        'director' => [
            'dashboard.view',
            'patient.view', 'medical_record.view',
            'report.view', 'report.export',
            'audit.view',
        ],
    ];

    public function run(): void
    {
        $catalogue = config('permissions');
        $permissionIds = [];

        foreach ($catalogue as $module => $actions) {
            foreach ($actions as $action) {
                $name = "{$module}.{$action}";
                $existing = Permission::findBy('name', $name);

                if ($existing === null) {
                    $id = Permission::create([
                        'name' => $name,
                        'module' => $module,
                        'action' => $action,
                        'description' => ucwords(str_replace('_', ' ', $module)) . ' - ' . ucwords(str_replace('_', ' ', $action)),
                    ]);
                    $this->info("Permission created: {$name}");
                } else {
                    $id = $existing['id'];
                }

                $permissionIds[$name] = $id;
            }
        }

        $this->assignRolePermissions($permissionIds);
    }

    /**
     * @param array<string, int|string> $permissionIds
     */
    private function assignRolePermissions(array $permissionIds): void
    {
        $superAdmin = Role::findBySlug('super_admin');
        if ($superAdmin !== null) {
            Role::syncPermissions($superAdmin['id'], array_values($permissionIds));
            $this->info('Assigned all permissions to Super Admin');
        }

        foreach (self::ROLE_PERMISSIONS as $slug => $permissions) {
            $role = Role::findBySlug($slug);
            if ($role === null) {
                continue;
            }

            $ids = $permissions === ['*']
                ? array_values($permissionIds)
                : array_values(array_intersect_key($permissionIds, array_flip($permissions)));

            Role::syncPermissions($role['id'], $ids);
            $this->info("Assigned " . count($ids) . " permissions to {$slug}");
        }
    }
}
