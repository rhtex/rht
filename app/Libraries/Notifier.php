<?php

namespace App\Libraries;

use App\Models\NotificationModel;
use App\Models\ProductModel;
use App\Models\InvoiceModel;

class Notifier
{
    public static function refresh()
    {
        $notificationModel = new NotificationModel();
        $productModel = new ProductModel();
        $invoiceModel = new InvoiceModel();

        // 1. Check for Invoices waiting for Transport LR
        $waitingInvoices = $invoiceModel->where('transport_name !=', '')
            ->where("(waybill_number = '' OR waybill_number IS NULL)")
            ->findAll();

        foreach ($waitingInvoices as $invoice) {
            $msg = "Invoice #{$invoice['invoice_number']} is waiting for Transport LR (Waybill) update.";

            $exists = $notificationModel->where('type', 'lr_update')
                ->where('reference_id', $invoice['id'])
                ->where('is_read', 0)
                ->first();

            if (!$exists) {
                $notificationModel->insert([
                    'type' => 'lr_update',
                    'title' => 'Transport LR Missing',
                    'message' => $msg,
                    'link' => "invoices/edit/{$invoice['id']}",
                    'reference_id' => $invoice['id']
                ]);
            }
        }

        // 3. Auto-Resolve: Mark notifications as read if the issue is fixed
        $unread = $notificationModel->where('is_read', 0)->findAll();
        foreach ($unread as $notify) {
            $resolved = false;

            if ($notify['type'] == 'lr_update') {
                $inv = $invoiceModel->find($notify['reference_id']);
                if ($inv && !empty($inv['waybill_number']))
                    $resolved = true;
            } elseif ($notify['type'] == 'reminder') {
                $reminderModel = new \App\Models\CalendarReminderModel();
                $rem = $reminderModel->find($notify['reference_id']);
                if ($rem && $rem['status'] == 'completed')
                    $resolved = true;
            }

            if ($resolved) {
                $notificationModel->update($notify['id'], ['is_read' => 1]);
            }
        }
    }
}
