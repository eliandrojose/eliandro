<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$conn = getConnection();

// Estatísticas
$totalAlunos = $conn->query("SELECT COUNT(*) FROM alunos")->fetchColumn();
$totalEscolas = $conn->query("SELECT COUNT(*) FROM escolas WHERE ativa = 1")->fetchColumn();
$totalProfessores = $conn->query("SELECT COUNT(*) FROM professores WHERE ativo = 1")->fetchColumn();
$totalSalas = $conn->query("SELECT COUNT(*) FROM salas WHERE ativa = 1")->fetchColumn();
$totalTurnos = $conn->query("SELECT COUNT(*) FROM turnos WHERE ativo = 1")->fetchColumn();
$alunosAtivos = $conn->query("SELECT COUNT(*) FROM alunos WHERE status = 'ativo'")->fetchColumn();

// Alunos por escola (para gráfico)
$alunosPorEscola = $conn->query("
    SELECT e.nome, COUNT(a.id) as total 
    FROM escolas e 
    LEFT JOIN alunos a ON e.id = a.escola_id 
    WHERE e.ativa = 1 
    GROUP BY e.id, e.nome 
    ORDER BY total DESC
")->fetchAll();

// Alunos por turno (para gráfico)
$alunosPorTurno = $conn->query("
    SELECT t.nome, COUNT(a.id) as total 
    FROM turnos t 
    LEFT JOIN alunos a ON t.id = a.turno_id 
    WHERE t.ativo = 1 
    GROUP BY t.id, t.nome
")->fetchAll();

// Últimas inscrições
$ultimasInscricoes = $conn->query("
    SELECT a.nome, a.data_inscricao, e.nome as escola, t.nome as turno 
    FROM alunos a 
    JOIN escolas e ON a.escola_id = e.id 
    JOIN turnos t ON a.turno_id = t.id 
    ORDER BY a.criado_em DESC 
    LIMIT 5
")->fetchAll();
?>

<!-- Cards de Estatísticas -->
<div class="row g-4 mb-4 fade-in">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-gradient-primary text-white me-3">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="stat-number"><?php echo $totalAlunos; ?></div>
                    <div class="stat-label">Total Alunos</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-gradient-success text-white me-3">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <div class="stat-number"><?php echo $totalEscolas; ?></div>
                    <div class="stat-label">Escolas</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-gradient-warning text-white me-3">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div>
                    <div class="stat-number"><?php echo $totalProfessores; ?></div>
                    <div class="stat-label">Professores</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-gradient-info text-white me-3">
                    <i class="bi bi-door-open-fill"></i>
                </div>
                <div>
                    <div class="stat-number"><?php echo $totalSalas; ?></div>
                    <div class="stat-label">Salas</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row g-4 mb-4 fade-in">
    <div class="col-lg-7">
        <div class="chart-container">
            <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart"></i> Alunos por Escola</h6>
            <canvas id="chartEscolas" height="250"></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="chart-container">
            <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart"></i> Alunos por Turno</h6>
            <canvas id="chartTurnos" height="250"></canvas>
        </div>
    </div>
</div>

<!-- Últimas Inscrições -->
<div class="row fade-in">
    <div class="col-12">
        <div class="table-container">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history"></i> Últimas Inscrições</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Escola</th>
                            <th>Turno</th>
                            <th>Data Inscrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimasInscricoes as $insc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($insc['nome']); ?></td>
                            <td><?php echo htmlspecialchars($insc['escola']); ?></td>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars($insc['turno']); ?></span></td>
                            <td><?php echo date('d/m/Y', strtotime($insc['data_inscricao'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($ultimasInscricoes)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Nenhuma inscrição encontrada.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico Alunos por Escola
    const ctxEscolas = document.getElementById('chartEscolas').getContext('2d');
    new Chart(ctxEscolas, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($alunosPorEscola, 'nome')); ?>,
            datasets: [{
                label: 'Alunos',
                data: <?php echo json_encode(array_map('intval', array_column($alunosPorEscola, 'total'))); ?>,
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(17, 153, 142, 0.8)',
                    'rgba(240, 147, 251, 0.8)',
                    'rgba(79, 172, 254, 0.8)',
                    'rgba(250, 112, 154, 0.8)'
                ],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // Gráfico Alunos por Turno
    const ctxTurnos = document.getElementById('chartTurnos').getContext('2d');
    new Chart(ctxTurnos, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($alunosPorTurno, 'nome')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_map('intval', array_column($alunosPorTurno, 'total'))); ?>,
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(17, 153, 142, 0.8)',
                    'rgba(240, 147, 251, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
