/**
 * Sistema de Gerenciamento Escolar - JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {

    // Toggle sidebar
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function () {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
            }
        });
    }

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('show')) {
            if (!sidebar.contains(e.target) && e.target !== menuToggle && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });

    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Tem certeza que deseja excluir este registro?')) {
                e.preventDefault();
            }
        });
    });

    // Dynamic select loading: when escola changes, load professores and salas
    const escolaSelect = document.getElementById('escola_id');
    if (escolaSelect) {
        escolaSelect.addEventListener('change', function () {
            const escolaId = this.value;
            if (!escolaId) return;

            // Load professors for this school
            const profSelect = document.getElementById('professor_id');
            if (profSelect) {
                loadOptions('ajax/get_professores.php?escola_id=' + escolaId, profSelect);
            }

            // Load classrooms for this school
            const salaSelect = document.getElementById('sala_id');
            if (salaSelect) {
                loadOptions('ajax/get_salas.php?escola_id=' + escolaId, salaSelect);
            }
        });
    }

    // CPF mask
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function () {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            if (value.length > 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
            } else if (value.length > 3) {
                value = value.replace(/(\d{3})(\d{1,3})/, '$1.$2');
            }
            this.value = value;
        });
    }

    // Phone mask
    const phoneInputs = document.querySelectorAll('input[name="telefone"]');
    phoneInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            if (value.length > 10) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            this.value = value;
        });
    });
});

/**
 * Load options into a select element via AJAX
 */
function loadOptions(url, selectElement) {
    selectElement.innerHTML = '<option value="">Carregando...</option>';
    selectElement.disabled = true;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            selectElement.innerHTML = '<option value="">Selecione...</option>';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nome;
                selectElement.appendChild(option);
            });
            selectElement.disabled = false;
        })
        .catch(() => {
            selectElement.innerHTML = '<option value="">Erro ao carregar</option>';
            selectElement.disabled = false;
        });
}

/**
 * Print report
 */
function printReport() {
    window.print();
}
