<?php
$pageTitle = 'Relatórios';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$conn = getConnection();

// Filtros
$filtroEscola = intval($_GET['escola'] ?? 0);
$filtroProfessor = intval($_GET['professor'] ?? 0);
$filtroSala = intval($_GET['sala'] ?? 0);
$filtroTurno = intval($_GET['turno'] ?? 0);
$filtroStatus = $_GET['status'] ?? '';
$filtroDataDe = $_GET['data_de'] ?? '';
$filtroDataAte = $_GET['data_ate'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($filtroEscola > 0) {
    $where .= " AND a.escola_id = ?";
    $params[] = $filtroEscola;
}
if ($filtroProfessor > 0) {
    $where .= " AND a.professor_id = ?";
    $params[] = $filtroProfessor;
}
if ($filtroSala > 0) {
    $where .= " AND a.sala_id = ?";
    $params[] = $filtroSala;
}
if ($filtroTurno > 0) {
    $where .= " AND a.turno_id = ?";
    $params[] = $filtroTurno;
}
if ($filtroStatus) {
    $where .= " AND a.status = ?";
    $params[] = $filtroStatus;
}
if ($filtroDataDe) {
    $where .= " AND a.data_inscricao >= ?";
    $params[] = $filtroDataDe;
}
if ($filtroDataAte) {
    $where .= " AND a.data_inscricao <= ?";
    $params[] = $filtroDataAte;
}

// Buscar dados
$stmt = $conn->prepare("
    SELECT a.*, e.nome as escola_nome, p.nome as professor_nome, 
           s.nome as sala_nome, t.nome as turno_nome
    FROM alunos a 
    JOIN escolas e ON a.escola_id = e.id 
    JOIN professores p ON a.professor_id = p.id 
    JOIN salas s ON a.sala_id = s.id 
    JOIN turnos t ON a.turno_id = t.id 
    $where 
    ORDER BY e.nome, a.nome
");
$stmt->execute($params);
$resultado = $stmt->fetchAll();

// Dados para selects
$escolas = $conn->query("SELECT id, nome FROM escolas WHERE ativa = 1 ORDER BY nome")->fetchAll();
$professoresAll = $conn->query("SELECT id, nome FROM professores WHERE ativo = 1 ORDER BY nome")->fetchAll();
$salasAll = $conn->query("SELECT id, nome FROM salas WHERE ativa = 1 ORDER BY nome")->fetchAll();
$turnos = $conn->query("SELECT id, nome FROM turnos WHERE ativo = 1 ORDER BY nome")->fetchAll();

// Estatísticas do relatório
$totalResultado = count($resultado);
$totalAtivos = count(array_filter($resultado, fn($r) => $r['status'] === 'ativo'));
$totalInativos = count(array_filter($resultado, fn($r) => $r['status'] === 'inativo'));
$totalTransferidos = count(array_filter($resultado, fn($r) => $r['status'] === 'transferido'));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Relatórios de Alunos</h5>
    <div class="no-print">
        <button class="btn btn-outline-success me-2" onclick="exportCSV()">
            <i class="bi bi-file-earmark-excel"></i> Exportar CSV
        </button>
        <button class="btn btn-outline-primary" onclick="printReport()">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

<!-- Filtros -->
<div class="report-filters no-print">
    <h6 class="fw-bold mb-3"><i class="bi bi-funnel"></i> Filtros</h6>
    <form method="GET">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Escola</label>
                <select class="form-select" name="escola">
                    <option value="">Todas</option>
                    <?php foreach ($escolas as $escola): ?>
                    <option value="<?php echo $escola['id']; ?>" <?php echo $filtroEscola == $escola['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($escola['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Professor</label>
                <select class="form-select" name="professor">
                    <option value="">Todos</option>
                    <?php foreach ($professoresAll as $prof): ?>
                    <option value="<?php echo $prof['id']; ?>" <?php echo $filtroProfessor == $prof['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($prof['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Sala</label>
                <select class="form-select" name="sala">
                    <option value="">Todas</option>
                    <?php foreach ($salasAll as $sala): ?>
                    <option value="<?php echo $sala['id']; ?>" <?php echo $filtroSala == $sala['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sala['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Turno</label>
                <select class="form-select" name="turno">
                    <option value="">Todos</option>
                    <?php foreach ($turnos as $turno): ?>
                    <option value="<?php echo $turno['id']; ?>" <?php echo $filtroTurno == $turno['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($turno['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">Todos</option>
                    <option value="ativo" <?php echo $filtroStatus === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                    <option value="inativo" <?php echo $filtroStatus === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                    <option value="transferido" <?php echo $filtroStatus === 'transferido' ? 'selected' : ''; ?>>Transferido</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Inscrição De</label>
                <input type="date" class="form-control" name="data_de" value="<?php echo htmlspecialchars($filtroDataDe); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Inscrição Até</label>
                <input type="date" class="form-control" name="data_ate" value="<?php echo htmlspecialchars($filtroDataAte); ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Gerar Relatório
                </button>
                <a href="relatorios.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Limpar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Resumo -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-light">
            <div class="card-body text-center">
                <h4 class="fw-bold text-primary"><?php echo $totalResultado; ?></h4>
                <small class="text-muted">Total Encontrados</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-light">
            <div class="card-body text-center">
                <h4 class="fw-bold text-success"><?php echo $totalAtivos; ?></h4>
                <small class="text-muted">Ativos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-light">
            <div class="card-body text-center">
                <h4 class="fw-bold text-secondary"><?php echo $totalInativos; ?></h4>
                <small class="text-muted">Inativos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-light">
            <div class="card-body text-center">
                <h4 class="fw-bold text-warning"><?php echo $totalTransferidos; ?></h4>
                <small class="text-muted">Transferidos</small>
            </div>
        </div>
    </div>
</div>

<!-- Tabela do Relatório -->
<div class="table-container fade-in">
    <div class="table-responsive">
        <table class="table table-hover table-striped" id="tabelaRelatorio">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Escola</th>
                    <th>Professor</th>
                    <th>Sala</th>
                    <th>Turno</th>
                    <th>Status</th>
                    <th>Data Inscrição</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultado as $aluno): ?>
                <?php
                    $statusClass = match($aluno['status']) {
                        'ativo' => 'bg-success',
                        'inativo' => 'bg-secondary',
                        'transferido' => 'bg-warning text-dark',
                        default => 'bg-secondary'
                    };
                ?>
                <tr>
                    <td><?php echo $aluno['id']; ?></td>
                    <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                    <td><?php echo htmlspecialchars($aluno['cpf']); ?></td>
                    <td><?php echo htmlspecialchars($aluno['escola_nome']); ?></td>
                    <td><?php echo htmlspecialchars($aluno['professor_nome']); ?></td>
                    <td><?php echo htmlspecialchars($aluno['sala_nome']); ?></td>
                    <td><?php echo htmlspecialchars($aluno['turno_nome']); ?></td>
                    <td><span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($aluno['status']); ?></span></td>
                    <td><?php echo $aluno['data_inscricao'] ? date('d/m/Y', strtotime($aluno['data_inscricao'])) : '-'; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($resultado)): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">Nenhum registro encontrado com os filtros aplicados.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function exportCSV() {
    const table = document.getElementById('tabelaRelatorio');
    const rows = table.querySelectorAll('tr');
    let csv = [];

    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            let text = col.innerText.replace(/"/g, '""');
            rowData.push('"' + text + '"');
        });
        csv.push(rowData.join(','));
    });

    const csvContent = '\uFEFF' + csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'relatorio_alunos_' + new Date().toISOString().slice(0, 10) + '.csv';
    link.click();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
