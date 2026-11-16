<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVehiclesTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('vehicles')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'vehicle_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '150',
                    'null'       => true,
                ],
                'vehicle_model' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '150',
                    'null'       => true,
                ],
                'vehicle_number' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                ],
                'vehicle_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'default'    => 'Regular',
                    'comment'    => 'Regular / Over Dimensional Cargo',
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['active', 'inactive'],
                    'default'    => 'active',
                ],
                'created_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'updated_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('vehicle_number');
            $this->forge->createTable('vehicles');
        }

        // Register module & permissions if modules table exists
        if ($this->db->tableExists('modules')) {
            $existingMod = $this->db->table('modules')->where('module_slug', 'vehicle')->get()->getRowArray();
            $moduleId = $existingMod ? $existingMod['id'] : null;

            if (!$moduleId) {
                $this->db->table('modules')->insert([
                    'module_name' => 'Vehicles',
                    'module_slug' => 'vehicle',
                    'status'      => 'active',
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
                $moduleId = $this->db->insertID();
            }

            if ($this->db->tableExists('permissions') && $moduleId) {
                $perms = [
                    ['permission_key' => 'vehicle.view',   'permission_name' => 'View Vehicles'],
                    ['permission_key' => 'vehicle.create', 'permission_name' => 'Create Vehicle'],
                    ['permission_key' => 'vehicle.edit',   'permission_name' => 'Edit Vehicle'],
                    ['permission_key' => 'vehicle.delete', 'permission_name' => 'Delete Vehicle'],
                ];

                foreach ($perms as $p) {
                    $exist = $this->db->table('permissions')->where('permission_key', $p['permission_key'])->get()->getRowArray();
                    if (!$exist) {
                        $this->db->table('permissions')->insert([
                            'permission_key'  => $p['permission_key'],
                            'permission_name' => $p['permission_name'],
                            'module_id'       => $moduleId,
                            'created_at'      => date('Y-m-d H:i:s'),
                            'updated_at'      => date('Y-m-d H:i:s'),
                        ]);
                        $permId = $this->db->insertID();
                    } else {
                        $permId = $exist['id'];
                    }

                    // Assign to Super Admin / Admin role (role_id = 1)
                    if ($this->db->tableExists('role_permissions') && $permId) {
                        $rpExist = $this->db->table('role_permissions')
                            ->where('role_id', 1)
                            ->where('permission_id', $permId)
                            ->get()->getRowArray();
                        if (!$rpExist) {
                            $this->db->table('role_permissions')->insert([
                                'role_id'       => 1,
                                'permission_id' => $permId,
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('vehicles', true);
    }
}
