<?php

namespace FiapAdmin\Controllers;

use FiapAdmin\Models\Student;

class StudentController
{
    private Student $student;
    public function __construct()
    {
        $this->student = new Student();
    }
    public function index():array
    {
        return $this->student->all();
    }
}