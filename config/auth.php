<?php
/**
 * Funções de autenticação e controle de sessão
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

/**
 * Verifica se o usuário está logado
 */
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Redireciona para login se não autenticado
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Realiza o login do usuário
 * @param string $email
 * @param string $senha
 * @return bool
 */
function login($email, $senha) {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nome'] = $user['nome'];
        $_SESSION['usuario_email'] = $user['email'];
        $_SESSION['usuario_tipo'] = $user['tipo'];
        return true;
    }
    return false;
}

/**
 * Realiza o logout
 */
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

/**
 * Retorna o nome do usuário logado
 */
function getUsuarioNome() {
    return $_SESSION['usuario_nome'] ?? 'Usuário';
}

/**
 * Retorna o tipo do usuário logado
 */
function getUsuarioTipo() {
    return $_SESSION['usuario_tipo'] ?? 'operador';
}
