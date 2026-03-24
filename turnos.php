<?php
$pageTitle = 'Turnos';
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
    $horario_inicio = $_POST['horario_inicio'] ?? '';
    $horario_fim = $_POST['horario_fim'] ?? '';

    if ($action === 'create') {
        $stmt = $conn->prepare("INSERT INTO turnos (nome, horario_inicio, horario_fim) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $horario_inicio, $horario_fim]);
        $msg = 'Turno cadastrado com sucesso!';
        $msgType = 'success';
    } elseif ($action === 'update' && $id > 0) {
        $stmt = $conn->prepare("UPDATE turnos SET nome = ?, horario_inicio = ?, horario_fim = ? WHERE id = ?");
        $stmt->execute([$nome, $horario_inicio, $horario_fim, $id]);
        $msg = 'Turno atualizado com sucesso!';
        $msgType = 'success';
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $conn->prepare("DELETE FROM turnos WHERE id = ?");
        $stmt->execute([$id]);
        $msg = 'Turno excluído com sucesso!';
        $msgType = 'success';
    }
}

// Listar turnos
$turnos = $conn->query("
    SELECT t.*,
        (SELECT COUNT(*) FROM alunos a WHERE a.turno_id = t.id) as total_alunos
    FROM turnos t 
    WHERE t.ativo = 1 
    ORDER BY t.horario_inicio
")->fetchAll();
?>

<?php if ($msg): ?>
<div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Gerenciar Turnos</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTurno" onclick="limparForm()">
        <i class="bi bi-plus-circle"></i> Novo Turno
    </button>
</div>

<div class="table-container fade-in">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Horário Início</th>
                    <th>Horário Fim</th>
                    <th>Alunos</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($turnos as $turno): ?>
                <tr>
                    <td><?php echo $turno['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($turno['nome']); ?></strong></td>
                    <td><?php echo $turno['horario_inicio'] ? date('H:i', strtotime($turno['horario_inicio'])) : '-'; ?></td>
                    <td><?php echo $turno['horario_fim'] ? date('H:i', strtotime($turno['horario_fim'])) : '-'; ?></td>
                    <td><span class="badge bg-primary"><?php echo $turno['total_alunos']; ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-action" onclick="editarTurno(<?php echo htmlspecialchars(json_encode($turno)); ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $turno['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger btn-action btn-delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($turnos)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Nenhum turno cadastrado.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Turno -->
<div class="modal fade" id="modalTurno" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTurnoTitle">Novo Turno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="formId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Nome do Turno *</label>
                        <input type="text" class="form-control" name="nome" id="formNome" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Horário Início</label>
                            <input type="time" class="form-control" name="horario_inicio" id="formInicio">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Horário Fim</label>
                            <input type="time" class="form-control" name="horario_fim" id="formFim">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function limparForm() {
    document.getElementById('modalTurnoTitle').textContent = 'Novo Turno';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formId').value = '0';
    document.getElementById('formNome').value = '';
    document.getElementById('formInicio').value = '';
    document.getElementById('formFim').value = '';
}

function editarTurno(turno) {
    document.getElementById('modalTurnoTitle').textContent = 'Editar Turno';
    document.getElementById('formAction').value = 'update';
    document.getElementById('formId').value = turno.id;
    document.getElementById('formNome').value = turno.nome;
    document.getElementById('formInicio').value = turno.horario_inicio || '';
    document.getElementById('formFim').value = turno.horario_fim || '';
    new bootstrap.Modal(document.getElementById('modalTurno')).show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
