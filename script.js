// theme toggle
const body = document.body;
const themeToggle = document.querySelector('#themeToggle');
themeToggle.addEventListener('click', () => {
    body.classList.toggle('dark');
    themeToggle.textContent = body.classList.contains('dark') ? '☀️' : '🌙';
    console.log('clicou')
    // try { localStorage.setItem('pv_theme', body.classList.contains('dark') ? 'dark' : 'light'); } catch (e) { }
});
// restore theme
try {
    const t = localStorage.getItem('pv_theme'); if (t === 'dark') { body.classList.add('dark'); themeToggle.textContent = '☀️' }
} catch (e) { }


// ============ CARRINHO ============
let carrinho = [];

const carrinhoDiv = document.getElementById("carrinhoLateral");
const conteudoCarrinho = document.getElementById("conteudoCarrinho");
const totalCarrinho = document.getElementById("totalCarrinho");
const addToCart = document.getElementById("addToCart");
const openCart = document.getElementById("openCart");
const cartCount = document.getElementById("cartCount");

// Pega nome e preço do produto
const nome = document.querySelector('.descricao-livro h2').textContent;
const preco = Number(document.querySelector('#preco').textContent);

// Garantir que exista botão fechar apenas UMA vez
let btnFecharCriado = false;

// Abrir carrinho
openCart.addEventListener("click", () => {
    carrinhoDiv.classList.add("ativo");

    if (!btnFecharCriado) {
        const btnFechar = document.createElement('button');
        btnFechar.innerText = "X";
        btnFechar.classList.add('fechar');
        carrinhoDiv.prepend(btnFechar);
        btnFecharCriado = true;

        btnFechar.addEventListener("click", () => {
            carrinhoDiv.classList.remove("ativo");
        });
    }
});

// Adicionar produto
addToCart.addEventListener("click", () => {
    const quantidade = Number(document.querySelector('#qtd').value);

    // Impede adicionar sem quantidade válida
    if (!quantidade || quantidade < 1) {
        alert("Digite uma quantidade válida!");
        return;
    }

    const precoTotal = preco * quantidade;

    const livroComprado = { nome, preco, quantidade, precoTotal };
    carrinho.push(livroComprado);

    atualizarCarrinho();
});

// Atualizar carrinho
function atualizarCarrinho() {
    conteudoCarrinho.innerHTML = "";
    let total = 0;

    carrinho.forEach(item => {
        total += item.precoTotal;
        const div = document.createElement("div");

        div.innerHTML = `
            <p><strong>${item.nome}</strong></p>
            <p>Quantidade: ${item.quantidade}</p>
            <p>Preço unitário: R$ ${item.preco.toFixed(2)}</p>
            <p><strong>Total: R$ ${item.precoTotal.toFixed(2)}</strong></p>
            <hr>
        `;
        conteudoCarrinho.appendChild(div);
    });

    cartCount.textContent = carrinho.length;
    totalCarrinho.textContent = total.toFixed(2);
}

// FINALIZAR COMPRA
document.getElementById("finalizarCompra").addEventListener("click", () => {
    if (carrinho.length === 0) {
        alert("Seu carrinho está vazio!");
        return;
    }

    alert("Compra finalizada! Obrigado por comprar na Páginas Vivas 📚✨");
    carrinho = [];
    atualizarCarrinho();
});
