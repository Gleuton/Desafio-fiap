<?php

namespace FiapAdmin\Controllers;

use FiapAdmin\Models\Course\Course;
use JsonException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class CourseController
{
    private Course $course;

    public function __construct()
    {
        $this->course = new Course();
    }

    public function index(ServerRequestInterface $request): JsonResponse
    {
        $page = $request->getQueryParams()['page'] ?? 1;
        $limit = $request->getQueryParams()['limit'] ?? 10;

        return new JsonResponse($this->course->index($page, $limit));
    }

    /**
     * @throws JsonException
     */
    public function create(Request $request): Response
    {
        $body = $request->getBody();
        $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $result = $this->course->create($data);

        if (!$result['success']) {
            return new JsonResponse($result['errors'], 422);
        }

        return new JsonResponse($result, 201);
    }

    public function show(string $id): Response
    {
        return new JsonResponse($this->course->findById($id));
    }

    public function delete(int $id): JsonResponse
    {
        $result = $this->course->delete($id);
        if (!$result['success']) {
            return new JsonResponse($result['errors'], 422);
        }
        return new JsonResponse([], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $body = $request->getBody();
        $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $result = $this->course->update($id, $data);

        if (!$result['success']) {
            return new JsonResponse($result['errors'], 422);
        }

        return new JsonResponse($result, 201);
    }
}