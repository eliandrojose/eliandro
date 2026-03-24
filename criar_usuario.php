<?php
require_once __DIR__ . '/config/database.php';

$sucesso = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';
    $tipo = $_POST['tipo'] ?? 'admin';

    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar)) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter no mínimo 6 caracteres.';
    } elseif ($senha !== $confirmar) {
        $erro = 'As senhas não conferem.';
    } else {
        try {
            $conn = getConnection();

            // Verificar se email já existe
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $erro = 'Este email já está cadastrado.';
            } else {
                $hash = password_hash($senha, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo, ativo) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$nome, $email, $hash, $tipo]);
                $sucesso = "Usuário '{$nome}' criado com sucesso como {$tipo}!";
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao criar usuário: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Usuário - Sistema Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card fade-in" style="max-width: 500px;">
            <div class="text-center mb-4">
                <i class="bi bi-person-plus-fill" style="font-size: 3rem; color: #667eea;"></i>
                <h3 class="mt-2">Criar Usuário</h3>
                <p class="text-muted">Cadastrar novo administrador no sistema</p>
            </div>

            <?php if ($sucesso): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($sucesso); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="nome" class="form-label fw-semibold">
                        <i class="bi bi-person"></i> Nome Completo *
                    </label>
                    <input type="text" class="form-control" id="nome" name="nome"
                           placeholder="Nome do usuário" required
                           value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">
                        <i class="bi bi-envelope"></i> Email *
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="email@exemplo.com" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label fw-semibold">
                        <i class="bi bi-lock"></i> Senha *
                    </label>
                    <input type="password" class="form-control" id="senha" name="senha"
                           placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>

                <div class="mb-3">
                    <label for="confirmar_senha" class="form-label fw-semibold">
                        <i class="bi bi-lock-fill"></i> Confirmar Senha *
                    </label>
                    <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha"
                           placeholder="Repita a senha" required minlength="6">
                </div>

                <div class="mb-4">
                    <label for="tipo" class="form-label fw-semibold">
                        <i class="bi bi-shield-check"></i> Tipo de Usuário
                    </label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="admin" <?php echo ($_POST['tipo'] ?? 'admin') === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                        <option value="operador" <?php echo ($_POST['tipo'] ?? '') === 'operador' ? 'selected' : ''; ?>>Operador</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-person-plus"></i> Criar Usuário
                </button>

                <a href="login.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Voltar ao Login
                </a>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
