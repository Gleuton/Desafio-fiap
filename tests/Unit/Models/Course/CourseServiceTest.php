<?php

namespace Tests\Unit\Models\Course;

use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Course\Course;
use FiapAdmin\Models\Course\CourseService;
use FiapAdmin\Models\Description;
use FiapAdmin\Models\Name;
use FiapAdmin\Repositories\CourseRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CourseServiceTest extends TestCase
{
    private MockObject|CourseRepository $repositoryMock;
    private CourseService $service;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(CourseRepository::class);
        $this->service = new CourseService($this->repositoryMock);
    }

    public function testIndexReturnsPaginatedData(): void
    {
        $page = 1;
        $limit = 10;
        $total = 20;
        $courses = [['id' => 1, 'name' => 'PHP Course']];

        $this->repositoryMock->method('countTotal')->willReturn($total);
        $this->repositoryMock->method('findPaginated')->with($page, $limit)->willReturn($courses);

        $result = $this->service->index($page, $limit);

        $this->assertEquals($courses, $result['courses']);
        $this->assertEquals(2, $result['totalPages']);
        $this->assertEquals($page, $result['currentPage']);
    }

    public function testCreateReturnsSuccessWhenValid(): void
    {
        $name = new Name('PHP Course');
        $description = new Description('Learn PHP programming');
        $course = new Course(null, $name, $description);

        $this->repositoryMock->method('saveCourse')
            ->with($this->callback(function ($course) {
                return $course instanceof Course 
                    && $course->name()->value() === 'PHP Course'
                    && $course->description()->value() === 'Learn PHP programming';
            }))
            ->willReturn(['id' => 1, 'name' => 'PHP Course']);

        $result = $this->service->create($course);

        $this->assertTrue($result['success']);
    }

    public function testCreateReturnsErrorsWhenInvalid(): void
    {
        $course = $this->createMock(Course::class);

        $this->repositoryMock->method('saveCourse')
            ->with($course)
            ->will($this->throwException(new ValidationException('name', 'Name is required')));

        $result = $this->service->create($course);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('name', $result['errors']);
    }

    public function testUpdateReturnsSuccessWhenValid(): void
    {
        $id = 1;
        $name = new Name('Updated Course');
        $description = new Description('Updated description');
        $course = new Course($id, $name, $description);

        $courseData = [
            'name' => 'Updated Course',
            'description' => 'Updated description'
        ];

        $this->repositoryMock->method('updateCourse')
            ->with($this->callback(function ($course) {
                return $course instanceof Course 
                    && $course->name()->value() === 'Updated Course'
                    && $course->description()->value() === 'Updated description';
            }))
            ->willReturn(true);

        $result = $this->service->update($course);

        $this->assertTrue($result['success']);
        $this->assertEquals($courseData, $result['data']);
    }

    public function testUpdateReturnsErrorsWhenInvalid(): void
    {
        $id = 1;
        $course = $this->createMock(Course::class);

        $course->method('id')->willReturn($id);

        $this->repositoryMock->method('updateCourse')
            ->with($course)
            ->will($this->throwException(new ValidationException('name', 'Name is required')));

        $result = $this->service->update($course);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('name', $result['errors']);
    }

    public function testDeleteReturnsSuccessWhenNoEnrollments(): void
    {
        $id = 1;

        $this->repositoryMock->method('hasEnrollments')->with($id)->willReturn(false);
        $this->repositoryMock->method('delete')->with($id)->willReturn(true);

        $result = $this->service->delete($id);

        $this->assertTrue($result['success']);
        $this->assertEquals($id, $result['id']);
    }

    public function testDeleteReturnsErrorWhenHasEnrollments(): void
    {
        $id = 1;

        $this->repositoryMock->method('hasEnrollments')->with($id)->willReturn(true);

        $result = $this->service->delete($id);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('enrollment', $result['errors']);
    }

    public function testFindByIdReturnsRepositoryResult(): void
    {
        $id = 1;
        $course = ['id' => 1, 'name' => 'PHP Course'];

        $this->repositoryMock->method('findOneById')->with($id)->willReturn($course);

        $result = $this->service->findById($id);

        $this->assertEquals($course, $result);
    }
}
