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

-- Admin (senha: Admin@123)
INSERT INTO users (role_id, name, birthdate, cpf, email, password)
VALUES (1, 'Administrador FIAP', '1990-01-01', '071.339.530-30', 'admin@fiap.com',
        '$2y$12$XL4ePWi6PbwCPz/LZFCy4OQ5sgxwWGvTgOWKa7m5G6095AmgBThe.');

INSERT INTO users (role_id, name, birthdate, cpf, email, password)
VALUES (2, 'Ana Souza', '2001-03-15', '403.113.070-86', 'ana.souza@fiap.com', 'senha123'),
       (2, 'Bruno Lima', '2000-08-21', '693.860.260-31', 'bruno.lima@fiap.com', 'senha123'),
       (2, 'Carlos Pereira', '1999-07-10', '481.336.770-41', 'carlos.pereira@fiap.com', 'senha123'),
       (2, 'Daniela Alves', '2002-02-20', '308.164.570-00', 'daniela.alves@fiap.com', 'senha123'),
       (2, 'Eduardo Ramos', '2003-12-05', '361.358.750-51', 'eduardo.ramos@fiap.com', 'senha123'),
       (2, 'Fernanda Dias', '1998-04-30', '351.847.980-64', 'fernanda.dias@fiap.com', 'senha123'),
       (2, 'Gabriel Martins', '2001-06-12', '109.715.080-11', 'gabriel.martins@fiap.com', 'senha123'),
       (2, 'Helena Costa', '2002-11-18', '369.320.700-30', 'helena.costa@fiap.com', 'senha123'),
       (2, 'Igor Rocha', '2000-09-03', '917.632.380-32', 'igor.rocha@fiap.com', 'senha123'),
       (2, 'Juliana Melo', '1999-05-25', '173.975.700-97', 'juliana.melo@fiap.com', 'senha123'),
       (2, 'Kleber Torres', '2001-01-01', '187.673.800-69', 'kleber.torres@fiap.com', 'senha123'),
       (2, 'Larissa Campos', '2002-02-02', '132.576.450-74', 'larissa.campos@fiap.com', 'senha123'),
       (2, 'Marcelo Borges', '2003-03-03', '438.065.500-85', 'marcelo.borges@fiap.com', 'senha123'),
       (2, 'Natália Farias', '2000-04-04', '809.433.470-33', 'natalia.farias@fiap.com', 'senha123'),
       (2, 'Otávio Silva', '2001-05-05', '423.646.030-05', 'otavio.silva@fiap.com', 'senha123');


INSERT INTO courses (name, description)
VALUES ('Engenharia de Software', 'Curso de desenvolvimento de software completo.'),
       ('Ciência da Computação', 'Fundamentos de computação e algoritmos.'),
       ('Sistemas de Informação', 'Tecnologia da informação aplicada.'),
       ('Análise e Desenvolvimento de Sistemas', 'Criação de sistemas e aplicativos.'),
       ('Banco de Dados', 'Modelagem e administração de bancos de dados.'),
       ('Inteligência Artificial', 'Técnicas e aplicações de IA.'),
       ('Redes de Computadores', 'Infraestrutura e segurança de redes.'),
       ('Engenharia de Dados', 'Big Data, ETL e processamento em larga escala.'),
       ('Cibersegurança', 'Proteção e análise de riscos digitais.'),
       ('DevOps', 'Integração contínua e entrega contínua.'),
       ('Computação em Nuvem', 'Serviços em nuvem e arquitetura distribuída.'),
       ('Design de Interfaces', 'UX, UI e design centrado no usuário.');


INSERT INTO enrollments (user_id, course_id)
VALUES (2, 1),
       (3, 2),
       (4, 3);

SET FOREIGN_KEY_CHECKS = 1;
