<?php
$pageTitle = 'Escolas';
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
    $endereco = trim($_POST['endereco'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $diretor = trim($_POST['diretor'] ?? '');

    if ($action === 'create') {
        $stmt = $conn->prepare("INSERT INTO escolas (nome, endereco, telefone, email, diretor) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $endereco, $telefone, $email, $diretor]);
        $msg = 'Escola cadastrada com sucesso!';
        $msgType = 'success';
    } elseif ($action === 'update' && $id > 0) {
        $stmt = $conn->prepare("UPDATE escolas SET nome = ?, endereco = ?, telefone = ?, email = ?, diretor = ? WHERE id = ?");
        $stmt->execute([$nome, $endereco, $telefone, $email, $diretor, $id]);
        $msg = 'Escola atualizada com sucesso!';
        $msgType = 'success';
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $conn->prepare("DELETE FROM escolas WHERE id = ?");
        $stmt->execute([$id]);
        $msg = 'Escola excluída com sucesso!';
        $msgType = 'success';
    }
}

// Listar escolas
$escolas = $conn->query("
    SELECT e.*, 
        (SELECT COUNT(*) FROM alunos a WHERE a.escola_id = e.id) as total_alunos,
        (SELECT COUNT(*) FROM professores p WHERE p.escola_id = e.id) as total_professores
    FROM escolas e 
    WHERE e.ativa = 1 
    ORDER BY e.nome
")->fetchAll();
?>

<?php if ($msg): ?>
<div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Gerenciar Escolas</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEscola" onclick="limparForm()">
        <i class="bi bi-plus-circle"></i> Nova Escola
    </button>
</div>

<div class="table-container fade-in">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Endereço</th>
                    <th>Telefone</th>
                    <th>Diretor</th>
                    <th>Alunos</th>
                    <th>Professores</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($escolas as $escola): ?>
                <tr>
                    <td><?php echo $escola['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($escola['nome']); ?></strong></td>
                    <td><?php echo htmlspecialchars($escola['endereco']); ?></td>
                    <td><?php echo htmlspecialchars($escola['telefone']); ?></td>
                    <td><?php echo htmlspecialchars($escola['diretor']); ?></td>
                    <td><span class="badge bg-primary"><?php echo $escola['total_alunos']; ?></span></td>
                    <td><span class="badge bg-warning text-dark"><?php echo $escola['total_professores']; ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-action" onclick="editarEscola(<?php echo htmlspecialchars(json_encode($escola)); ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $escola['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger btn-action btn-delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($escolas)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Nenhuma escola cadastrada.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Escola -->
<div class="modal fade" id="modalEscola" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEscolaTitle">Nova Escola</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="formId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Nome da Escola *</label>
                        <input type="text" class="form-control" name="nome" id="formNome" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Endereço</label>
                        <input type="text" class="form-control" name="endereco" id="formEndereco">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" class="form-control" name="telefone" id="formTelefone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="formEmail">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Diretor(a)</label>
                        <input type="text" class="form-control" name="diretor" id="formDiretor">
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
    document.getElementById('modalEscolaTitle').textContent = 'Nova Escola';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formId').value = '0';
    document.getElementById('formNome').value = '';
    document.getElementById('formEndereco').value = '';
    document.getElementById('formTelefone').value = '';
    document.getElementById('formEmail').value = '';
    document.getElementById('formDiretor').value = '';
}

function editarEscola(escola) {
    document.getElementById('modalEscolaTitle').textContent = 'Editar Escola';
    document.getElementById('formAction').value = 'update';
    document.getElementById('formId').value = escola.id;
    document.getElementById('formNome').value = escola.nome;
    document.getElementById('formEndereco').value = escola.endereco || '';
    document.getElementById('formTelefone').value = escola.telefone || '';
    document.getElementById('formEmail').value = escola.email || '';
    document.getElementById('formDiretor').value = escola.diretor || '';
    new bootstrap.Modal(document.getElementById('modalEscola')).show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
