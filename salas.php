<?php
$pageTitle = 'Salas';
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
    $capacidade = intval($_POST['capacidade'] ?? 30);
    $escola_id = intval($_POST['escola_id'] ?? 0);

    if ($action === 'create' && $escola_id > 0) {
        $stmt = $conn->prepare("INSERT INTO salas (nome, capacidade, escola_id) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $capacidade, $escola_id]);
        $msg = 'Sala cadastrada com sucesso!';
        $msgType = 'success';
    } elseif ($action === 'update' && $id > 0) {
        $stmt = $conn->prepare("UPDATE salas SET nome = ?, capacidade = ?, escola_id = ? WHERE id = ?");
        $stmt->execute([$nome, $capacidade, $escola_id, $id]);
        $msg = 'Sala atualizada com sucesso!';
        $msgType = 'success';
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $conn->prepare("DELETE FROM salas WHERE id = ?");
        $stmt->execute([$id]);
        $msg = 'Sala excluída com sucesso!';
        $msgType = 'success';
    }
}

// Listar salas
$salas = $conn->query("
    SELECT s.*, e.nome as escola_nome,
        (SELECT COUNT(*) FROM alunos a WHERE a.sala_id = s.id) as total_alunos
    FROM salas s 
    JOIN escolas e ON s.escola_id = e.id 
    WHERE s.ativa = 1 
    ORDER BY e.nome, s.nome
")->fetchAll();

// Escolas para o select
$escolas = $conn->query("SELECT id, nome FROM escolas WHERE ativa = 1 ORDER BY nome")->fetchAll();
?>

<?php if ($msg): ?>
<div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Gerenciar Salas</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSala" onclick="limparForm()">
        <i class="bi bi-plus-circle"></i> Nova Sala
    </button>
</div>

<div class="table-container fade-in">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Capacidade</th>
                    <th>Escola</th>
                    <th>Alunos</th>
                    <th>Ocupação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salas as $sala): ?>
                <?php 
                    $ocupacao = $sala['capacidade'] > 0 ? round(($sala['total_alunos'] / $sala['capacidade']) * 100) : 0;
                    $ocupacaoClass = $ocupacao > 90 ? 'bg-danger' : ($ocupacao > 60 ? 'bg-warning' : 'bg-success');
                ?>
                <tr>
                    <td><?php echo $sala['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($sala['nome']); ?></strong></td>
                    <td><?php echo $sala['capacidade']; ?> alunos</td>
                    <td><?php echo htmlspecialchars($sala['escola_nome']); ?></td>
                    <td><span class="badge bg-primary"><?php echo $sala['total_alunos']; ?></span></td>
                    <td>
                        <div class="progress" style="height: 20px; min-width: 80px;">
                            <div class="progress-bar <?php echo $ocupacaoClass; ?>" style="width: <?php echo $ocupacao; ?>%">
                                <?php echo $ocupacao; ?>%
                            </div>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-action" onclick="editarSala(<?php echo htmlspecialchars(json_encode($sala)); ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $sala['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger btn-action btn-delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($salas)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Nenhuma sala cadastrada.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Sala -->
<div class="modal fade" id="modalSala" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSalaTitle">Nova Sala</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="formId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Nome da Sala *</label>
                        <input type="text" class="form-control" name="nome" id="formNome" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacidade</label>
                        <input type="number" class="form-control" name="capacidade" id="formCapacidade" value="30" min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Escola *</label>
                        <select class="form-select" name="escola_id" id="formEscolaId" required>
                            <option value="">Selecione a escola...</option>
                            <?php foreach ($escolas as $escola): ?>
                            <option value="<?php echo $escola['id']; ?>"><?php echo htmlspecialchars($escola['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
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
    document.getElementById('modalSalaTitle').textContent = 'Nova Sala';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formId').value = '0';
    document.getElementById('formNome').value = '';
    document.getElementById('formCapacidade').value = '30';
    document.getElementById('formEscolaId').value = '';
}

function editarSala(sala) {
    document.getElementById('modalSalaTitle').textContent = 'Editar Sala';
    document.getElementById('formAction').value = 'update';
    document.getElementById('formId').value = sala.id;
    document.getElementById('formNome').value = sala.nome;
    document.getElementById('formCapacidade').value = sala.capacidade;
    document.getElementById('formEscolaId').value = sala.escola_id;
    new bootstrap.Modal(document.getElementById('modalSala')).show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
