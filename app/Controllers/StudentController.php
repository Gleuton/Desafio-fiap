<?php

namespace FiapAdmin\Controllers;

use Exception;
use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Student\StudentOperations;
use JsonException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

readonly class StudentController
{
    public function __construct(private StudentOperations $student)
    {
    }

    public function index(Request $request): Response
    {
        $studentName = $request->getQueryParams()['name'] ?? null;
        $limit = $request->getQueryParams()['limit'] ?? null;
        return new JsonResponse($this->student->all($studentName, $limit));
    }

    /**
     * @throws JsonException
     */
    public function create(Request $request): Response
    {
        $body = $request->getBody();
        $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);

        try {
            $result = $this->student->create($data);
        } catch (ValidationException $e) {
            $jsonException = json_decode($e->getMessage(), true, 512, JSON_THROW_ON_ERROR);
            return new JsonResponse(
                ['error' => $jsonException],
                422
            );
        } catch (Exception $e) {
            return new JsonResponse(
                ['Error' => $e->getMessage()],
                500
            );
        }

        return new JsonResponse($result, 201);
    }

    public function show(Request $request, int $id): Response
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

    public function delete(Request $request, int $id): Response
    {
        $result = $this->student->delete($id);
        if (!$result['success']) {
            return new JsonResponse($result['errors'], 422);
        }
        return new JsonResponse([], 201);
    }
}