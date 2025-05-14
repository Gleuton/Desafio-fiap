<?php

namespace Tests\Unit\Models\Course;

use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Course\Course;
use FiapAdmin\Models\Description;
use FiapAdmin\Models\Name;
use PHPUnit\Framework\TestCase;

class CourseTest extends TestCase
{
    public function testCourseCreationWithValidData(): void
    {
        $name = new Name('PHP Course');
        $description = new Description('Learn PHP programming with this comprehensive course');

        $course = new Course(null, $name, $description);

        $this->assertSame($name, $course->name());
        $this->assertSame($description, $course->description());
    }

    public function testCourseNameAccessor(): void
    {
        $name = new Name('PHP Course');
        $description = new Description('Learn PHP programming with this comprehensive course');

        $course = new Course(null, $name, $description);

        $this->assertEquals('PHP Course', $course->name()->value());
    }

    public function testCourseDescriptionAccessor(): void
    {
        $name = new Name('PHP Course');
        $description = new Description('Learn PHP programming with this comprehensive course');

        $course = new Course(null, $name, $description);

        $this->assertEquals('Learn PHP programming with this comprehensive course', $course->description()->value());
    }

    public function testCourseCreationWithInvalidNameThrowsException(): void
    {
        $this->expectException(ValidationException::class);

        $name = 'A';
        $description = 'Learn PHP programming with this comprehensive course';

        new Course(null, new Name($name), new Description($description));
    }

    public function testCourseCreationWithInvalidDescriptionThrowsException(): void
    {
        $this->expectException(ValidationException::class);

        $name = 'PHP Course';
        $description = 'Short';

        new Course(null, new Name($name), new Description($description));
    }
}
