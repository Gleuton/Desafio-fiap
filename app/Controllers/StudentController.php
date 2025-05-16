<?php

namespace FiapAdmin\Controllers;

use Exception;
use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Birthdate;
use FiapAdmin\Models\Cpf;
use FiapAdmin\Models\Email;
use FiapAdmin\Models\Name;
use FiapAdmin\Models\Password;
use FiapAdmin\Models\Student\Student;
use FiapAdmin\Models\Student\StudentService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

readonly class StudentController
{
    public function __construct(private StudentService $student)
    {
    }

    public function index(Request $request): Response
    {
        $studentName = $request->getQueryParams()['name'] ?? null;
        $limit = $request->getQueryParams()['limit'] ?? null;
        return new JsonResponse($this->student->all($studentName, $limit));
    }

    public function create(Request $request): Response
    {
        try {
            $body = $request->getBody();
            $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);

            $student = $this->student($data);
            $result = $this->student->create($student);
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

        return new JsonResponse($result, 201);
    }

    public function show(Request $request, int $id): Response
    {
        return new JsonResponse($this->student->findById($id));
    }

    public function update(Request $request, ?int $id): Response
    {
        try {
            $body = $request->getBody();
            $data = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);
            $data['id'] = $id;

            $student = $this->student($data);
            $result = $this->student->update($student);
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

    /**
     * @throws ValidationException
     */
    private function student(array $data): Student
    {
        $name = new Name($data['name']);
        $birthdate = new Birthdate($data['birthdate']);
        $cpf = new Cpf($data['cpf']);
        $email = new Email($data['email']);
        $password = empty($data['password']) ? null : new Password($data['password']);
        $id = empty($data['id']) ? null : $data['id'];

        return new Student(
            $id,
            $name,
            $cpf,
            $email,
            $birthdate,
            $password,
        );
    }
}
