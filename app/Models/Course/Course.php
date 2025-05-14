<?php

namespace FiapAdmin\Models\Course;

use FiapAdmin\Models\Description;
use FiapAdmin\Models\Name;

readonly class Course
{
    public function __construct(private ?int $id,private Name $name, private Description $description)
    {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function description(): Description
    {
        return $this->description;
    }
}