<?php

namespace FiapAdmin\Controllers;

use Exception;
use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Course\Course;
use FiapAdmin\Models\Course\CourseService;
use FiapAdmin\Models\Description;
use FiapAdmin\Models\Name;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

readonly class CourseController
{
    public function __construct(private CourseService $course)
    {
    }

    /**
     * @throws ValidationException
     */
    private function createCourse(array $data, ?int $id = null): Course
    {
        $name = new Name($data['name']);
        $description = new Description($data['description']);

        return new Course($id, $name, $description);
    }

    public function index(ServerRequestInterface $request): JsonResponse
    {
        $page = $request->getQueryParams()['page'] ?? 1;
        $limit = $request->getQueryParams()['limit'] ?? 10;

        return new JsonResponse($this->course->index($page, $limit));
    }

    public function create(Request $request): Response
    {
        try {
            $body = $request->getBody();
            $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);

            $course = $this->createCourse($data);
            $result = $this->course->create($course);

            return new JsonResponse($result, 201);
        } catch (ValidationException $e) {
            return new JsonResponse(
                ['error' => [$e->getField() => $e->getMessage()]],
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
        return new JsonResponse($this->course->findById($id));
    }

    public function delete(Request $request, int $id): JsonResponse
    {
        $result = $this->course->delete($id);
        if (!$result['success']) {
            return new JsonResponse($result['errors'], 422);
        }
        return new JsonResponse([], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $body = $request->getBody();
            $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);

            $course = $this->createCourse($data, $id);
            $result = $this->course->update($course);

            return new JsonResponse($result, 201);
        } catch (ValidationException $e) {
            return new JsonResponse(
                ['error' => [$e->getField() => $e->getMessage()]],
                422
            );
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
