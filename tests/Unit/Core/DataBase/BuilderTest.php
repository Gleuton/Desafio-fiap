<?php

namespace Tests\Unit\Core\DataBase;

use Core\DataBase\Builder;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class BuilderTest extends TestCase
{
    private Builder $builder;
    private PDO|MockObject $pdoMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdoMock = $this->createMock(PDO::class);
        $this->builder = new Builder($this->pdoMock);
        $this->builder->setTable('users')
            ->setPrimaryKey('user_id')
            ->setFillable(['name', 'email']);
    }

    public function testFindByIdExecutesCorrectQuery(): void
    {
        $id = '123';
        $expectedSql = "SELECT * FROM users WHERE user_id = '$id'";

        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->method('fetchObject')
            ->willReturn(new stdClass());

        $this->pdoMock->method('query')
            ->with($expectedSql)
            ->willReturn($pdoStatementMock);

        $result = $this->builder->findById($id);

        $this->assertInstanceOf(stdClass::class, $result);
    }

    public function testInsertPreparesAndExecutesCorrectQuery(): void
    {
        $data = [
            'name' => 'John',
            'email' => 'john@example.com',
            'invalid_field' => 'should be ignored'
        ];

        $expectedData = [
            'name' => 'John',
            'email' => 'john@example.com'
        ];

        $columns = implode(', ', array_keys($expectedData));
        $valuesPart = implode(', :', array_keys($expectedData));

        $expectedSql = "INSERT INTO users ($columns) VALUES (:$valuesPart)";

        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->method('execute')
            ->with($expectedData)
            ->willReturn(true);

        $this->pdoMock->method('prepare')
            ->with($expectedSql)
            ->willReturn($pdoStatementMock);

        $result = $this->builder->insert($data);

        $this->assertTrue($result);
    }

    public function testUpdatePreparesAndExecutesCorrectQuery(): void
    {
        $id = '456';
        $data = [
            'name' => 'Jane',
            'invalid_field' => 'ignored'
        ];

        $expectedData = [
            'name' => 'Jane',
            'user_id' => $id
        ];

        $expectedSql = "UPDATE users SET name=:name WHERE user_id=:user_id";

        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->method('execute')
            ->with($expectedData)
            ->willReturn(true);

        $this->pdoMock->method('prepare')
            ->with($expectedSql)
            ->willReturn($pdoStatementMock);

        $result = $this->builder->update($id, $data);

        $this->assertTrue($result);
    }

    public function testDeletePreparesAndExecutesCorrectQuery(): void
    {
        $id = '789';
        $expectedSql = "DELETE FROM users WHERE user_id = ?";

        $pdoStatementMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->method('prepare')
            ->with($expectedSql)
            ->willReturn($pdoStatementMock);

        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->with([$id]);

        $this->builder->delete($id);
    }

    public function testAllWithFilterAppendsFilter(): void
    {
        $filter = 'WHERE name = "John"';
        $expectedSql = "SELECT * FROM users $filter";

        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->method('fetchAll')
            ->willReturn([]);

        $this->pdoMock->method('query')
            ->with($expectedSql)
            ->willReturn($pdoStatementMock);

        $result = $this->builder->all($filter);
        $this->assertSame([], $result);
    }

    public function testAllWithoutFilter(): void
    {
        $expectedSql = "SELECT * FROM users";

        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->method('fetchAll')
            ->willReturn([]);

        $this->pdoMock->method('query')
            ->with($expectedSql)
            ->willReturn($pdoStatementMock);

        $result = $this->builder->all();
        $this->assertSame([], $result);
    }

    public function testFindByAppendsFilters(): void
    {
        $filters = 'WHERE email = "test@example.com"';
        $expectedSql = "SELECT * FROM users $filters";

        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->method('fetchObject')
            ->willReturn(new stdClass());

        $this->pdoMock->method('query')
            ->with($expectedSql)
            ->willReturn($pdoStatementMock);

        $result = $this->builder->findBy($filters);
        $this->assertInstanceOf(stdClass::class, $result);
    }
}
