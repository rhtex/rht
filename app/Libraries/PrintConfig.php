<?php

namespace App\Libraries;

use App\Models\PrintConfigurationModel;

/**
 * Central registry & resolver for per-document print field configurations.
 *
 * Defines all available document types, their applicable fields grouped into
 * sections A-F, mandatory legal/tax flags, and default configurations matching
 * the standard ERP print layout.
 */
class PrintConfig
{
    public const SECTIONS = [
        'company'  => 'A. Company Details',
        'document' => 'B. Document Details',
        'party'    => 'C. Customer / Supplier Details',
        'items'    => 'D. Item Table Columns',
        'summary'  => 'E. Summary and Totals',
        'extra'    => 'F. Additional Information',
    ];

    /**
     * Supported document types in the ERP.
     */
    public static function getDocumentTypes(): array
    {
        return [
            'invoice' => [
                'label'        => 'Sales Invoice',
                'title'        => 'TAX INVOICE',
                'number_label' => 'Invoice#',
                'date_label'   => 'Invoice Date',
                'bill_heading' => 'Bill To :',
                'ship_heading' => 'Ship To :',
            ],
            'bill' => [
                'label'        => 'Purchase Bill',
                'title'        => 'PURCHASE BILL',
                'number_label' => 'Bill#',
                'date_label'   => 'Bill Date',
                'bill_heading' => 'Vendor / Bill From :',
                'ship_heading' => 'Deliver To :',
            ],
            'sales_order' => [
                'label'        => 'Sales Order',
                'title'        => 'SALES ORDER',
                'number_label' => 'Order#',
                'date_label'   => 'Order Date',
                'bill_heading' => 'Bill To :',
                'ship_heading' => 'Ship To :',
            ],
            'quotation' => [
                'label'        => 'Quotation / Estimate',
                'title'        => 'ESTIMATE / QUOTATION',
                'number_label' => 'Quotation#',
                'date_label'   => 'Date',
                'bill_heading' => 'Quotation To :',
                'ship_heading' => 'Ship To :',
            ],
            'credit_note' => [
                'label'        => 'Credit Note (Sales Return)',
                'title'        => 'CREDIT NOTE',
                'number_label' => 'Credit Note#',
                'date_label'   => 'Date',
                'bill_heading' => 'Credit To :',
                'ship_heading' => 'Party Details :',
            ],
            'debit_note' => [
                'label'        => 'Debit Note (Vendor Credit)',
                'title'        => 'DEBIT NOTE',
                'number_label' => 'Debit Note#',
                'date_label'   => 'Date',
                'bill_heading' => 'Debit To (Vendor) :',
                'ship_heading' => 'From :',
            ],
        ];
    }

    /**
     * Master catalog of all customisable fields.
     * `docs` = null means applicable to all document types; otherwise array of doc keys.
     */
    public static function getFieldCatalog(string $docType = 'invoice'): array
    {
        $docMeta = self::getDocumentTypes()[$docType] ?? self::getDocumentTypes()['invoice'];
        $allDocs = ['invoice', 'bill', 'sales_order', 'quotation', 'credit_note', 'debit_note'];

        $catalog = [
            // A. Company Details
            'company_logo' => [
                'section' => 'company', 'name' => 'Company Logo', 'label' => 'Company Logo',
                'visible' => true, 'bold' => false, 'align' => 'center', 'font_size' => '10px',
                'has_align' => false, 'has_label' => false, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'company_name' => [
                'section' => 'company', 'name' => 'Company Name', 'label' => 'Company Name',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '18px',
                'has_align' => false, 'has_label' => false, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'company_address' => [
                'section' => 'company', 'name' => 'Company Address', 'label' => 'Company Address',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => false, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'company_phone' => [
                'section' => 'company', 'name' => 'Phone Number', 'label' => 'Mobile:',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'company_email' => [
                'section' => 'company', 'name' => 'Email', 'label' => 'Email :',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'company_gstin' => [
                'section' => 'company', 'name' => 'Company GSTIN', 'label' => 'GSTIN :',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'company_website' => [
                'section' => 'company', 'name' => 'Website', 'label' => 'Website :',
                'visible' => false, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],

            // B. Document Details
            'doc_title' => [
                'section' => 'document', 'name' => 'Document Title', 'label' => $docMeta['title'],
                'visible' => true, 'bold' => true, 'align' => 'right', 'font_size' => '18px',
                'has_align' => true, 'has_label' => true, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'doc_number' => [
                'section' => 'document', 'name' => 'Document Number', 'label' => $docMeta['number_label'],
                'visible' => true, 'bold' => true, 'align' => 'right', 'font_size' => '12.5px',
                'has_align' => true, 'has_label' => true, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'doc_date' => [
                'section' => 'document', 'name' => 'Document Date', 'label' => $docMeta['date_label'],
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'payment_terms' => [
                'section' => 'document', 'name' => 'Payment Terms', 'label' => 'Terms',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill'],
            ],
            'due_date' => [
                'section' => 'document', 'name' => 'Due / Valid / Shipment Date', 'label' => $docType === 'sales_order' ? 'Shipment Date' : ($docType === 'quotation' ? 'Valid Until' : 'Due Date'),
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation'],
            ],
            'reference_number' => [
                'section' => 'document', 'name' => 'Reference Number', 'label' => 'Ref / PO#',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'order_number' => [
                'section' => 'document', 'name' => 'Order Number', 'label' => 'SO Number',
                'visible' => false, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice'],
            ],
            'place_of_supply' => [
                'section' => 'document', 'name' => 'Place of Supply', 'label' => 'Place Of Supply :',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '11px',
                'has_align' => true, 'has_label' => true, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'reverse_charge' => [
                'section' => 'document', 'name' => 'Reverse Charge', 'label' => 'Reverse Charge',
                'visible' => false, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill'],
            ],

            // C. Customer / Supplier Details
            'party_name' => [
                'section' => 'party', 'name' => 'Customer / Supplier Name', 'label' => 'Party Name',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '12px',
                'has_align' => false, 'has_label' => false, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'party_gstin' => [
                'section' => 'party', 'name' => 'Party GSTIN', 'label' => 'GST:',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '12px',
                'has_align' => false, 'has_label' => true, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'billing_address' => [
                'section' => 'party', 'name' => 'Billing / From Address', 'label' => $docMeta['bill_heading'],
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'shipping_address' => [
                'section' => 'party', 'name' => 'Shipping / Deliver To Address', 'label' => $docMeta['ship_heading'],
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'party_state' => [
                'section' => 'party', 'name' => 'State', 'label' => 'State',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => false, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'party_state_code' => [
                'section' => 'party', 'name' => 'State Code', 'label' => 'State Code:',
                'visible' => false, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'party_phone' => [
                'section' => 'party', 'name' => 'Contact Number', 'label' => 'Phone:',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'party_email' => [
                'section' => 'party', 'name' => 'Party Email', 'label' => 'Email:',
                'visible' => false, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],

            // D. Item Table Columns (ordered, draggable, width %)
            'serial_no' => [
                'section' => 'items', 'name' => 'Serial Number', 'label' => '#',
                'visible' => true, 'bold' => true, 'align' => 'center', 'font_size' => '10px', 'width' => 5,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'item_name' => [
                'section' => 'items', 'name' => 'Product Name', 'label' => 'Item',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10px', 'width' => 45,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'item_sku' => [
                'section' => 'items', 'name' => 'Product Code / SKU', 'label' => 'SKU',
                'visible' => false, 'bold' => true, 'align' => 'left', 'font_size' => '10px', 'width' => 10,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation', 'credit_note'],
            ],
            'item_description' => [
                'section' => 'items', 'name' => 'Description', 'label' => 'Description',
                'visible' => false, 'bold' => false, 'align' => 'left', 'font_size' => '10px', 'width' => 20,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'item_unit' => [
                'section' => 'items', 'name' => 'Unit / Meters', 'label' => 'Meters',
                'visible' => true, 'bold' => true, 'align' => 'center', 'font_size' => '10px', 'width' => 10,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'item_hsn' => [
                'section' => 'items', 'name' => 'HSN / SAC', 'label' => 'HSN/SAC',
                'visible' => true, 'bold' => true, 'align' => 'center', 'font_size' => '10px', 'width' => 12,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'item_qty' => [
                'section' => 'items', 'name' => 'Quantity', 'label' => 'Qty',
                'visible' => true, 'bold' => true, 'align' => 'center', 'font_size' => '10px', 'width' => 8,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'item_rate' => [
                'section' => 'items', 'name' => 'Rate', 'label' => 'Rate',
                'visible' => true, 'bold' => true, 'align' => 'right', 'font_size' => '10px', 'width' => 10,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'item_discount' => [
                'section' => 'items', 'name' => 'Discount', 'label' => 'Disc',
                'visible' => false, 'bold' => true, 'align' => 'right', 'font_size' => '10px', 'width' => 8,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation'],
            ],
            'item_taxable' => [
                'section' => 'items', 'name' => 'Taxable Amount', 'label' => 'Taxable',
                'visible' => false, 'bold' => true, 'align' => 'right', 'font_size' => '10px', 'width' => 12,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'item_cgst_pct' => [
                'section' => 'items', 'name' => 'CGST %', 'label' => 'CGST%',
                'visible' => false, 'bold' => true, 'align' => 'center', 'font_size' => '9.5px', 'width' => 7,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation', 'credit_note'],
            ],
            'item_cgst_amt' => [
                'section' => 'items', 'name' => 'CGST Amount', 'label' => 'CGST',
                'visible' => false, 'bold' => true, 'align' => 'right', 'font_size' => '9.5px', 'width' => 9,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation', 'credit_note'],
            ],
            'item_sgst_pct' => [
                'section' => 'items', 'name' => 'SGST %', 'label' => 'SGST%',
                'visible' => false, 'bold' => true, 'align' => 'center', 'font_size' => '9.5px', 'width' => 7,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation', 'credit_note'],
            ],
            'item_sgst_amt' => [
                'section' => 'items', 'name' => 'SGST Amount', 'label' => 'SGST',
                'visible' => false, 'bold' => true, 'align' => 'right', 'font_size' => '9.5px', 'width' => 9,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation', 'credit_note'],
            ],
            'item_igst_pct' => [
                'section' => 'items', 'name' => 'IGST %', 'label' => 'IGST%',
                'visible' => false, 'bold' => true, 'align' => 'center', 'font_size' => '9.5px', 'width' => 7,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation', 'credit_note'],
            ],
            'item_igst_amt' => [
                'section' => 'items', 'name' => 'IGST Amount', 'label' => 'IGST',
                'visible' => false, 'bold' => true, 'align' => 'right', 'font_size' => '9.5px', 'width' => 9,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation', 'credit_note'],
            ],
            'item_cess' => [
                'section' => 'items', 'name' => 'Cess', 'label' => 'Cess',
                'visible' => false, 'bold' => true, 'align' => 'right', 'font_size' => '9.5px', 'width' => 7,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill'],
            ],
            'item_amount' => [
                'section' => 'items', 'name' => 'Total Amount', 'label' => 'Amount',
                'visible' => true, 'bold' => true, 'align' => 'right', 'font_size' => '10px', 'width' => 10,
                'has_align' => true, 'has_label' => true, 'has_width' => true, 'mandatory' => true, 'docs' => $allDocs,
            ],

            // E. Summary and Totals
            'total_items_qty' => [
                'section' => 'summary', 'name' => 'Items in Total Qty', 'label' => 'Items in Total',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '11px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'subtotal' => [
                'section' => 'summary', 'name' => 'Subtotal', 'label' => 'Sub Total',
                'visible' => true, 'bold' => true, 'align' => 'right', 'font_size' => '11px',
                'has_align' => false, 'has_label' => true, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'total_discount' => [
                'section' => 'summary', 'name' => 'Total Discount', 'label' => 'Discount',
                'visible' => true, 'bold' => true, 'align' => 'right', 'font_size' => '11px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation'],
            ],
            'packing_charge' => [
                'section' => 'summary', 'name' => 'Packing Charges', 'label' => 'Packing Charges',
                'visible' => false, 'bold' => true, 'align' => 'right', 'font_size' => '11px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill', 'sales_order'],
            ],
            'shipping_charge' => [
                'section' => 'summary', 'name' => 'Transport / Shipping Charges', 'label' => 'Shipping Charge',
                'visible' => true, 'bold' => true, 'align' => 'right', 'font_size' => '11px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation'],
            ],
            'other_charges' => [
                'section' => 'summary', 'name' => 'Other Charges', 'label' => 'Other Charges',
                'visible' => false, 'bold' => true, 'align' => 'right', 'font_size' => '11px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill'],
            ],
            'taxable_value' => [
                'section' => 'summary', 'name' => 'Taxable Value', 'label' => 'Taxable Value',
                'visible' => false, 'bold' => true, 'align' => 'right', 'font_size' => '11px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'cgst_total' => [
                'section' => 'summary', 'name' => 'CGST Total', 'label' => 'CGST',
                'visible' => true, 'bold' => true, 'align' => 'right', 'font_size' => '11px',
                'has_align' => false, 'has_label' => true, 'mandatory' => true, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation', 'credit_note'],
            ],
            'sgst_total' => [
                'section' => 'summary', 'name' => 'SGST Total', 'label' => 'SGST',
                'visible' => true, 'bold' => true, 'align' => 'right', 'font_size' => '11px',
                'has_align' => false, 'has_label' => true, 'mandatory' => true, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation', 'credit_note'],
            ],
            'igst_total' => [
                'section' => 'summary', 'name' => 'IGST Total', 'label' => 'IGST',
                'visible' => true, 'bold' => true, 'align' => 'right', 'font_size' => '11px',
                'has_align' => false, 'has_label' => true, 'mandatory' => true, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation', 'credit_note'],
            ],
            'roundoff_amount' => [
                'section' => 'summary', 'name' => 'Round Off', 'label' => 'Round Off',
                'visible' => true, 'bold' => true, 'align' => 'right', 'font_size' => '11px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice', 'bill', 'sales_order', 'quotation'],
            ],
            'grand_total' => [
                'section' => 'summary', 'name' => 'Grand Total', 'label' => 'Total',
                'visible' => true, 'bold' => true, 'align' => 'right', 'font_size' => '13px',
                'has_align' => false, 'has_label' => true, 'mandatory' => true, 'docs' => $allDocs,
            ],
            'amount_in_words' => [
                'section' => 'summary', 'name' => 'Amount in Words', 'label' => 'Total In Words :',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '11px',
                'has_align' => true, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],

            // F. Additional Information
            'notes' => [
                'section' => 'extra', 'name' => 'Notes', 'label' => 'Notes :',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'terms_conditions' => [
                'section' => 'extra', 'name' => 'Terms and Conditions', 'label' => 'Terms & Conditions :',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '9.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'transport_name' => [
                'section' => 'extra', 'name' => 'Transporter Name', 'label' => 'Transport',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice', 'sales_order', 'quotation'],
            ],
            'vehicle_number' => [
                'section' => 'extra', 'name' => 'Vehicle Number', 'label' => 'Vehicle No',
                'visible' => false, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice'],
            ],
            'waybill_number' => [
                'section' => 'extra', 'name' => 'Waybill / LR Number', 'label' => 'Waybill/LR#',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice', 'sales_order', 'quotation'],
            ],
            'bales_count' => [
                'section' => 'extra', 'name' => 'No of Bales / Packages', 'label' => 'No of Bales',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice'],
            ],
            'ewaybill_number' => [
                'section' => 'extra', 'name' => 'E-Way Bill Number', 'label' => 'E-Way Bill#',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice'],
            ],
            'delivery_details' => [
                'section' => 'extra', 'name' => 'Delivery / Courier Details', 'label' => 'Delivery Details',
                'visible' => false, 'bold' => true, 'align' => 'left', 'font_size' => '10.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => ['invoice'],
            ],
            'bank_details' => [
                'section' => 'extra', 'name' => 'Bank Account Details', 'label' => 'Bank Account Details',
                'visible' => true, 'bold' => true, 'align' => 'left', 'font_size' => '9.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'upi_details' => [
                'section' => 'extra', 'name' => 'UPI Details', 'label' => 'UPI ID :',
                'visible' => false, 'bold' => true, 'align' => 'left', 'font_size' => '9.5px',
                'has_align' => false, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'prepared_by' => [
                'section' => 'extra', 'name' => 'Prepared By', 'label' => 'Prepared By',
                'visible' => false, 'bold' => true, 'align' => 'center', 'font_size' => '10px',
                'has_align' => true, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'authorised_signature' => [
                'section' => 'extra', 'name' => 'Authorised Signature', 'label' => 'Authorized Signature',
                'visible' => true, 'bold' => true, 'align' => 'center', 'font_size' => '11px',
                'has_align' => true, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
            'company_seal' => [
                'section' => 'extra', 'name' => 'Company Seal', 'label' => '(Company Seal)',
                'visible' => false, 'bold' => true, 'align' => 'center', 'font_size' => '9.5px',
                'has_align' => true, 'has_label' => true, 'mandatory' => false, 'docs' => $allDocs,
            ],
        ];

        $order = 1;
        $filtered = [];
        foreach ($catalog as $key => $def) {
            if (!in_array($docType, $def['docs'], true)) {
                continue;
            }
            $def['key'] = $key;
            $def['order'] = $order++;
            $filtered[$key] = $def;
        }

        return $filtered;
    }

    /**
     * Build the default full configuration array for a document type.
     */
    public static function getDefaultConfig(string $docType): array
    {
        return [
            'document_type'  => $docType,
            'paper_size'     => 'A4',
            'orientation'    => 'portrait',
            'font_family'    => 'Helvetica, Arial, sans-serif',
            'base_font_size' => '10.5px',
            'fields'         => self::getFieldCatalog($docType),
        ];
    }

    /**
     * Load the effective configuration for a document type from DB merged with catalog defaults.
     * Ensures newly added fields in future releases still appear with defaults.
     */
    public static function getForDocument(string $docType, ?array $override = null): array
    {
        $default = self::getDefaultConfig($docType);

        if ($override !== null) {
            return self::mergeConfig($default, $override);
        }

        try {
            $model = new PrintConfigurationModel();
            $row   = $model->getByDocumentType($docType);
            if ($row && !empty($row['config_json'])) {
                $decoded = json_decode($row['config_json'], true);
                if (is_array($decoded)) {
                    $saved = [
                        'paper_size'     => $row['paper_size'] ?? 'A4',
                        'orientation'    => $row['orientation'] ?? 'portrait',
                        'font_family'    => $row['font_family'] ?? 'Helvetica, Arial, sans-serif',
                        'base_font_size' => $row['base_font_size'] ?? '10.5px',
                        'fields'         => $decoded['fields'] ?? $decoded,
                    ];
                    return self::mergeConfig($default, $saved);
                }
            }
        } catch (\Throwable $e) {
            // Table might not exist before migration; safely fall back to defaults
        }

        return $default;
    }

    /**
     * Merge saved settings on top of default catalog.
     */
    public static function mergeConfig(array $default, array $saved): array
    {
        $result = $default;
        foreach (['paper_size', 'orientation', 'font_family', 'base_font_size'] as $prop) {
            if (!empty($saved[$prop])) {
                $result[$prop] = $saved[$prop];
            }
        }

        $savedFields = $saved['fields'] ?? [];
        if (is_array($savedFields)) {
            foreach ($result['fields'] as $key => &$field) {
                if (isset($savedFields[$key]) && is_array($savedFields[$key])) {
                    $sf = $savedFields[$key];
                    $field['visible']   = !empty($sf['visible']);
                    if (isset($sf['label']) && $sf['label'] !== '') {
                        $field['label'] = (string) $sf['label'];
                    }
                    if (isset($sf['order'])) {
                        $field['order'] = (int) $sf['order'];
                    }
                    $field['bold']      = !empty($sf['bold']);
                    if (!empty($sf['align']) && in_array($sf['align'], ['left', 'center', 'right'], true)) {
                        $field['align'] = $sf['align'];
                    }
                    if (!empty($sf['font_size'])) {
                        $field['font_size'] = (string) $sf['font_size'];
                    }
                    if (isset($sf['width']) && is_numeric($sf['width'])) {
                        $field['width'] = max(2, min(80, (float) $sf['width']));
                    }
                }
            }
            unset($field);
        }

        // Sort fields by order
        uasort($result['fields'], fn ($a, $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));

        return $result;
    }

    /**
     * Helper to return fields for a specific section sorted by order.
     */
    public static function getSectionFields(array $config, string $section, bool $onlyVisible = false): array
    {
        $out = [];
        foreach (($config['fields'] ?? []) as $key => $f) {
            if (($f['section'] ?? '') !== $section) {
                continue;
            }
            if ($onlyVisible && empty($f['visible'])) {
                continue;
            }
            $out[$key] = $f;
        }
        return $out;
    }

    /**
     * Realistic sample data for Live Print Preview in Settings.
     */
    public static function getSampleData(string $docType): array
    {
        $meta = self::getDocumentTypes()[$docType] ?? self::getDocumentTypes()['invoice'];

        return [
            'document_type'         => $docType,
            'document_title'        => $meta['title'],
            'document_number_label' => $meta['number_label'],
            'document_number'       => strtoupper(substr($docType, 0, 3)) . '-001941',
            'date_label'            => $meta['date_label'],
            'document_date'         => date('Y-m-d'),
            'due_date'              => date('Y-m-d', strtotime('+30 days')),
            'payment_terms'         => 'Net 30',
            'reference_number'      => 'PO-2026-084',
            'order_number'          => 'SO-00412',
            'reverse_charge'        => 'No',
            'ewaybill_number'       => '552108724954',
            'transport_name'        => 'VRL LOGISTICS LIMITED',
            'vehicle_number'        => 'TN-28-AB-4567',
            'waybill_number'        => 'LR-889021',
            'bales_count'           => '4',
            'delivery_details'      => 'Dispatched via VRL Express, Track# VRL99201',

            'bill_to_heading'       => $meta['bill_heading'],
            'billing_name'          => 'POOJITHA HANDLOOMS',
            'billing_gstin'         => '37ALEPG1104G1ZQ',
            'billing_address'       => "1ST FLOOR, 40-9-74, 74A,\nREVENUE WARD 13, TRENDSET MALL",
            'billing_city'          => 'Vijayawada',
            'billing_pincode'       => '520010',
            'billing_state'         => 'Andhra Pradesh',
            'billing_state_code'    => '37',
            'billing_phone'         => '9247568247',
            'billing_email'         => 'accounts@poojithahandlooms.in',

            'ship_to_heading'       => $meta['ship_heading'],
            'shipping_name'         => 'POOJITHA HANDLOOMS',
            'shipping_gstin'        => '37ALEPG1104G1ZQ',
            'shipping_address'      => "1ST FLOOR, 40-9-74, 74A,\nREVENUE WARD 13, BENZ CIRCLE",
            'shipping_city'         => 'Vijayawada',
            'shipping_pincode'      => '520010',
            'shipping_state'        => 'Andhra Pradesh',
            'shipping_state_code'   => '37',
            'shipping_phone'        => '9247568247',
            'shipping_email'        => 'dispatch@poojithahandlooms.in',

            'place_of_supply'       => 'Andhra Pradesh (37)',
            'is_inter_state'        => 0,

            'items' => [
                [
                    'name'        => 'Satha White Cross Kattam',
                    'sku'         => 'SWCK-620',
                    'description' => 'Pure Cotton Saree with Zari Border',
                    'unit'        => '6.20',
                    'meters'      => '6.20',
                    'hsn_code'    => '5208',
                    'quantity'    => 25,
                    'rate'        => 400.00,
                    'discount'    => 0.00,
                    'taxable'     => 10000.00,
                    'cgst_rate'   => 2.5,
                    'cgst_amount' => 250.00,
                    'sgst_rate'   => 2.5,
                    'sgst_amount' => 250.00,
                    'igst_rate'   => 5.0,
                    'igst_amount' => 0.00,
                    'cess_amount' => 0.00,
                    'amount'      => 10000.00,
                ],
                [
                    'name'        => 'Satha Plain White Cross Thread Pet',
                    'sku'         => 'SPWC-620',
                    'description' => 'Handloom Cotton Cross Thread Finish',
                    'unit'        => '6.20',
                    'meters'      => '6.20',
                    'hsn_code'    => '5208',
                    'quantity'    => 15,
                    'rate'        => 440.00,
                    'discount'    => 100.00,
                    'taxable'     => 6500.00,
                    'cgst_rate'   => 2.5,
                    'cgst_amount' => 162.50,
                    'sgst_rate'   => 2.5,
                    'sgst_amount' => 162.50,
                    'igst_rate'   => 5.0,
                    'igst_amount' => 0.00,
                    'cess_amount' => 0.00,
                    'amount'      => 6500.00,
                ],
                [
                    'name'        => 'Satha Richpallo Coin Putta',
                    'sku'         => 'SRCP-620',
                    'description' => 'Traditional Rich Pallu Coin Motif',
                    'unit'        => '6.20',
                    'meters'      => '6.20',
                    'hsn_code'    => '5208',
                    'quantity'    => 10,
                    'rate'        => 860.00,
                    'discount'    => 0.00,
                    'taxable'     => 8600.00,
                    'cgst_rate'   => 2.5,
                    'cgst_amount' => 215.00,
                    'sgst_rate'   => 2.5,
                    'sgst_amount' => 215.00,
                    'igst_rate'   => 5.0,
                    'igst_amount' => 0.00,
                    'cess_amount' => 0.00,
                    'amount'      => 8600.00,
                ],
            ],

            'subtotal'        => 25100.00,
            'discount_amount' => 100.00,
            'packing_charge'  => 150.00,
            'shipping_charge' => 250.00,
            'other_charges'   => 0.00,
            'taxable_value'   => 25000.00,
            'cgst_amount'     => 627.50,
            'sgst_amount'     => 627.50,
            'igst_amount'     => 0.00,
            'roundoff_amount' => 0.00,
            'total_amount'    => 26655.00,
            'notes'           => 'Please verify bale count upon delivery.',
            'prepared_by'     => 'Admin User',
            'auto_print'      => false,
            'is_preview'      => true,
        ];
    }
}
