<?php

namespace FiapAdmin\Repositories;

use Core\DataBase\BuilderInterface;
use FiapAdmin\Exceptions\ValidationException;
use FiapAdmin\Models\Student\Student;

class StudentRepository extends Repository
{
    protected string $table = 'users';
    protected array $fillable = [
        'name',
        'birthdate',
        'cpf',
        'email',
        'password',
        'role_id'
    ];

    public function __construct(BuilderInterface $builder, private RoleRepository $roleRepository)
    {
        parent::__construct($builder);
    }

    public function findAll(): array
    {
        $sql = "SELECT 
                u.id,
                u.name,
                u.birthdate,
                u.cpf,
                u.email
            FROM $this->table u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE r.name = 'student'
            ORDER BY u.name ASC";

        return $this->query($sql);
    }

    /**
     * @throws ValidationException
     */
    public function saveStudent(Student $student): array
    {
        $data = $this->mapStudentToArray($student);

        if ($this->cpfExists($data['cpf'])) {
            throw new ValidationException('cpf', 'CPF já cadastrado');
        }

        if ($this->emailExists($data['email'])) {
            throw new ValidationException('email', 'E-mail já cadastrado');
        }

        $newId = $this->insert($data);
        return $this->findById($newId);
    }

    /**
     * @throws ValidationException
     */
    public function updateStudent(Student $student): array
    {
        $id = $student->id();
        $data = $this->mapStudentToArray($student);

        if ($this->cpfExists($data['cpf'], $id)) {
            throw new ValidationException('cpf', 'CPF já cadastrado');
        }

        if ($this->emailExists($data['email'], $id)) {
            throw new ValidationException('email', 'E-mail já cadastrado');
        }

        $this->update($id, $data);
        return $this->findById($id);
    }

    public function cpfExists(string $cpf, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) total
            FROM $this->table u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE u.cpf = ? 
            AND r.name = 'student'";

        $params = [$cpf];

        if ($excludeId) {
            $sql .= " AND u.id <> ?";
            $params[] = $excludeId;
        }

        return $this->query($sql, $params)[0]['total'] > 0;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) total
            FROM $this->table u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE u.email = ? 
            AND r.name = 'student'";

        $params = [$email];

        if ($excludeId) {
            $sql .= " AND u.id <> ?";
            $params[] = $excludeId;
        }

        return $this->query($sql, $params)[0]['total'] > 0;
    }

    public function findOneById(int $id): array
    {
        $fields = ['id', 'name', 'birthdate', 'cpf', 'email'];
        return $this->findById($id, $fields);
    }

    public function hasEnrollments(int $id): bool
    {
        $sql = "SELECT COUNT(*) total
            FROM $this->table u
            INNER JOIN enrollments e ON u.id = e.user_id
            WHERE u.id = ?";

        $params = [$id];
        return $this->query($sql, $params)[0]['total'] > 0;
    }

    public function findAllByName(string $name, ?int $limit): ?array
    {
        $nameSearch = '%' . trim($name) . '%';
        $nameSearch = filter_var($nameSearch);
        $params = ['name' => $nameSearch];
        $sql = "SELECT 
                    u.id, 
                    u.name, 
                    u.birthdate, 
                    u.cpf, 
                    u.email 
                FROM $this->table u
                INNER JOIN roles r ON u.role_id = r.id 
                    WHERE u.name like :name 
                    AND r.name = 'student'";

        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }

        return $this->query($sql, $params);
    }

    /**
     * @param Student $student
     * @return array
     */
    public function mapStudentToArray(Student $student): array
    {
        $roleId = $this->roleRepository->roleId(Student::ROLE);
        return [
            'name' => $student->name(),
            'birthdate' => $student->birthdate(),
            'cpf' => $student->cpf(),
            'email' => $student->email(),
            'password' => $student->password(),
            'role_id' => $roleId,
        ];
    }
}