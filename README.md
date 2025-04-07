# FIAP Admin

Sistema de administração de cursos, usuários e matrículas — desenvolvido como parte do Desafio Técnico FIAP.

## ✨ Funcionalidades

- Autenticação com JWT (login, logout, verificação de sessão)
- Controle de permissões por tipo de usuário (admin e aluno)
- Gerenciamento de cursos, usuários e matrículas
- API RESTful
- Banco de dados com migrations e seed automatizados
- Código modular e testável

## 🚀 Tecnologias Utilizadas

- PHP 8.4
- MySQL 8.3
- Composer
- Laminas Diactoros + HandlerRunner
- JWT (firebase/php-jwt)
- Docker

## 📦 Instalação

### 1. Clonar o repositório

```bash
git clone https://github.com/Gleuton/Desafio-fiap.git
cd Desafio-fiap
```

### 2. Subir o ambiente com Docker

```bash
docker compose up -d
```

A aplicação ficará disponível em: [http://localhost:8909](http://localhost:8909)

### 3. Acessar o terminal do container

```bash
docker compose exec fiap-app bash
```

### 4. Instalar dependências com o Composer

```bash
composer install
```

### 5. Criar as tabelas e dados iniciais

```bash
composer db:install
```

Este comando:
- Cria todas as tabelas
- Insere os papéis `admin` e `student`
- Cria o usuário administrador:  
  **E-mail:** `admin@fiap.com`  
  **Senha:** `Admin@123`

## 🧪 Rodar os testes (opcional)

```bash
composer test
```

## 📂 Estrutura de Pastas

```
├── app/               # Código da aplicação (controllers, services, etc)
├── public/            # Arquivos públicos (index.php, assets)
├── scripts/           # Scripts auxiliares (reset do banco, seeds, etc)
├── src/               # Core (Connection, Middlewares, Helpers)
├── tests/             # Testes automatizados
├── .docker/           # Arquivos de configuração Docker
├── dump.sql           # Script SQL de estrutura e seed
├── composer.json
└── README.md
```

## 🛠 Sobre o projeto

Desenvolvido por **Gleuton Dutra** para o processo seletivo FIAP.

📧 [gleuton.dutra@gmail.com](mailto:gleuton.dutra@gmail.com)
