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

        // 1. Check for Low Stock
        $lowStockProducts = $productModel->where('total_stock <', 10)->findAll();
        foreach ($lowStockProducts as $product) {
            $msg = "Product '{$product['product_name']}' is low in stock ({$product['total_stock']} units left).";
            
            $exists = $notificationModel->where('type', 'low_stock')
                                            ->where('reference_id', $product['id'])
                                            ->where('is_read', 0)
                                            ->first();
            
            if (!$exists) {
                $notificationModel->insert([
                    'type'         => 'low_stock',
                    'title'        => 'Low Stock Alert',
                    'message'      => $msg,
                    'link'         => "products/view/{$product['id']}",
                    'reference_id' => $product['id']
                ]);
            }
        }

        // 2. Check for Invoices waiting for Transport LR
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
                    'type'         => 'lr_update',
                    'title'        => 'Transport LR Missing',
                    'message'      => $msg,
                    'link'         => "invoices/edit/{$invoice['id']}",
                    'reference_id' => $invoice['id']
                ]);
            }
        }
    }
}
