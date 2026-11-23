<?php

namespace App\Models;

use CodeIgniter\Model;

class PrintConfigurationModel extends Model
{
    protected $table            = 'print_configurations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'document_type',
        'paper_size',
        'orientation',
        'font_family',
        'base_font_size',
        'config_json',
        'updated_by',
    ];

    public function getByDocumentType(string $documentType): ?array
    {
        return $this->where('document_type', $documentType)->first();
    }

    public function saveForDocumentType(string $documentType, array $data): bool
    {
        $existing = $this->getByDocumentType($documentType);
        $payload = [
            'document_type'  => $documentType,
            'paper_size'     => $data['paper_size'] ?? 'A4',
            'orientation'    => in_array(($data['orientation'] ?? 'portrait'), ['portrait', 'landscape'], true) ? $data['orientation'] : 'portrait',
            'font_family'    => $data['font_family'] ?? 'Helvetica, Arial, sans-serif',
            'base_font_size' => $data['base_font_size'] ?? '10.5px',
            'config_json'    => is_string($data['config_json']) ? $data['config_json'] : json_encode($data['config_json']),
            'updated_by'     => session()->get('user_id') ?: null,
        ];

        if ($existing) {
            return (bool) $this->update($existing['id'], $payload);
        }

        return (bool) $this->insert($payload);
    }
}
