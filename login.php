<?php
require_once __DIR__ . '/config/auth.php';

// Se já está logado, redirecionar
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } elseif (login($email, $senha)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $erro = 'Email ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gerenciamento Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card fade-in">
            <div class="text-center mb-4">
                <i class="bi bi-mortarboard-fill" style="font-size: 3rem; color: #667eea;"></i>
                <h3 class="mt-2">Sistema Escolar</h3>
                <p class="text-muted">Gerenciamento de Inscrições</p>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">
                        <i class="bi bi-envelope"></i> Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="seu@email.com" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="mb-4">
                    <label for="senha" class="form-label fw-semibold">
                        <i class="bi bi-lock"></i> Senha
                    </label>
                    <input type="password" class="form-control" id="senha" name="senha"
                           placeholder="Sua senha" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                </button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">
                    Acesso padrão: <strong>admin@escola.com</strong> / <strong>admin123</strong>
                </small>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
