SET FOREIGN_KEY_CHECKS = 0;


CREATE TABLE roles
(
    id   INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE users
(
    id         INT PRIMARY KEY AUTO_INCREMENT,
    role_id    INT          NOT NULL,
    name       VARCHAR(255) NOT NULL CHECK (LENGTH(name) >= 3),
    birthdate  DATE         NOT NULL,
    cpf        VARCHAR(14)  NOT NULL UNIQUE,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles (id)
);

CREATE TABLE courses
(
    id          INT PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(255) NOT NULL CHECK (LENGTH(name) >= 3),
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE enrollments
(
    id              INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    course_id       INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_enrollment (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (course_id) REFERENCES courses (id)
);

CREATE TABLE tokens
(
    id            INT PRIMARY KEY AUTO_INCREMENT,
    user_id       INT          NOT NULL,
    token         VARCHAR(255) NOT NULL UNIQUE,
    refresh_token VARCHAR(255) NOT NULL UNIQUE,
    expires_at    TIMESTAMP    NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    revoked       BOOLEAN   DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users (id)
);

CREATE INDEX idx_users_cpf ON users (cpf);
CREATE INDEX idx_users_email ON users (email);
CREATE INDEX idx_enrollments_user ON enrollments (user_id);
CREATE INDEX idx_enrollments_course ON enrollments (course_id);
CREATE INDEX idx_token ON tokens (token);
CREATE INDEX idx_refresh_token ON tokens (refresh_token);

INSERT INTO roles (name)
VALUES ('admin'),
       ('student');

-- admin inicial (senha: Admin@123)
INSERT INTO users (role_id, name, birthdate, cpf, email, password)
VALUES (1,
        'Administrador FIAP',
        '1990-01-01',
        '000.000.000-00',
        'admin@fiap.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

SET FOREIGN_KEY_CHECKS = 1;
