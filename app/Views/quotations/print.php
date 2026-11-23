<?php
$q = $quotation ?? [];

$printItems = [];
foreach (($q['items'] ?? []) as $it) {
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
    'document_title'        => 'ESTIMATE / QUOTATION',
    'document_number_label' => 'Quotation#',
    'document_number'       => $q['quotation_number'] ?? '',
    'date_label'            => 'Date',
    'document_date'         => $q['quotation_date'] ?? date('Y-m-d'),
    'due_date'              => $q['valid_until'] ?? null,
    'reference_number'      => $q['reference_number'] ?? null,
    
    // Customer
    'bill_to_heading'       => 'Quotation To :',
    'billing_name'          => $q['customer_name'] ?? '',
    'billing_gstin'         => $q['customer_gstin'] ?? '',
    'billing_address'       => implode(",\n", array_filter([$q['customer_address1'] ?? '', $q['customer_address2'] ?? ''])),
    'billing_city'          => $q['customer_city'] ?? '',
    'billing_pincode'       => $q['customer_pincode'] ?? '',
    'billing_state'         => $q['customer_state'] ?? '',
    'billing_phone'         => implode(', ', array_filter([$q['customer_phone'] ?? '', $q['customer_whatsapp'] ?? ''])),

    'ship_to_heading'       => 'Ship To :',
    'shipping_name'         => $q['customer_name'] ?? '',
    'shipping_gstin'        => $q['customer_gstin'] ?? '',
    'shipping_address'      => implode(",\n", array_filter([$q['customer_address1'] ?? '', $q['customer_address2'] ?? ''])),
    'shipping_city'         => $q['customer_city'] ?? '',
    'shipping_pincode'      => $q['customer_pincode'] ?? '',
    'shipping_state'        => $q['customer_state'] ?? '',
    'shipping_phone'        => implode(', ', array_filter([$q['customer_phone'] ?? '', $q['customer_whatsapp'] ?? ''])),

    'place_of_supply'       => !empty($q['customer_state']) ? $q['customer_state'] : '',
    
    'items'                 => $printItems,
    
    'subtotal'              => $q['subtotal'] ?? 0,
    'discount_amount'       => $q['discount_amount'] ?? 0,
    'cgst_amount'           => $q['cgst_amount'] ?? 0,
    'sgst_amount'           => $q['sgst_amount'] ?? 0,
    'igst_amount'           => $q['igst_amount'] ?? 0,
    'shipping_charge'       => $q['shipping_charge'] ?? 0,
    'roundoff_amount'       => $q['roundoff_amount'] ?? 0,
    'total_amount'          => $q['total_amount'] ?? 0,

    'notes'                 => $q['notes'] ?? '',
    'terms'                 => $q['terms'] ?? '',
    'auto_print'            => $auto_print ?? true,
];

echo view('common/print/standard_document', $printData);
