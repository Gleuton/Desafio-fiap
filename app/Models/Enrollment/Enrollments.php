<?php

namespace FiapAdmin\Models\Enrollment;

use FiapAdmin\Repositories\EnrollmentRepository;

readonly class Enrollments
{
    public function __construct(private EnrollmentRepository $repository, private EnrollmentValidator $validator)
    {
    }

    public function create(array $data): array
    {
        $validation = $this->validator->validateCreate($data);
        if (!empty($validation)) {
            return ['success' => false, 'errors' => $validation];
        }

        return [
            'success' => $this->repository->saveEnrollment($data),
            'data' => $data
        ];
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }

    public function listByCurses(int $courseId): array
    {
        return $this->repository->listByCurses($courseId);
    }
}