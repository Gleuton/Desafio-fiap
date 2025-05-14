<?php

namespace FiapAdmin\Controllers;

use Exception;
use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Enrollment\Enrollment;
use FiapAdmin\Models\Enrollment\EnrollmentService;
use JsonException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

readonly class EnrollmentsController
{
    public function __construct(private EnrollmentService $enrollmentService)
    {
    }

    /**
     * @throws ValidationException
     */
    private function createEnrollment(array $data, ?int $id = null): Enrollment
    {
        $userId = $data['user_id'] ?? $data['student_id'] ?? 0;
        $courseId = $data['course_id'] ?? 0;

        return new Enrollment($id, $userId, $courseId);
    }

    public function index(Request $request): JsonResponse
    {
        $page = $request->getQueryParams()['page'] ?? 1;
        $limit = $request->getQueryParams()['limit'] ?? 10;

        return new JsonResponse($this->enrollmentService->index($page, $limit));
    }

    /**
     * @throws JsonException
     */
    public function create(Request $request): Response
    {
        try {
            $body = $request->getBody();
            $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);

            $enrollment = $this->createEnrollment($data);
            $result = $this->enrollmentService->create($enrollment);

            return new JsonResponse($result, 201);
        } catch (ValidationException $e) {
            return new JsonResponse(
                ['errors' => [$e->getField() => $e->getMessage()]],
                422
            );
        } catch (Exception $e) {
            return new JsonResponse(
                ['Error' => $e->getMessage()],
                500
            );
        }
    }

    public function show(Request $request, int $id): Response
    {
        return new JsonResponse($this->enrollmentService->findById($id));
    }

    public function listByCurses(Request $request, int $courseId): JsonResponse
    {
        return new JsonResponse($this->enrollmentService->findByCourseId($courseId));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $body = $request->getBody();
            $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);

            $enrollment = $this->createEnrollment($data, $id);
            $result = $this->enrollmentService->update($enrollment);

            return new JsonResponse($result, 201);
        } catch (ValidationException $e) {
            return new JsonResponse(
                ['errors' => [$e->getField() => $e->getMessage()]],
                422
            );
        } catch (Exception $e) {
            return new JsonResponse(
                ['Error' => $e->getMessage()],
                500
            );
        }
    }

    public function delete(Request $request, int $id): JsonResponse
    {
        $result = $this->enrollmentService->delete($id);
        if (!$result['success']) {
            return new JsonResponse($result['errors'], 422);
        }
        return new JsonResponse([], 201);
    }
}
