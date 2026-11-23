<?php
$b = $bill ?? [];

$paymentTerms = '';
if (!empty($b['bill_date']) && !empty($b['due_date'])) {
    $diffDays = (int) round((strtotime($b['due_date']) - strtotime($b['bill_date'])) / (60 * 60 * 24));
    if ($diffDays > 0) {
        $paymentTerms = 'Net ' . $diffDays;
    }
}

$printItems = [];
foreach (($b['items'] ?? []) as $it) {
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
    'document_title'        => 'PURCHASE BILL',
    'document_number_label' => 'Bill#',
    'document_number'       => $b['bill_number'] ?? '',
    'document_date'         => $b['bill_date'] ?? date('Y-m-d'),
    'due_date'              => $b['due_date'] ?? null,
    'payment_terms'         => $paymentTerms,
    'reference_number'      => $b['reference_number'] ?? null,
    
    // Vendor (Bill From / Vendor)
    'bill_to_heading'       => 'Vendor / Bill From :',
    'billing_name'          => $b['vendor_name'] ?? '',
    'billing_gstin'         => $b['vendor_gstin'] ?? '',
    'billing_address'       => implode(",\n", array_filter([$b['vendor_address1'] ?? '', $b['vendor_address2'] ?? ''])),
    'billing_city'          => $b['vendor_city'] ?? '',
    'billing_pincode'       => $b['vendor_pincode'] ?? '',
    'billing_state'         => $b['vendor_state'] ?? '',
    'billing_phone'         => implode(', ', array_filter([$b['vendor_phone'] ?? '', $b['vendor_whatsapp'] ?? ''])),

    'ship_to_heading'       => 'Deliver To :',
    'shipping_name'         => get_setting('company_print_name', get_setting('org_name', get_setting('app_name', 'RASI HANDLOOM TEXTILES'))),
    'shipping_gstin'        => get_setting('company_print_gstin', get_setting('company_gstin', '')),
    'shipping_address'      => get_setting('company_print_address', get_setting('company_address_1', '')),
    'shipping_city'         => get_setting('company_city', ''),
    'shipping_pincode'      => get_setting('company_pincode', ''),
    'shipping_state'        => 'Tamil Nadu (33)',
    'shipping_phone'        => get_setting('company_phone', ''),

    'place_of_supply'       => !empty($b['vendor_state']) ? $b['vendor_state'] : '',
    
    'items'                 => $printItems,
    
    'subtotal'              => $b['subtotal'] ?? 0,
    'discount_amount'       => $b['discount_amount'] ?? 0,
    'cgst_amount'           => $b['cgst_amount'] ?? 0,
    'sgst_amount'           => $b['sgst_amount'] ?? 0,
    'igst_amount'           => $b['igst_amount'] ?? 0,
    'shipping_charge'       => $b['shipping_charge'] ?? 0,
    'roundoff_amount'       => $b['roundoff_amount'] ?? 0,
    'total_amount'          => $b['total_amount'] ?? 0,

    'notes'                 => $b['notes'] ?? '',
    'terms'                 => $b['terms'] ?? '',
    'auto_print'            => $auto_print ?? true,
];

echo view('common/print/standard_document', $printData);
