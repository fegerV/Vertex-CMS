<?php

namespace Vertex\Forms\Contracts;

use Vertex\Forms\Models\Form;

interface FormRepository
{
    public function find(int $id): ?Form;
    public function findBySlug(string $slug): ?Form;
    public function save(array $data, ?Form $form = null): Form;
    public function delete(Form $form): bool;
    public function getActiveForms();
    public function getSubmissionStats(int $formId, ?string $from = null, ?string $to = null): array;
}
