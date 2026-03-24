-- Sistema de Gerenciamento de Inscrições de Alunos
-- Database Schema

CREATE DATABASE IF NOT EXISTS sistema_escolar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_escolar;

-- Tabela de Usuários (Login)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'operador') DEFAULT 'operador',
    ativo TINYINT(1) DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de Escolas
CREATE TABLE IF NOT EXISTS escolas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    endereco VARCHAR(255),
    telefone VARCHAR(20),
    email VARCHAR(100),
    diretor VARCHAR(100),
    ativa TINYINT(1) DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de Turnos
CREATE TABLE IF NOT EXISTS turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    horario_inicio TIME,
    horario_fim TIME,
    ativo TINYINT(1) DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de Salas
CREATE TABLE IF NOT EXISTS salas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    capacidade INT DEFAULT 30,
    escola_id INT NOT NULL,
    ativa TINYINT(1) DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (escola_id) REFERENCES escolas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de Professores
CREATE TABLE IF NOT EXISTS professores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefone VARCHAR(20),
    especialidade VARCHAR(100),
    escola_id INT NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (escola_id) REFERENCES escolas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de Alunos (Inscrições)
CREATE TABLE IF NOT EXISTS alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data_nascimento DATE,
    cpf VARCHAR(14),
    email VARCHAR(100),
    telefone VARCHAR(20),
    endereco VARCHAR(255),
    escola_id INT NOT NULL,
    professor_id INT NOT NULL,
    sala_id INT NOT NULL,
    turno_id INT NOT NULL,
    status ENUM('ativo', 'inativo', 'transferido') DEFAULT 'ativo',
    data_inscricao DATE DEFAULT (CURRENT_DATE),
    observacoes TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (escola_id) REFERENCES escolas(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE CASCADE,
    FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE CASCADE,
    FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- DADOS INICIAIS
-- =============================================

-- Usuário admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador', 'admin@escola.com', '$2y$10$OEQZHSWhV1DrI.WQiLemFOsK3tkGUwg87TND1bO5Xf2tRE/VKipzi', 'admin');

-- 5 Escolas
INSERT INTO escolas (nome, endereco, telefone, email, diretor) VALUES
('Escola Municipal Dom Pedro I', 'Rua das Flores, 123 - Centro', '(11) 3456-7890', 'dompedro@edu.br', 'Maria Silva'),
('Escola Estadual Tiradentes', 'Av. Brasil, 456 - Jardim América', '(11) 3456-7891', 'tiradentes@edu.br', 'João Santos'),
('Escola Municipal Monteiro Lobato', 'Rua do Saber, 789 - Vila Nova', '(11) 3456-7892', 'lobato@edu.br', 'Ana Oliveira'),
('Escola Estadual Castro Alves', 'Av. Educação, 321 - Bairro Novo', '(11) 3456-7893', 'castroalves@edu.br', 'Carlos Lima'),
('Escola Municipal Cecília Meireles', 'Rua dos Poetas, 654 - Centro', '(11) 3456-7894', 'cecilia@edu.br', 'Fernanda Costa');

-- Turnos
INSERT INTO turnos (nome, horario_inicio, horario_fim) VALUES
('Manhã', '07:00:00', '12:00:00'),
('Tarde', '13:00:00', '18:00:00'),
('Noite', '19:00:00', '22:00:00');

-- Salas (2 por escola)
INSERT INTO salas (nome, capacidade, escola_id) VALUES
('Sala A1', 35, 1), ('Sala A2', 30, 1),
('Sala B1', 35, 2), ('Sala B2', 30, 2),
('Sala C1', 35, 3), ('Sala C2', 30, 3),
('Sala D1', 35, 4), ('Sala D2', 30, 4),
('Sala E1', 35, 5), ('Sala E2', 30, 5);

-- Professores (2 por escola)
INSERT INTO professores (nome, email, telefone, especialidade, escola_id) VALUES
('Prof. Roberto Almeida', 'roberto@edu.br', '(11) 99999-0001', 'Matemática', 1),
('Prof. Carla Mendes', 'carla@edu.br', '(11) 99999-0002', 'Português', 1),
('Prof. Fernando Costa', 'fernando@edu.br', '(11) 99999-0003', 'Ciências', 2),
('Prof. Juliana Rocha', 'juliana@edu.br', '(11) 99999-0004', 'História', 2),
('Prof. Marcos Souza', 'marcos@edu.br', '(11) 99999-0005', 'Geografia', 3),
('Prof. Patricia Lima', 'patricia@edu.br', '(11) 99999-0006', 'Inglês', 3),
('Prof. André Ferreira', 'andre@edu.br', '(11) 99999-0007', 'Educação Física', 4),
('Prof. Luciana Dias', 'luciana@edu.br', '(11) 99999-0008', 'Artes', 4),
('Prof. Ricardo Nunes', 'ricardo@edu.br', '(11) 99999-0009', 'Matemática', 5),
('Prof. Beatriz Martins', 'beatriz@edu.br', '(11) 99999-0010', 'Português', 5);

-- Alunos de exemplo
INSERT INTO alunos (nome, data_nascimento, cpf, email, telefone, endereco, escola_id, professor_id, sala_id, turno_id, status, data_inscricao) VALUES
('Lucas Pereira', '2010-03-15', '123.456.789-01', 'lucas@email.com', '(11) 98765-0001', 'Rua A, 10', 1, 1, 1, 1, 'ativo', '2026-02-01'),
('Ana Clara Silva', '2011-07-20', '123.456.789-02', 'anaclara@email.com', '(11) 98765-0002', 'Rua B, 20', 1, 2, 2, 1, 'ativo', '2026-02-01'),
('Pedro Henrique', '2010-11-05', '123.456.789-03', 'pedro@email.com', '(11) 98765-0003', 'Rua C, 30', 2, 3, 3, 2, 'ativo', '2026-02-15'),
('Mariana Costa', '2012-01-22', '123.456.789-04', 'mariana@email.com', '(11) 98765-0004', 'Rua D, 40', 2, 4, 4, 2, 'ativo', '2026-02-15'),
('Gabriel Santos', '2011-05-10', '123.456.789-05', 'gabriel@email.com', '(11) 98765-0005', 'Rua E, 50', 3, 5, 5, 1, 'ativo', '2026-03-01'),
('Isabela Oliveira', '2010-09-30', '123.456.789-06', 'isabela@email.com', '(11) 98765-0006', 'Rua F, 60', 3, 6, 6, 1, 'ativo', '2026-03-01'),
('Rafael Lima', '2011-12-18', '123.456.789-07', 'rafael@email.com', '(11) 98765-0007', 'Rua G, 70', 4, 7, 7, 3, 'ativo', '2026-03-01'),
('Camila Ferreira', '2012-04-25', '123.456.789-08', 'camila@email.com', '(11) 98765-0008', 'Rua H, 80', 4, 8, 8, 3, 'ativo', '2026-03-01'),
('Thiago Rodrigues', '2010-08-12', '123.456.789-09', 'thiago@email.com', '(11) 98765-0009', 'Rua I, 90', 5, 9, 9, 2, 'ativo', '2026-03-10'),
('Laura Martins', '2011-02-28', '123.456.789-10', 'laura@email.com', '(11) 98765-0010', 'Rua J, 100', 5, 10, 10, 2, 'ativo', '2026-03-10');
