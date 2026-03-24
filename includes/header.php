<?php
require_once __DIR__ . '/../config/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Sistema Escolar'; ?> - Sistema de Gerenciamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar bg-dark text-white" id="sidebar">
            <div class="sidebar-header p-3 text-center">
                <h5><i class="bi bi-mortarboard-fill"></i> Sistema Escolar</h5>
            </div>
            <nav class="nav flex-column p-2">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) === 'escolas.php' ? 'active' : ''; ?>" href="escolas.php">
                    <i class="bi bi-building"></i> Escolas
                </a>
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) === 'professores.php' ? 'active' : ''; ?>" href="professores.php">
                    <i class="bi bi-person-badge"></i> Professores
                </a>
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) === 'salas.php' ? 'active' : ''; ?>" href="salas.php">
                    <i class="bi bi-door-open"></i> Salas
                </a>
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) === 'turnos.php' ? 'active' : ''; ?>" href="turnos.php">
                    <i class="bi bi-clock"></i> Turnos
                </a>
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) === 'alunos.php' ? 'active' : ''; ?>" href="alunos.php">
                    <i class="bi bi-people"></i> Alunos
                </a>
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) === 'relatorios.php' ? 'active' : ''; ?>" href="relatorios.php">
                    <i class="bi bi-file-earmark-bar-graph"></i> Relatórios
                </a>
                <hr class="text-secondary">
                <a class="nav-link text-white" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </a>
            </nav>
        </div>

        <!-- Page Content -->
        <div class="flex-grow-1" id="page-content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-3">
                <button class="btn btn-outline-dark me-3" id="menu-toggle">
                    <i class="bi bi-list"></i>
                </button>
                <span class="navbar-text fw-bold"><?php echo $pageTitle ?? 'Dashboard'; ?></span>
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 text-muted">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(getUsuarioNome()); ?>
                    </span>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="container-fluid p-4">
