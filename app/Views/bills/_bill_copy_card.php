<?= view('common/_copy_upload_card', [
    'title'     => 'Bill Copy (Supplier Invoice)',
    'filePath'  => $bill['bill_copy'] ?? '',
    'uploadUrl' => site_url('bills/upload-copy/' . $bill['id']),
    'deleteUrl' => site_url('bills/delete-copy/' . $bill['id']),
    'fieldName' => 'bill_copy',
    'canEdit'   => can('bill.edit'),
    'emptyText' => 'No bill copy uploaded.',
]) ?>
