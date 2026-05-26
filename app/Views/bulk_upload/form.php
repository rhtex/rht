<?php
/**
 * Bulk Upload Form View
 *
 * Provides a modern, responsive UI for uploading CSV or XLSX files
 * for Customers or Vendors. Includes glassmorphism and subtle
 * animations for a premium experience.
 */
?>
<?= view('layouts/header') ?>
<div class="container py-5" style="max-width: 800px;">
  <div class="card bg-glass shadow-lg border-0" style="backdrop-filter: blur(12px); background: rgba(255,255,255,0.15);">
    <div class="card-body p-4">
      <h2 class="mb-4 text-center" style="font-family: 'Inter', sans-serif; color: #0d6efd;">Bulk Upload</h2>
      <?php if (session('error')): ?>
        <div class="alert alert-danger" role="alert">
          <?= esc(session('error')) ?>
        </div>
      <?php endif; ?>
      <?php if (session('message')): ?>
        <div class="alert alert-success" role="alert">
          <?= esc(session('message')) ?>
        </div>
      <?php endif; ?>
      <form action="<?= base_url('bulk-upload/process') ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label fw-medium" for="typeSelect">Upload Type</label>
          <select id="typeSelect" name="type" class="form-select" required>
            <option value="customer">Customer</option>
            <option value="vendor">Vendor</option>
          </select>
          <div class="invalid-feedback">Please select an upload type.</div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-medium" for="fileInput">File (CSV or XLSX)</label>
          <input type="file" id="fileInput" name="file" class="form-control" accept=".csv,.xlsx,.xls" required />
          <div class="invalid-feedback">
              Please choose a file to upload.
          </div>
          <p class="mt-2"><a href="<?php echo base_url('sample_customer.csv'); ?>" class="text-decoration-none"><i class="bi bi-download"></i> Download Sample CSV</a></p>
        </div>
        <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(45deg, #4e54c8, #8f94fb); border: none;">Upload</button>
      </form>
    </div>
  </div>
</div>
<script>
  // Bootstrap custom validation
  (function () {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  })();
</script>
<?= view('layouts/footer') ?>
