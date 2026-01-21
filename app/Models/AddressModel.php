<?php

namespace App\Models;

use CodeIgniter\Model;

class AddressModel extends Model
{
    protected $table            = 'addresses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'owner_type', 'owner_id', 'address_type', 'address_line1', 'address_line2', 
        'city', 'pincode', 'state_id', 'country_id', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'owner_type'    => 'required|in_list[customer,vendor]',
        'owner_id'      => 'required|integer',
        'address_type'  => 'required|in_list[billing,shipping]',
        'address_line1' => 'required|max_length[255]',
        'city'          => 'required|max_length[100]',
        'pincode'       => 'required|max_length[10]',
        'state_id'      => 'required|integer',
    ];

    /**
     * Mark existing addresses of a certain type as inactive for an owner
     */
    public function deactivateOthers($ownerType, $ownerId, $addressType)
    {
        return $this->where('owner_type', $ownerType)
                    ->where('owner_id', $ownerId)
                    ->where('address_type', $addressType)
                    ->set(['is_active' => 0])
                    ->update();
    }

    /**
     * Get the active address for an owner
     */
    public function getActiveAddress($ownerType, $ownerId, $addressType)
    {
        return $this->where('owner_type', $ownerType)
                    ->where('owner_id', $ownerId)
                    ->where('address_type', $addressType)
                    ->where('is_active', 1)
                    ->first();
    }
}
