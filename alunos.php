<?php
$pageTitle = 'Alunos';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$conn = getConnection();
$msg = '';
$msgType = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $data_nascimento = $_POST['data_nascimento'] ?? '';
    $cpf = trim($_POST['cpf'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $escola_id = intval($_POST['escola_id'] ?? 0);
    $professor_id = intval($_POST['professor_id'] ?? 0);
    $sala_id = intval($_POST['sala_id'] ?? 0);
    $turno_id = intval($_POST['turno_id'] ?? 0);
    $status = $_POST['status'] ?? 'ativo';
    $observacoes = trim($_POST['observacoes'] ?? '');

    if ($action === 'create' && $escola_id > 0 && $professor_id > 0 && $sala_id > 0 && $turno_id > 0) {
        $stmt = $conn->prepare("INSERT INTO alunos (nome, data_nascimento, cpf, email, telefone, endereco, escola_id, professor_id, sala_id, turno_id, status, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $data_nascimento ?: null, $cpf, $email, $telefone, $endereco, $escola_id, $professor_id, $sala_id, $turno_id, $status, $observacoes]);
        $msg = 'Aluno inscrito com sucesso!';
        $msgType = 'success';
    } elseif ($action === 'update' && $id > 0) {
        $stmt = $conn->prepare("UPDATE alunos SET nome = ?, data_nascimento = ?, cpf = ?, email = ?, telefone = ?, endereco = ?, escola_id = ?, professor_id = ?, sala_id = ?, turno_id = ?, status = ?, observacoes = ? WHERE id = ?");
        $stmt->execute([$nome, $data_nascimento ?: null, $cpf, $email, $telefone, $endereco, $escola_id, $professor_id, $sala_id, $turno_id, $status, $observacoes, $id]);
        $msg = 'Aluno atualizado com sucesso!';
        $msgType = 'success';
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $conn->prepare("DELETE FROM alunos WHERE id = ?");
        $stmt->execute([$id]);
        $msg = 'Aluno excluído com sucesso!';
        $msgType = 'success';
    }
}

// Filtros
$filtroEscola = intval($_GET['escola'] ?? 0);
$filtroTurno = intval($_GET['turno'] ?? 0);
$filtroBusca = trim($_GET['busca'] ?? '');

$where = "WHERE 1=1";
$params = [];

if ($filtroEscola > 0) {
    $where .= " AND a.escola_id = ?";
    $params[] = $filtroEscola;
}
if ($filtroTurno > 0) {
    $where .= " AND a.turno_id = ?";
    $params[] = $filtroTurno;
}
if ($filtroBusca) {
    $where .= " AND (a.nome LIKE ? OR a.cpf LIKE ?)";
    $params[] = "%$filtroBusca%";
    $params[] = "%$filtroBusca%";
}

// Listar alunos
$stmt = $conn->prepare("
    SELECT a.*, e.nome as escola_nome, p.nome as professor_nome, 
           s.nome as sala_nome, t.nome as turno_nome
    FROM alunos a 
    JOIN escolas e ON a.escola_id = e.id 
    JOIN professores p ON a.professor_id = p.id 
    JOIN salas s ON a.sala_id = s.id 
    JOIN turnos t ON a.turno_id = t.id 
    $where 
    ORDER BY a.nome
");
$stmt->execute($params);
$alunos = $stmt->fetchAll();

// Dados para selects
$escolas = $conn->query("SELECT id, nome FROM escolas WHERE ativa = 1 ORDER BY nome")->fetchAll();
$turnos = $conn->query("SELECT id, nome FROM turnos WHERE ativo = 1 ORDER BY nome")->fetchAll();
$professores = $conn->query("SELECT p.id, p.nome, p.escola_id FROM professores p WHERE p.ativo = 1 ORDER BY p.nome")->fetchAll();
$salas = $conn->query("SELECT s.id, s.nome, s.escola_id FROM salas s WHERE s.ativa = 1 ORDER BY s.nome")->fetchAll();
?>

<?php if ($msg): ?>
<div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Gerenciar Alunos</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAluno" onclick="limparForm()">
        <i class="bi bi-plus-circle"></i> Nova Inscrição
    </button>
</div>

<!-- Filtros -->
<div class="report-filters mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Buscar</label>
            <input type="text" class="form-control" name="busca" placeholder="Nome ou CPF..." value="<?php echo htmlspecialchars($filtroBusca); ?>">
        </div>
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
            <button type="submit" class="btn btn-outline-primary me-2">
                <i class="bi bi-search"></i> Filtrar
            </button>
            <a href="alunos.php" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Limpar
            </a>
        </div>
    </form>
</div>

<div class="table-container fade-in">
    <div class="table-responsive">
        <table class="table table-hover">
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
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alunos as $aluno): ?>
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
                    <td><strong><?php echo htmlspecialchars($aluno['nome']); ?></strong></td>
                    <td><?php echo htmlspecialchars($aluno['cpf']); ?></td>
                    <td><?php echo htmlspecialchars($aluno['escola_nome']); ?></td>
                    <td><?php echo htmlspecialchars($aluno['professor_nome']); ?></td>
                    <td><?php echo htmlspecialchars($aluno['sala_nome']); ?></td>
                    <td><span class="badge bg-info"><?php echo htmlspecialchars($aluno['turno_nome']); ?></span></td>
                    <td><span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($aluno['status']); ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-action" onclick="editarAluno(<?php echo htmlspecialchars(json_encode($aluno)); ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $aluno['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger btn-action btn-delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($alunos)): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">Nenhum aluno encontrado.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="text-muted mt-2">
        <small>Total: <?php echo count($alunos); ?> aluno(s)</small>
    </div>
</div>

<!-- Modal Aluno -->
<div class="modal fade" id="modalAluno" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAlunoTitle">Nova Inscrição</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="formId" value="0">

                    <h6 class="text-muted mb-3"><i class="bi bi-person"></i> Dados Pessoais</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" class="form-control" name="nome" id="formNome" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" class="form-control" name="data_nascimento" id="formNascimento">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">CPF</label>
                            <input type="text" class="form-control" name="cpf" id="cpf" placeholder="000.000.000-00">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="formEmail">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" class="form-control" name="telefone" id="formTelefone">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="formStatus">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="transferido">Transferido</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Endereço</label>
                        <input type="text" class="form-control" name="endereco" id="formEndereco">
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-building"></i> Vínculo Escolar</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Escola *</label>
                            <select class="form-select" name="escola_id" id="escola_id" required onchange="filtrarVinculos()">
                                <option value="">Selecione a escola...</option>
                                <?php foreach ($escolas as $escola): ?>
                                <option value="<?php echo $escola['id']; ?>"><?php echo htmlspecialchars($escola['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Professor *</label>
                            <select class="form-select" name="professor_id" id="professor_id" required>
                                <option value="">Selecione a escola primeiro...</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sala *</label>
                            <select class="form-select" name="sala_id" id="sala_id" required>
                                <option value="">Selecione a escola primeiro...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Turno *</label>
                            <select class="form-select" name="turno_id" id="formTurnoId" required>
                                <option value="">Selecione o turno...</option>
                                <?php foreach ($turnos as $turno): ?>
                                <option value="<?php echo $turno['id']; ?>"><?php echo htmlspecialchars($turno['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" name="observacoes" id="formObservacoes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Inscrição</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Dados de professores e salas para filtro dinâmico
const allProfessores = <?php echo json_encode($professores); ?>;
const allSalas = <?php echo json_encode($salas); ?>;

function filtrarVinculos() {
    const escolaId = document.getElementById('escola_id').value;

    // Filtrar professores
    const profSelect = document.getElementById('professor_id');
    profSelect.innerHTML = '<option value="">Selecione...</option>';
    allProfessores.filter(p => p.escola_id == escolaId).forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = p.nome;
        profSelect.appendChild(opt);
    });

    // Filtrar salas
    const salaSelect = document.getElementById('sala_id');
    salaSelect.innerHTML = '<option value="">Selecione...</option>';
    allSalas.filter(s => s.escola_id == escolaId).forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.nome;
        salaSelect.appendChild(opt);
    });
}

function limparForm() {
    document.getElementById('modalAlunoTitle').textContent = 'Nova Inscrição';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formId').value = '0';
    document.getElementById('formNome').value = '';
    document.getElementById('formNascimento').value = '';
    document.getElementById('cpf').value = '';
    document.getElementById('formEmail').value = '';
    document.getElementById('formTelefone').value = '';
    document.getElementById('formEndereco').value = '';
    document.getElementById('formStatus').value = 'ativo';
    document.getElementById('escola_id').value = '';
    document.getElementById('professor_id').innerHTML = '<option value="">Selecione a escola primeiro...</option>';
    document.getElementById('sala_id').innerHTML = '<option value="">Selecione a escola primeiro...</option>';
    document.getElementById('formTurnoId').value = '';
    document.getElementById('formObservacoes').value = '';
}

function editarAluno(aluno) {
    document.getElementById('modalAlunoTitle').textContent = 'Editar Aluno';
    document.getElementById('formAction').value = 'update';
    document.getElementById('formId').value = aluno.id;
    document.getElementById('formNome').value = aluno.nome;
    document.getElementById('formNascimento').value = aluno.data_nascimento || '';
    document.getElementById('cpf').value = aluno.cpf || '';
    document.getElementById('formEmail').value = aluno.email || '';
    document.getElementById('formTelefone').value = aluno.telefone || '';
    document.getElementById('formEndereco').value = aluno.endereco || '';
    document.getElementById('formStatus').value = aluno.status;
    document.getElementById('escola_id').value = aluno.escola_id;
    document.getElementById('formTurnoId').value = aluno.turno_id;
    document.getElementById('formObservacoes').value = aluno.observacoes || '';

    // Carregar professores e salas da escola
    filtrarVinculos();

    // Setar valores após filtro (com timeout para esperar o DOM)
    setTimeout(() => {
        document.getElementById('professor_id').value = aluno.professor_id;
        document.getElementById('sala_id').value = aluno.sala_id;
    }, 100);

    new bootstrap.Modal(document.getElementById('modalAluno')).show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
