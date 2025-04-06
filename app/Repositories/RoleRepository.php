<?php

namespace FiapAdmin\Repositories;

class RoleRepository extends Repository
{
    protected string $table = 'roles';

    public function roleId(string $role): int
    {
        return $this->findBy('WHERE name=:name', ['name'=>$role])['id'];
    }
}