<?php
$pageTitle = 'Professores';
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
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $especialidade = trim($_POST['especialidade'] ?? '');
    $escola_id = intval($_POST['escola_id'] ?? 0);

    if ($action === 'create' && $escola_id > 0) {
        $stmt = $conn->prepare("INSERT INTO professores (nome, email, telefone, especialidade, escola_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $telefone, $especialidade, $escola_id]);
        $msg = 'Professor cadastrado com sucesso!';
        $msgType = 'success';
    } elseif ($action === 'update' && $id > 0) {
        $stmt = $conn->prepare("UPDATE professores SET nome = ?, email = ?, telefone = ?, especialidade = ?, escola_id = ? WHERE id = ?");
        $stmt->execute([$nome, $email, $telefone, $especialidade, $escola_id, $id]);
        $msg = 'Professor atualizado com sucesso!';
        $msgType = 'success';
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $conn->prepare("DELETE FROM professores WHERE id = ?");
        $stmt->execute([$id]);
        $msg = 'Professor excluído com sucesso!';
        $msgType = 'success';
    }
}

// Listar professores
$professores = $conn->query("
    SELECT p.*, e.nome as escola_nome,
        (SELECT COUNT(*) FROM alunos a WHERE a.professor_id = p.id) as total_alunos
    FROM professores p 
    JOIN escolas e ON p.escola_id = e.id 
    WHERE p.ativo = 1 
    ORDER BY p.nome
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
    <h5 class="mb-0">Gerenciar Professores</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProfessor" onclick="limparForm()">
        <i class="bi bi-plus-circle"></i> Novo Professor
    </button>
</div>

<div class="table-container fade-in">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Especialidade</th>
                    <th>Escola</th>
                    <th>Alunos</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($professores as $prof): ?>
                <tr>
                    <td><?php echo $prof['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($prof['nome']); ?></strong></td>
                    <td><?php echo htmlspecialchars($prof['email']); ?></td>
                    <td><?php echo htmlspecialchars($prof['telefone']); ?></td>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($prof['especialidade']); ?></span></td>
                    <td><?php echo htmlspecialchars($prof['escola_nome']); ?></td>
                    <td><span class="badge bg-primary"><?php echo $prof['total_alunos']; ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-action" onclick="editarProfessor(<?php echo htmlspecialchars(json_encode($prof)); ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $prof['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger btn-action btn-delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($professores)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Nenhum professor cadastrado.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Professor -->
<div class="modal fade" id="modalProfessor" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProfessorTitle">Novo Professor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="formId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" class="form-control" name="nome" id="formNome" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="formEmail">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" class="form-control" name="telefone" id="formTelefone">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Especialidade</label>
                        <input type="text" class="form-control" name="especialidade" id="formEspecialidade">
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
    document.getElementById('modalProfessorTitle').textContent = 'Novo Professor';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formId').value = '0';
    document.getElementById('formNome').value = '';
    document.getElementById('formEmail').value = '';
    document.getElementById('formTelefone').value = '';
    document.getElementById('formEspecialidade').value = '';
    document.getElementById('formEscolaId').value = '';
}

function editarProfessor(prof) {
    document.getElementById('modalProfessorTitle').textContent = 'Editar Professor';
    document.getElementById('formAction').value = 'update';
    document.getElementById('formId').value = prof.id;
    document.getElementById('formNome').value = prof.nome;
    document.getElementById('formEmail').value = prof.email || '';
    document.getElementById('formTelefone').value = prof.telefone || '';
    document.getElementById('formEspecialidade').value = prof.especialidade || '';
    document.getElementById('formEscolaId').value = prof.escola_id;
    new bootstrap.Modal(document.getElementById('modalProfessor')).show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
