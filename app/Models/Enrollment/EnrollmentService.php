<?php

namespace FiapAdmin\Models\Enrollment;

use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Repositories\EnrollmentRepository;

readonly class EnrollmentService
{
    public function __construct(
        private EnrollmentRepository $repository
    ) {
    }

    /**
     * @throws ValidationException
     */
    private function checkIfAlreadyEnrolled(int $studentId, int $courseId): void
    {
        if ($this->repository->isEnrolled($studentId, $courseId)) {
            throw new ValidationException('enrollment', 'Este aluno já está matriculado nesta turma!');
        }
    }

    public function index(int $page = 1, int $limit = 10): array
    {
        $total = $this->repository->countTotal();
        $totalPages = ceil($total / $limit);

        $enrollments = $this->repository->findPaginated($page, $limit);

        return [
            'enrollments' => $enrollments,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ];
    }

    /**
     * @throws ValidationException
     */
    public function create(Enrollment $enrollment): array
    {
        $this->checkIfAlreadyEnrolled($enrollment->userId(), $enrollment->courseId());

        $data = $enrollment->toArray();
        $result = $this->repository->saveEnrollment($data);

        return [
            'success' => true,
            'data' => $result
        ];
    }

    public function findById(int $id): array
    {
        return $this->repository->findOneById($id);
    }

    public function findByCourseId(int $courseId): array
    {
        return $this->repository->listByCurses($courseId);
    }

    /**
     * @throws ValidationException
     */
    public function update(Enrollment $enrollment): array
    {
        $this->checkIfAlreadyEnrolled($enrollment->userId(), $enrollment->courseId());

        $data = $enrollment->toArray();
        $result = $this->repository->updateEnrollment($enrollment->id(), $data);

        return [
            'success' => $result,
            'data' => [
                'user_id' => $enrollment->userId(),
                'course_id' => $enrollment->courseId()
            ]
        ];
    }

    public function delete(int $id): array
    {
        return [
            'success' => $this->repository->delete($id),
            'id' => $id
        ];
    }

}
