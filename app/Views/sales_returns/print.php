<?php
$return = $return ?? [];

$printItems = [];
foreach (($items ?? ($return['items'] ?? [])) as $it) {
    $printItems[] = [
        'name'        => $it['description'] ?? ($it['product_name'] ?? ''),
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
    'document_type'         => 'credit_note',
    'document_title'        => 'CREDIT NOTE',
    'document_number_label' => 'Credit Note#',
    'document_number'       => $return['return_number'] ?? '',
    'date_label'            => 'Date',
    'document_date'         => $return['return_date'] ?? date('Y-m-d'),
    'reference_number'      => !empty($return['invoice_number']) ? 'Ref Inv: ' . $return['invoice_number'] : ($return['reason'] ?? null),
    
    // Customer
    'bill_to_heading'       => 'Credit To :',
    'billing_name'          => $return['customer_name'] ?? '',
    'billing_gstin'         => $return['customer_gstin'] ?? '',
    'billing_address'       => implode(",\n", array_filter([$return['customer_address1'] ?? ($return['customer_address_1'] ?? ''), $return['customer_address2'] ?? ($return['customer_address_2'] ?? '')])),
    'billing_city'          => $return['customer_city'] ?? '',
    'billing_pincode'       => $return['customer_pincode'] ?? '',
    'billing_state'         => $return['customer_state'] ?? '',
    'billing_phone'         => implode(', ', array_filter([$return['customer_phone'] ?? '', $return['customer_whatsapp'] ?? ''])),

    'ship_to_heading'       => 'Party Details :',
    'shipping_name'         => $return['customer_name'] ?? '',
    'shipping_gstin'        => $return['customer_gstin'] ?? '',
    'shipping_address'      => implode(",\n", array_filter([$return['customer_address1'] ?? ($return['customer_address_1'] ?? ''), $return['customer_address2'] ?? ($return['customer_address_2'] ?? '')])),
    'shipping_city'         => $return['customer_city'] ?? '',
    'shipping_pincode'      => $return['customer_pincode'] ?? '',
    'shipping_state'        => $return['customer_state'] ?? '',
    'shipping_phone'        => implode(', ', array_filter([$return['customer_phone'] ?? '', $return['customer_whatsapp'] ?? ''])),

    'place_of_supply'       => !empty($return['customer_state']) ? $return['customer_state'] : '',
    
    'items'                 => $printItems,
    
    'subtotal'              => $return['subtotal'] ?? 0,
    'cgst_amount'           => !empty($return['cgst_amount']) ? $return['cgst_amount'] : (!empty($return['tax_amount']) && empty($return['is_inter_state']) ? $return['tax_amount'] / 2 : 0),
    'sgst_amount'           => !empty($return['sgst_amount']) ? $return['sgst_amount'] : (!empty($return['tax_amount']) && empty($return['is_inter_state']) ? $return['tax_amount'] / 2 : 0),
    'igst_amount'           => !empty($return['igst_amount']) ? $return['igst_amount'] : (!empty($return['tax_amount']) && !empty($return['is_inter_state']) ? $return['tax_amount'] : 0),
    'total_amount'          => $return['total_amount'] ?? 0,

    'notes'                 => $return['reason'] ?? 'Credit note issued against returned goods.',
    'auto_print'            => $auto_print ?? true,
];

echo view('common/print/standard_document', $printData);
