<?php
$inv = $invoice ?? [];

// Calculate terms from customer credit period or difference
$paymentTerms = '';
if (!empty($inv['invoice_date']) && !empty($inv['due_date'])) {
    $diffDays = (int) round((strtotime($inv['due_date']) - strtotime($inv['invoice_date'])) / (60 * 60 * 24));
    if ($diffDays > 0) {
        $paymentTerms = 'Net ' . $diffDays;
    }
}
if (empty($paymentTerms) && !empty($inv['customer_credit_period_days'])) {
    $paymentTerms = 'Net ' . (int) $inv['customer_credit_period_days'];
}

// Format items for print template
$printItems = [];
foreach (($inv['items'] ?? []) as $it) {
    $printItems[] = [
        'name'        => $it['product_name'] ?? ($it['description'] ?? ''),
        'description' => $it['description'] ?? ($it['product_name'] ?? ''),
        'meters'      => !empty($it['unit']) ? $it['unit'] : '6.20',
        'unit'        => !empty($it['unit']) ? $it['unit'] : '6.20',
        'hsn_code'    => $it['hsn_code'] ?? ($it['prod_hsn'] ?? '5208'),
        'quantity'    => $it['quantity'] ?? 0,
        'rate'        => $it['rate'] ?? 0,
        'amount'      => $it['amount'] ?? 0,
    ];
}

$printData = [
    'document_type'         => 'invoice',
    'document_title'        => 'TAX INVOICE',
    'document_number_label' => 'Invoice#',
    'document_number'       => $inv['invoice_number'] ?? '',
    'document_date'         => $inv['invoice_date'] ?? date('Y-m-d'),
    'due_date'              => $inv['due_date'] ?? null,
    'payment_terms'         => $paymentTerms,
    'ewaybill_number'       => $inv['ewaybill_number'] ?? null,
    'transport_name'        => $inv['transport_name'] ?? null,
    'waybill_number'        => $inv['waybill_number'] ?? null,
    'bales_count'           => !empty($inv['bales_count']) ? $inv['bales_count'] : (!empty($inv['packages_count']) ? $inv['packages_count'] : null),
    'reference_number'      => $inv['reference_number'] ?? null,
    'order_number'          => $inv['so_number'] ?? null,
    'is_inter_state'        => $inv['is_inter_state'] ?? null,
    'billing_email'         => $inv['customer_email'] ?? null,
    'billing_state_code'    => $inv['customer_state_gst_code'] ?? null,
    
    // Customer Billing & Shipping
    'bill_to_heading'       => 'Bill To :',
    'billing_name'          => $inv['customer_name'] ?? '',
    'billing_gstin'         => $inv['customer_gstin'] ?? '',
    'billing_address'       => implode(",\n", array_filter([$inv['customer_address_1'] ?? '', $inv['customer_address_2'] ?? ''])),
    'billing_city'          => $inv['customer_city'] ?? '',
    'billing_pincode'       => $inv['customer_pincode'] ?? '',
    'billing_state'         => $inv['customer_state'] ?? '',
    'billing_phone'         => implode(', ', array_filter([$inv['customer_phone'] ?? '', $inv['customer_whatsapp'] ?? ''])),

    'ship_to_heading'       => 'Ship To :',
    'shipping_name'         => $inv['customer_name'] ?? '',
    'shipping_gstin'        => $inv['customer_gstin'] ?? '',
    'shipping_address'      => implode(",\n", array_filter([$inv['shipping_address_1'] ?? ($inv['customer_address_1'] ?? ''), $inv['shipping_address_2'] ?? ($inv['customer_address_2'] ?? '')])),
    'shipping_city'         => $inv['shipping_city'] ?? ($inv['customer_city'] ?? ''),
    'shipping_pincode'      => $inv['shipping_pincode'] ?? ($inv['customer_pincode'] ?? ''),
    'shipping_state'        => $inv['shipping_state'] ?? ($inv['customer_state'] ?? ''),
    'shipping_phone'        => implode(', ', array_filter([$inv['customer_phone'] ?? '', $inv['customer_whatsapp'] ?? ''])),

    'place_of_supply'       => !empty($inv['customer_state']) ? $inv['customer_state'] . (!empty($inv['customer_state_gst_code']) ? ' (' . $inv['customer_state_gst_code'] . ')' : '') : '',
    
    // Line items
    'items'                 => $printItems,
    
    // Financials
    'subtotal'              => $inv['subtotal'] ?? 0,
    'discount_amount'       => $inv['discount_amount'] ?? 0,
    'cgst_amount'           => $inv['cgst_amount'] ?? 0,
    'sgst_amount'           => $inv['sgst_amount'] ?? 0,
    'igst_amount'           => $inv['igst_amount'] ?? 0,
    'shipping_charge'       => $inv['shipping_charge'] ?? 0,
    'roundoff_amount'       => $inv['roundoff_amount'] ?? 0,
    'total_amount'          => $inv['total_amount'] ?? 0,

    'notes'                 => $inv['notes'] ?? '',
    'terms'                 => $inv['terms'] ?? '',
    'auto_print'            => $auto_print ?? true,
];

echo view('common/print/standard_document', $printData);
