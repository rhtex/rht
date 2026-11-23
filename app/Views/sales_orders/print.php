<?php
$so = $order ?? [];

$printItems = [];
foreach (($so['items'] ?? []) as $it) {
    $printItems[] = [
        'name'        => $it['product_name'] ?? ($it['description'] ?? ''),
        'description' => $it['description'] ?? ($it['product_name'] ?? ''),
        'meters'      => !empty($it['unit']) ? $it['unit'] : '6.20',
        'unit'        => !empty($it['unit']) ? $it['unit'] : '6.20',
        'hsn_code'    => $it['hsn_code'] ?? ($it['product_hsn'] ?? '5208'),
        'quantity'    => $it['quantity'] ?? 0,
        'rate'        => $it['rate'] ?? 0,
        'amount'      => $it['amount'] ?? 0,
    ];
}

$printData = [
    'document_title'        => 'SALES ORDER',
    'document_number_label' => 'Order#',
    'document_number'       => $so['sales_order_number'] ?? '',
    'date_label'            => 'Order Date',
    'document_date'         => $so['order_date'] ?? date('Y-m-d'),
    'due_date'              => $so['shipment_date'] ?? null,
    'transport_name'        => $so['transport_name'] ?? null,
    'waybill_number'        => $so['waybill_number'] ?? null,
    'reference_number'      => $so['reference_number'] ?? null,
    
    // Customer Billing & Shipping
    'bill_to_heading'       => 'Bill To :',
    'billing_name'          => $so['customer_name'] ?? '',
    'billing_gstin'         => $so['customer_gstin'] ?? '',
    'billing_address'       => implode(",\n", array_filter([$so['customer_address_1'] ?? '', $so['customer_address_2'] ?? ''])),
    'billing_city'          => $so['customer_city'] ?? '',
    'billing_pincode'       => $so['customer_pincode'] ?? '',
    'billing_state'         => $so['customer_state'] ?? '',
    'billing_phone'         => implode(', ', array_filter([$so['customer_phone'] ?? '', $so['customer_whatsapp'] ?? ''])),

    'ship_to_heading'       => 'Ship To :',
    'shipping_name'         => $so['customer_name'] ?? '',
    'shipping_gstin'        => $so['customer_gstin'] ?? '',
    'shipping_address'      => implode(",\n", array_filter([$so['shipping_address_1'] ?? ($so['customer_address_1'] ?? ''), $so['shipping_address_2'] ?? ($so['customer_address_2'] ?? '')])),
    'shipping_city'         => $so['shipping_city'] ?? ($so['customer_city'] ?? ''),
    'shipping_pincode'      => $so['shipping_pincode'] ?? ($so['customer_pincode'] ?? ''),
    'shipping_state'        => $so['shipping_state'] ?? ($so['customer_state'] ?? ''),
    'shipping_phone'        => implode(', ', array_filter([$so['customer_phone'] ?? '', $so['customer_whatsapp'] ?? ''])),

    'place_of_supply'       => !empty($so['customer_state']) ? $so['customer_state'] . (!empty($so['customer_state_gst_code']) ? ' (' . $so['customer_state_gst_code'] . ')' : '') : '',
    
    'items'                 => $printItems,
    
    'subtotal'              => $so['subtotal'] ?? 0,
    'discount_amount'       => $so['discount_amount'] ?? 0,
    'cgst_amount'           => $so['cgst_amount'] ?? 0,
    'sgst_amount'           => $so['sgst_amount'] ?? 0,
    'igst_amount'           => $so['igst_amount'] ?? 0,
    'shipping_charge'       => $so['shipping_charge'] ?? 0,
    'roundoff_amount'       => $so['roundoff_amount'] ?? 0,
    'total_amount'          => $so['total_amount'] ?? 0,

    'notes'                 => $so['notes'] ?? '',
    'terms'                 => $so['terms'] ?? '',
    'auto_print'            => $auto_print ?? true,
];

echo view('common/print/standard_document', $printData);
