<?php

namespace App\Controllers;

use App\Libraries\PdfRenderer;
use App\Libraries\PrintConfig;
use App\Models\PrintConfigurationModel;

class PrintSettingsController extends BaseController
{
    public function index()
    {
        $docTypes = PrintConfig::getDocumentTypes();
        $docType  = (string) ($this->request->getGet('doc') ?? 'invoice');
        if (!isset($docTypes[$docType])) {
            $docType = 'invoice';
        }

        $config = PrintConfig::getForDocument($docType);

        return view('settings/print_settings', [
            'docTypes'    => $docTypes,
            'activeDoc'   => $docType,
            'sections'    => PrintConfig::SECTIONS,
            'config'      => $config,
        ]);
    }

    public function save()
    {
        $docTypes = PrintConfig::getDocumentTypes();
        $docType  = (string) $this->request->getPost('document_type');
        if (!isset($docTypes[$docType])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid document type.']);
        }

        $payload = $this->extractOverrideFromPost();
        $merged  = PrintConfig::mergeConfig(PrintConfig::getDefaultConfig($docType), $payload);

        $model = new PrintConfigurationModel();
        $saved = $model->saveForDocumentType($docType, [
            'paper_size'     => $merged['paper_size'],
            'orientation'    => $merged['orientation'],
            'font_family'    => $merged['font_family'],
            'base_font_size' => $merged['base_font_size'],
            'config_json'    => json_encode(['fields' => $merged['fields']]),
        ]);

        return $this->response->setJSON([
            'success' => $saved,
            'message' => $saved ? 'Print configuration saved for ' . $docTypes[$docType]['label'] . '.' : 'Failed to save configuration.',
        ]);
    }

    public function reset()
    {
        $docTypes = PrintConfig::getDocumentTypes();
        $docType  = (string) $this->request->getPost('document_type');
        if (!isset($docTypes[$docType])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid document type.']);
        }

        $model = new PrintConfigurationModel();
        $model->where('document_type', $docType)->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => $docTypes[$docType]['label'] . ' configuration reset to default.',
        ]);
    }

    public function copy()
    {
        $docTypes  = PrintConfig::getDocumentTypes();
        $sourceDoc = (string) $this->request->getPost('source_document_type');
        $targetDoc = (string) $this->request->getPost('target_document_type');

        if (!isset($docTypes[$sourceDoc]) || !isset($docTypes[$targetDoc]) || $sourceDoc === $targetDoc) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid source or target document type.']);
        }

        $sourceConfig = PrintConfig::getForDocument($sourceDoc);
        $targetDefault = PrintConfig::getDefaultConfig($targetDoc);

        // Preserve target's own document title / number label / party headings unless explicitly overridden
        $merged = PrintConfig::mergeConfig($targetDefault, [
            'paper_size'     => $sourceConfig['paper_size'],
            'orientation'    => $sourceConfig['orientation'],
            'font_family'    => $sourceConfig['font_family'],
            'base_font_size' => $sourceConfig['base_font_size'],
            'fields'         => $sourceConfig['fields'],
        ]);

        $model = new PrintConfigurationModel();
        $model->saveForDocumentType($targetDoc, [
            'paper_size'     => $merged['paper_size'],
            'orientation'    => $merged['orientation'],
            'font_family'    => $merged['font_family'],
            'base_font_size' => $merged['base_font_size'],
            'config_json'    => json_encode(['fields' => $merged['fields']]),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Copied configuration from ' . $docTypes[$sourceDoc]['label'] . ' to ' . $docTypes[$targetDoc]['label'] . '.',
        ]);
    }

    public function preview()
    {
        $docTypes = PrintConfig::getDocumentTypes();
        $docType  = (string) ($this->request->getPost('document_type') ?? $this->request->getGet('doc') ?? 'invoice');
        if (!isset($docTypes[$docType])) {
            $docType = 'invoice';
        }

        $override = $this->request->getMethod() === 'POST' ? $this->extractOverrideFromPost() : null;
        $sample   = PrintConfig::getSampleData($docType);
        $sample['print_config_override'] = $override;

        return view('common/print/standard_document', $sample);
    }

    public function pdfPreview()
    {
        $docTypes = PrintConfig::getDocumentTypes();
        $docType  = (string) ($this->request->getPost('document_type') ?? $this->request->getGet('doc') ?? 'invoice');
        if (!isset($docTypes[$docType])) {
            $docType = 'invoice';
        }

        $override = $this->request->getMethod() === 'POST' ? $this->extractOverrideFromPost() : null;
        $effective = PrintConfig::getForDocument($docType, $override);
        $sample    = PrintConfig::getSampleData($docType);
        $sample['print_config_override'] = $override;
        $sample['is_preview'] = false;

        $html = view('common/print/standard_document', $sample);
        return PdfRenderer::stream(
            $html,
            'Preview_' . $docType,
            $effective['paper_size'] ?? 'A4',
            $effective['orientation'] ?? 'portrait'
        );
    }

    private function extractOverrideFromPost(): array
    {
        $fieldsRaw = $this->request->getPost('fields_json');
        $fields    = is_string($fieldsRaw) ? json_decode($fieldsRaw, true) : $this->request->getPost('fields');

        return [
            'paper_size'     => (string) ($this->request->getPost('paper_size') ?? 'A4'),
            'orientation'    => (string) ($this->request->getPost('orientation') ?? 'portrait'),
            'font_family'    => (string) ($this->request->getPost('font_family') ?? 'Helvetica, Arial, sans-serif'),
            'base_font_size' => (string) ($this->request->getPost('base_font_size') ?? '10.5px'),
            'fields'         => is_array($fields) ? $fields : [],
        ];
    }
}
