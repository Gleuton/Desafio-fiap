<?php

namespace FiapAdmin\Models;

use FiapAdmin\Repositories\StudentRepository;

class Student
{
    private StudentRepository $studentRepository;
    public function __construct()
    {
        $this->studentRepository = new StudentRepository();
    }

    public function all(): array
    {
        return $this->studentRepository->findAll();
    }
}