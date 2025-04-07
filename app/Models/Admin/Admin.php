<?php

namespace FiapAdmin\Models\Admin;

use FiapAdmin\Repositories\AdminRepository;

readonly class Admin
{
    private AdminRepository $adminRepository;

    public function __construct()
    {
        $this->adminRepository = new AdminRepository();
    }

    public function authenticate(string $email, string $password): ?array
    {
        $admin = $this->adminRepository->findAdminByEmail($email);

        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }

        return null;
    }

}