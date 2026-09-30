// ==========================================
// 1. ALTERNADOR DE MODO ESCURO (DARK MODE)
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('dark-mode-toggle');
    
    if (toggleBtn) {
        // Verifica o tema inicial e define o ícone correto
        if (document.body.classList.contains('dark-mode')) {
            toggleBtn.innerHTML = '☀️';
        } else {
            toggleBtn.innerHTML = '🌙';
        }

        // Evento de clique para alternar o tema
        toggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            
            // Efeito visual de clique no botão
            toggleBtn.style.transform = 'scale(0.8)';
            setTimeout(() => { toggleBtn.style.transform = 'scale(1)'; }, 150);

            // Salva a preferência e troca o ícone
            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
                toggleBtn.innerHTML = '☀️';
            } else {
                localStorage.setItem('theme', 'light');
                toggleBtn.innerHTML = '🌙';
            }
        });
    }
});

// ==========================================
// 2. CADASTRO DE LIVRO (Alternar Formatos)
// ==========================================
function toggleFormato(tipo) {
    const checkbox = document.getElementById('check_' + tipo);
    const bloco = document.getElementById('bloco_' + tipo);
    if (checkbox && bloco) {
        bloco.classList.toggle('is-hidden', !checkbox.checked);
    }
}

// ==========================================
// 3. CARRINHO DE COMPRAS (Alterar Qtd / Remover)
// ==========================================
function alterarQtd(idProduto, delta) {
    let elementoQtd = document.getElementById('qtd-' + idProduto);
    if (!elementoQtd) return;

    let qtdAtual = parseInt(elementoQtd.innerText);
    let novaQtd = qtdAtual + delta;

    if (novaQtd < 1) return;

    let formData = new FormData();
    formData.append('acao', 'atualizar');
    formData.append('id_produto', idProduto);
    formData.append('quantidade', novaQtd);

    fetch('../src/php/carrinho_acoes.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            let badgeHeader = document.getElementById('cart-count');
            if (badgeHeader) badgeHeader.innerText = data.total_itens;

            elementoQtd.innerText = novaQtd;

            let itemAtualizado = data.itens.find(i => i.id_produto == idProduto);
            if (itemAtualizado) {
                let subtotalEl = document.getElementById('subtotal-' + idProduto);
                if (subtotalEl) subtotalEl.innerText = 'R$ ' + itemAtualizado.subtotal;
            }
            let totalGeralEl = document.getElementById('cart-total-geral');
            if (totalGeralEl) totalGeralEl.innerText = 'R$ ' + data.subtotal;
        } else {
            alert(data.mensagem || 'Estoque insuficiente.');
        }
    })
    .catch(error => console.error('Erro:', error));
}

function removerItem(idProduto) {
    let formData = new FormData();
    formData.append('acao', 'remover');
    formData.append('id_produto', idProduto);

    fetch('../src/php/carrinho_acoes.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            let badgeHeader = document.getElementById('cart-count');
            if (badgeHeader) badgeHeader.innerText = data.total_itens;

            let row = document.getElementById('produto-row-' + idProduto);
            if (row) row.remove();

            let totalGeralEl = document.getElementById('cart-total-geral');
            if (totalGeralEl) totalGeralEl.innerText = 'R$ ' + data.subtotal;

            if (data.total_itens === 0) {
                location.reload();
            }
        }
    })
    .catch(error => console.error('Erro:', error));
}

// ==========================================
// 4. CHECKOUT (Atualizar Totais e Pagamento)
// ==========================================
function atualizarTotais() {
    let formCheckout = document.getElementById('form-checkout');
    if (!formCheckout) return;

    let subtotalOriginal = parseFloat(formCheckout.dataset.subtotal || 0);
    let formaPagamento = document.querySelector('input[name="forma_pagamento"]:checked')?.value;
    let containerParcelas = document.getElementById('container-parcelas');
    let linhaDesconto = document.getElementById('linha-desconto');
    let linhaJuros = document.getElementById('linha-juros');

    let totalFinal = subtotalOriginal;

    if (formaPagamento === 'cartao') {
        if (containerParcelas) containerParcelas.classList.remove('is-hidden');

        let selectParcelas = document.getElementById('select-parcelas');
        let parcelas = selectParcelas ? parseInt(selectParcelas.value) : 1;

        if (parcelas > 3) {
            let percentualAcres = (parcelas - 3) * 0.015;
            let acrescimo = subtotalOriginal * percentualAcres;
            totalFinal += acrescimo;

            let valorJurosEl = document.getElementById('valor-juros');
            if (valorJurosEl) valorJurosEl.innerText = '+ R$ ' + acrescimo.toFixed(2).replace('.', ',');
            if (linhaJuros) linhaJuros.classList.remove('is-hidden');
        } else {
            if (linhaJuros) linhaJuros.classList.add('is-hidden');
        }
        if (linhaDesconto) linhaDesconto.classList.add('is-hidden');
    } else {
        if (containerParcelas) containerParcelas.classList.add('is-hidden');
        if (linhaJuros) linhaJuros.classList.add('is-hidden');

        if (formaPagamento === 'pix') {
            let desconto = subtotalOriginal * 0.10;
            totalFinal -= desconto;
            let valorDescontoEl = document.getElementById('valor-desconto');
            if (valorDescontoEl) valorDescontoEl.innerText = '- R$ ' + desconto.toFixed(2).replace('.', ',');
            if (linhaDesconto) linhaDesconto.classList.remove('is-hidden');
        } else {
            if (linhaDesconto) linhaDesconto.classList.add('is-hidden');
        }
    }

    let valorTotalFinalEl = document.getElementById('valor-total-final');
    if (valorTotalFinalEl) {
        valorTotalFinalEl.innerText = 'R$ ' + totalFinal.toFixed(2).replace('.', ',');
    }
}

// ==========================================
// 5. PÁGINA DO LIVRO (Toast e Adicionar Carrinho)
// ==========================================
function mostrarToast(mensagem, tipo = 'success') {
    let toast = document.getElementById('toast-notification');
    if (!toast) return;

    toast.innerText = mensagem;
    toast.className = 'custom-toast ' + tipo + ' show';

    setTimeout(() => {
        toast.className = 'custom-toast';
    }, 3500);
}

// ==========================================
// 6. LIGAÇÃO DOS EVENTOS GERAIS
// ==========================================
document.addEventListener('DOMContentLoaded', function () {

    // Confirmação genérica
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });

    // Submissão automática do Select na Busca
    document.querySelectorAll('.js-auto-submit').forEach(select => {
        select.addEventListener('change', function () {
            select.form.submit();
        });
    });

    // Formatos de Livro
    document.querySelectorAll('[data-toggle-formato]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            toggleFormato(checkbox.dataset.toggleFormato);
        });
    });

    // Botões do Carrinho
    document.querySelectorAll('.js-qtd').forEach(btn => {
        btn.addEventListener('click', function () {
            alterarQtd(btn.dataset.id, parseInt(btn.dataset.delta));
        });
    });

    document.querySelectorAll('.js-remover-item').forEach(btn => {
        btn.addEventListener('click', function () {
            removerItem(btn.dataset.id);
        });
    });

    // Checkout Form
    let formCheckout = document.getElementById('form-checkout');
    if (formCheckout) {
        formCheckout.querySelectorAll('input[name="forma_pagamento"]').forEach(radio => {
            radio.addEventListener('change', atualizarTotais);
        });

        let selectParcelas = document.getElementById('select-parcelas');
        if (selectParcelas) selectParcelas.addEventListener('change', atualizarTotais);

        atualizarTotais();
    }

    // Formulário do Carrinho (Página do Livro)
    let formCarrinho = document.getElementById('form-carrinho');
    if (formCarrinho) {
        formCarrinho.addEventListener('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    let contador = document.getElementById('cart-count');
                    if (contador) contador.innerText = data.total_itens;
                    mostrarToast('Livro adicionado ao carrinho!', 'success');
                } else {
                    mostrarToast(data.mensagem || 'Erro ao adicionar ao carrinho.', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarToast('Ocorreu um erro ao processar sua solicitação.', 'error');
            });
        });
    }
});