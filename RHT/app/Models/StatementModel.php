<?php

namespace App\Models;

use CodeIgniter\Model;

class StatementModel extends Model
{
    protected $table = 'bank_statements';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'bank_id',
        'transaction_type',
        'amount',
        'added_by',
        'receiver_name',
        // Changed from depositor_name
        'reference_no',
        'payment_others_reason',
        'type_of_payment',
        'mode_of_payment',
        'transaction_date'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Relationships
    public function bank()
    {
        return $this->belongsTo(BankModel::class, 'bank_id');
    }
}
