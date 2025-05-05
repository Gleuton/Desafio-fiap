<?php

namespace FiapAdmin\Models\Course;

use FiapAdmin\Repositories\CourseRepository;

readonly class CourseValidator
{
    public function __construct(private CourseRepository $courseRepository)
    {
    }

    public function validateCreate(array $data): array
    {
        $errors = [];

        $required = ['name', 'description'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst($field) . " é obrigatório";
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        return array_merge($errors, $this->validateCommon($data));
    }

    public function validateUpdate(?int $id, array $data): array
    {
        $errors = [];

        if (is_null($id)) {
            $errors['id'] = "ID é obrigatório";
        }

        $required = ['name', 'description'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst($field) . " é obrigatório";
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        return array_merge($errors, $this->validateCommon($data, $id));
    }

    private function validateCommon(array $data, $excludeId = null): array
    {
        $errors = [];

        if (strlen($data['name']) < 3) {
            $errors['name'] = "Nome deve ter pelo menos 3 caracteres";
        }

        if ($this->courseRepository->nameExists($data['name'], $excludeId)) {
            $errors['name'] = "Já existe uma turma com este nome";
        }

        return $errors;
    }
}
