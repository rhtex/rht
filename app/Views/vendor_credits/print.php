<?php
$credit = $credit ?? [];

$printItems = [];
if (!empty($credit['items'])) {
    foreach ($credit['items'] as $it) {
        $printItems[] = [
            'name'        => $it['description'] ?? ($it['product_name'] ?? 'Vendor Debit Item'),
            'description' => $it['description'] ?? '',
            'meters'      => $it['unit'] ?? '6.20',
            'hsn_code'    => $it['hsn_code'] ?? '5208',
            'quantity'    => $it['quantity'] ?? 1,
            'rate'        => $it['rate'] ?? $it['amount'],
            'amount'      => $it['amount'] ?? 0,
        ];
    }
} else {
    $printItems[] = [
        'name'        => !empty($credit['notes']) ? $credit['notes'] : 'Debit adjustment for purchase return / vendor balance',
        'description' => !empty($credit['bill_number']) ? 'Against Bill# ' . $credit['bill_number'] : '',
        'meters'      => '-',
        'hsn_code'    => '-',
        'quantity'    => 1,
        'rate'        => $credit['amount'] ?? 0,
        'amount'      => $credit['amount'] ?? 0,
    ];
}

$printData = [
    'document_title'        => 'DEBIT NOTE',
    'document_number_label' => 'Debit Note#',
    'document_number'       => $credit['reference_no'] ?? '',
    'date_label'            => 'Date',
    'document_date'         => $credit['credit_note_date'] ?? date('Y-m-d'),
    'reference_number'      => !empty($credit['bill_number']) ? 'Bill: ' . $credit['bill_number'] : null,
    
    // Vendor
    'bill_to_heading'       => 'Debit To (Vendor) :',
    'billing_name'          => $credit['vendor_name'] ?? '',
    'billing_gstin'         => $credit['vendor_gstin'] ?? '',
    'billing_address'       => implode(",\n", array_filter([$credit['vendor_address1'] ?? '', $credit['vendor_address2'] ?? ''])),
    'billing_city'          => $credit['vendor_city'] ?? '',
    'billing_pincode'       => $credit['vendor_pincode'] ?? '',
    'billing_state'         => $credit['vendor_state'] ?? '',
    'billing_phone'         => implode(', ', array_filter([$credit['vendor_phone'] ?? '', $credit['vendor_whatsapp'] ?? ''])),

    'ship_to_heading'       => 'From :',
    'shipping_name'         => get_setting('company_print_name', get_setting('org_name', get_setting('app_name', 'RASI HANDLOOM TEXTILES'))),
    'shipping_gstin'        => get_setting('company_print_gstin', get_setting('company_gstin', '')),
    'shipping_address'      => get_setting('company_print_address', get_setting('company_address_1', '')),
    'shipping_city'         => get_setting('company_city', ''),
    'shipping_pincode'      => get_setting('company_pincode', ''),
    'shipping_state'        => 'Tamil Nadu (33)',
    'shipping_phone'        => get_setting('company_phone', ''),

    'place_of_supply'       => !empty($credit['vendor_state']) ? $credit['vendor_state'] : '',
    
    'items'                 => $printItems,
    
    'subtotal'              => $credit['amount'] ?? 0,
    'total_amount'          => $credit['amount'] ?? 0,

    'notes'                 => $credit['notes'] ?? 'Debit note recorded in books.',
    'auto_print'            => $auto_print ?? true,
];

echo view('common/print/standard_document', $printData);
