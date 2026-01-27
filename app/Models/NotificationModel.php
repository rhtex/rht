<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'type', 'title', 'message', 'link', 'reference_id', 'is_read'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get unread notifications
     */
    public function getUnread()
    {
        return $this->where('is_read', 0)->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        return $this->where('is_read', 0)->set(['is_read' => 1])->update();
    }
}
