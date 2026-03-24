# Sistema de Gerenciamento de Inscrições de Alunos

Sistema completo para gerenciar inscrições de alunos em múltiplas escolas, vinculando-os a escola, professor, sala e turno.

## Funcionalidades

- **Login** com autenticação segura (senha com bcrypt)
- **Dashboard** com estatísticas e gráficos interativos (Chart.js)
- **CRUD de Escolas** - Cadastrar, editar e excluir escolas
- **CRUD de Professores** - Gerenciar professores vinculados a escolas
- **CRUD de Salas** - Gerenciar salas vinculadas a escolas com controle de capacidade
- **CRUD de Turnos** - Gerenciar turnos (Manhã, Tarde, Noite)
- **CRUD de Alunos** - Inscrever alunos vinculando a escola, professor, sala e turno
- **Relatórios** - Filtros avançados, impressão e exportação CSV
- **Design Responsivo** - Funciona em desktop e mobile (Bootstrap 5)

## Tecnologias

- PHP 8+
- MySQL 5.7+
- HTML5 / CSS3
- JavaScript (ES6+)
- Bootstrap 5
- Chart.js
- Bootstrap Icons

## Requisitos

- PHP 8.0 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx) ou PHP built-in server

## Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/eliandrojose/eliandro.git
cd eliandro
```

### 2. Configure o banco de dados

Crie o banco de dados e importe o schema:

```bash
mysql -u root -p < database.sql
```

### 3. Configure a conexão

Edite o arquivo `config/database.php` com suas credenciais do MySQL:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_escolar');
define('DB_USER', 'root');
define('DB_PASS', 'sua_senha');
```

### 4. Inicie o servidor

```bash
php -S localhost:8000
```

### 5. Acesse o sistema

Abra o navegador em `http://localhost:8000`

**Credenciais padrão:**
- Email: `admin@escola.com`
- Senha: `admin123`

## Estrutura do Projeto

```
├── ajax/                  # Endpoints AJAX
│   ├── get_professores.php
│   └── get_salas.php
├── assets/
│   ├── css/
│   │   └── style.css      # Estilos customizados
│   └── js/
│       └── app.js          # JavaScript principal
├── config/
│   ├── auth.php           # Autenticação e sessões
│   └── database.php       # Conexão com banco de dados
├── includes/
│   ├── header.php         # Cabeçalho e sidebar
│   └── footer.php         # Rodapé
├── alunos.php             # CRUD de alunos
├── dashboard.php          # Dashboard com estatísticas
├── database.sql           # Schema do banco de dados
├── escolas.php            # CRUD de escolas
├── index.php              # Redirecionamento
├── login.php              # Tela de login
├── logout.php             # Logout
├── professores.php        # CRUD de professores
├── relatorios.php         # Relatórios
├── salas.php              # CRUD de salas
└── turnos.php             # CRUD de turnos
```

## Dados Iniciais

O sistema vem pré-configurado com:
- 1 usuário administrador
- 5 escolas
- 10 professores (2 por escola)
- 10 salas (2 por escola)
- 3 turnos (Manhã, Tarde, Noite)
- 10 alunos de exemplo

## Capturas de Tela

O sistema possui:
- Tela de login moderna com gradiente
- Dashboard com cards de estatísticas e gráficos
- Tabelas responsivas com ações de editar/excluir
- Modais para formulários de cadastro
- Relatórios com filtros avançados e exportação
