<?php

namespace FiapAdmin\Controllers;

use FiapAdmin\Models\Enrollment\Enrollments;
use JsonException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

readonly class EnrollmentsController
{

    public function __construct(private Enrollments $enrollments)
    {
    }

    /**
     * @throws JsonException
     */
    public function create(Request $request): Response
    {
        $body = $request->getBody();
        $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $result = $this->enrollments->create($data);

        if (!$result['success']) {
            return new JsonResponse($result['errors'], 422);
        }

        return new JsonResponse($result, 201);
    }

    public function listByCurses(Request $request, int $courseId): JsonResponse
    {
        return new JsonResponse($this->enrollments->listByCurses($courseId));
    }

    public function delete(Request $request, int $id): JsonResponse
    {
        $this->enrollments->delete($id);
        return new JsonResponse([], 201);
    }
}