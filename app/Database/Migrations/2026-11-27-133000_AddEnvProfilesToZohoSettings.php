<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEnvProfilesToZohoSettings extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('env_profiles_json', 'zoho_settings')) {
            $this->db->query("ALTER TABLE `zoho_settings` ADD COLUMN `env_profiles_json` LONGTEXT NULL AFTER `finance_default_account_map_json`");
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('env_profiles_json', 'zoho_settings')) {
            $this->db->query("ALTER TABLE `zoho_settings` DROP COLUMN `env_profiles_json`");
        }
    }
}
