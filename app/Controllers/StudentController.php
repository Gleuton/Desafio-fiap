<?php

namespace FiapAdmin\Controllers;

use FiapAdmin\Models\Student\Student;
use JsonException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class StudentController
{
    private Student $student;

    public function __construct()
    {
        $this->student = new Student();
    }

    public function index(Request $request): Response
    {
        $studentName = $request->getQueryParams()['name'] ?? null;
        return new JsonResponse($this->student->all($studentName));
    }

    /**
     * @throws JsonException
     */
    public function create(Request $request): Response
    {
        $body = $request->getBody();
        $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $result = $this->student->create($data);

        if (!$result['success']) {
            return new JsonResponse($result['errors'], 422);
        }

        return new JsonResponse($result, 201);
    }

    public function show(string $id): Response
    {
        return new JsonResponse($this->student->findById($id));
    }

    /**
     * @throws JsonException
     */
    public function update(Request $request, ?int $id): Response
    {
        $body = $request->getBody();
        $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $result = $this->student->update($id, $data);

        if (!$result['success']) {
            return new JsonResponse($result['errors'], 422);
        }

        return new JsonResponse($result, 201);
    }

    public function delete(int $id): Response
    {
        $result = $this->student->delete($id);
        if (!$result['success']) {
            return new JsonResponse($result['errors'], 422);
        }
        return new JsonResponse([], 201);
    }
}