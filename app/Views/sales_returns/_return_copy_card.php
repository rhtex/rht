<?= view('common/_copy_upload_card', [
    'title'     => 'Sales Return Copy (Credit / Debit Note)',
    'filePath'  => $return['return_copy'] ?? '',
    'uploadUrl' => site_url('sales_returns/upload-copy/' . $return['id']),
    'deleteUrl' => site_url('sales_returns/delete-copy/' . $return['id']),
    'fieldName' => 'return_copy',
    'canEdit'   => can('invoice.edit'),
    'emptyText' => 'No sales return copy uploaded.',
]) ?>
