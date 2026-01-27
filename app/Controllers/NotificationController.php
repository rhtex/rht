<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificationModel;
use App\Models\ProductModel;
use App\Models\InvoiceModel;

class NotificationController extends BaseController
{
    protected $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    public function index()
    {
        \App\Libraries\Notifier::refresh();
        
        $data['notifications'] = $this->notificationModel->orderBy('is_read', 'ASC')
                                                        ->orderBy('created_at', 'DESC')
                                                        ->findAll();
        
        return view('notifications/index', $data);
    }

    public function markAsRead($id)
    {
        $this->notificationModel->update($id, ['is_read' => 1]);
        return redirect()->back();
    }

    public function markAllRead()
    {
        $this->notificationModel->markAllAsRead();
        return redirect()->back();
    }
}
