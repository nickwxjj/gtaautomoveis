<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/verifica_login.php';
include '../includes/conexao.php';

if ($_POST) {

    $titulo = $_POST['titulo'];
    $preco = $_POST['preco'];
    $preco_antigo = !empty($_POST['preco_antigo']) ? $_POST['preco_antigo'] : null;
    $ano = $_POST['ano'];
    $km = $_POST['km'];
    $descricao = $_POST['descricao'];

    // upload da imagem de capa
    $imagem_capa = null;
    if (!empty($_FILES['imagem_capa']['name'])) {
        $nomeArquivo = uniqid() . '_' . $_FILES['imagem_capa']['name'];
        move_uploaded_file(
            $_FILES['imagem_capa']['tmp_name'],
            '../uploads/' . $nomeArquivo
        );
        $imagem_capa = 'uploads/' . $nomeArquivo;
    }

    $stmt = $pdo->prepare("
        INSERT INTO carros 
        (titulo, preco, preco_antigo, ano, km, descricao, imagem_capa)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $titulo,
        $preco,
        $preco_antigo,
        $ano,
        $km,
        $descricao,
        $imagem_capa
    ]);

    // Obter o ID do carro inserido
    $id_carro = $pdo->lastInsertId();

    // Processar imagens extras
    if (!empty($_FILES['imagens']['name'][0])) {
        $pasta_carro = '../uploads/carros/' . $id_carro . '/';
        
        // Criar pasta se não existir
        if (!file_exists($pasta_carro)) {
            mkdir($pasta_carro, 0777, true);
        }

        // Salvar cada imagem
        foreach ($_FILES['imagens']['name'] as $key => $nome) {
            if (!empty($nome)) {
                $nomeArquivo = uniqid() . '_' . $nome;
                move_uploaded_file(
                    $_FILES['imagens']['tmp_name'][$key],
                    $pasta_carro . $nomeArquivo
                );
            }
        }
    }

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Carro - GTA Automóveis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v6.3.0/css/all.css" rel="stylesheet" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background-image: url('../img/fundo_cont.png');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
            padding: 80px 20px 40px;
        }

        /* Container Principal */
        .page-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Header da Página */
        .page-header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
            animation: fadeInDown 0.6s ease-out;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .page-header .subtitle {
            font-size: 1rem;
            opacity: 0.9;
            color: #FFD700;
        }

        /* Card Principal com Glassmorphism */
        .form-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
            animation: fadeInUp 0.6s ease-out;
        }

        .form-card h2 {
            color: #000;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .form-card h2 i {
            color: #FFD700;
            font-size: 1.8rem;
        }

        /* Divisor de seção */
        .form-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            margin: 30px 0;
        }

        /* Grupos de Inputs */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #000;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Inputs e Textareas */
        .form-control, 
        textarea.form-control {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 14px 18px;
            color: #000;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-control::placeholder,
        textarea.form-control::placeholder {
            color: rgba(0, 0, 0, 0.5);
        }

        .form-control:focus,
        textarea.form-control:focus {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.4);
            color: #000;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.2);
            outline: none;
        }

        /* Inputs de Preço */
        .price-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Inputs de Ano e KM */
        .spec-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Input File Customizado */
        .file-input-wrapper {
            position: relative;
            margin-bottom: 20px;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #000;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .file-input-label:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 215, 0, 0.5);
            color: #000;
        }

        .file-input-label i {
            font-size: 1.5rem;
            color: #FFD700;
        }

        input[type="file"] {
            display: none;
        }

        .file-name {
            display: block;
            margin-top: 8px;
            font-size: 0.85rem;
            color: rgba(0, 0, 0, 0.7);
            font-weight: 500;
        }

        /* Botões */
        .btn-submit {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #000;
            border: none;
            padding: 16px 40px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #FFA500 0%, #FFD700 100%);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(255, 215, 0, 0.4);
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        .btn-submit i {
            font-size: 1.2rem;
        }

        /* Grid para inputs lado a lado responsivo */
        @media (max-width: 768px) {
            .price-row,
            .spec-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .form-card {
                padding: 25px;
            }

            .page-header h1 {
                font-size: 1.8rem;
            }
        }

        /* Animações */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Barra de progresso para upload */
        .upload-feedback {
            color: #FFD700;
            font-size: 0.85rem;
            margin-top: 8px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .upload-feedback.show {
            opacity: 1;
        }

        /* Links e botões secundários */
        .btn-link-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #FFD700;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .btn-link-back:hover {
            color: #FFA500;
            transform: translateX(-5px);
        }

        .form-card.secondary {
            margin-top: 30px;
            opacity: 0.95;
        }

        /* Card com botão de submit */
        .form-card[style*="background: rgba(255, 215, 0, 0.15)"] {
            margin-top: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100px;
        }

        /* Suporte para múltiplos arquivos */
        .file-list {
            margin-top: 15px;
            font-size: 0.85rem;
            color: rgba(0, 0, 0, 0.7);
        }

        #preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #0f172a; /* Cor de fundo (Azul Escuro/Preto do seu tema) */
    z-index: 99999; /* Garante que fique acima de tudo */
    display: flex;
    justify-content: center;
    align-items: center;
    transition: opacity 0.5s ease, visibility 0.5s ease;
}

/* Classe que será adicionada via JS para esconder */
.loader-hide {
    opacity: 0;
    visibility: hidden;
}

/* =========================================
   SEU SPINNER (CSS ORIGINAL)
   ========================================= */
.spinner {
   --size: 30px;
   --first-block-clr: #005bba; /* Azul */
   --second-block-clr: #fed500; /* Amarelo */
   --clr: #111;
   width: 100px;
   height: 100px;
   position: relative;
}

.spinner::after,.spinner::before {
   box-sizing: border-box;
   position: absolute;
   content: "";
   width: var(--size);
   height: var(--size);
   top: 50%;
   animation: up 2.4s cubic-bezier(0, 0, 0.24, 1.21) infinite;
   left: 50%;
   background: var(--first-block-clr);
}

.spinner::after {
   background: var(--second-block-clr);
   top: calc(50% - var(--size));
   left: calc(50% - var(--size));
   animation: down 2.4s cubic-bezier(0, 0, 0.24, 1.21) infinite;
}

@keyframes down {
   0%, 100% { transform: none; }
   25% { transform: translateX(100%); }
   50% { transform: translateX(100%) translateY(100%); }
   75% { transform: translateY(100%); }
}

@keyframes up {
   0%, 100% { transform: none; }
   25% { transform: translateX(-100%); }
   50% { transform: translateX(-100%) translateY(-100%); }
   75% { transform: translateY(-100%); }
}

    </style>
</head>
<body>

    <div id="preloader">
    <div class="spinner"></div>
</div>

<div class="page-container">

    <a href="index.php" class="btn-link-back">
        <i class="fas fa-arrow-left"></i> Voltar ao Painel
    </a>

    <div class="page-header">
        <h1>Novo Veículo</h1>
        <p class="subtitle">Adicione um novo carro ao catálogo</p>
    </div>

    <!-- Formulário Principal - Único Form -->
    <form method="post" enctype="multipart/form-data">

        <!-- Card 1: Informações do Veículo -->
        <div class="form-card">
            <h2><i class="fas fa-car"></i> Informações do Veículo</h2>

            <div class="form-group">
                <label for="titulo">Título do Carro</label>
                <input type="text" name="titulo" id="titulo" class="form-control" placeholder="Ex: BMW 320i 2020" required>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição Completa</label>
                <textarea name="descricao" id="descricao" class="form-control" rows="4" placeholder="Descreva detalhes do veículo, condição, acessórios, etc..."></textarea>
            </div>

            <div class="form-divider"></div>

            <div class="form-group">
                <label>Preços</label>
                <div class="price-row">
                    <div>
                        <label for="preco" style="font-size: 0.85rem; text-transform: none;">Preço Atual</label>
                        <input type="number" name="preco" id="preco" class="form-control" placeholder="0.00" step="0.01" required>
                    </div>
                    <div>
                        <label for="preco_antigo" style="font-size: 0.85rem; text-transform: none;">Preço Anterior (opcional)</label>
                        <input type="number" name="preco_antigo" id="preco_antigo" class="form-control" placeholder="0.00" step="0.01">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Especificações</label>
                <div class="spec-row">
                    <div>
                        <label for="ano" style="font-size: 0.85rem; text-transform: none;">Ano</label>
                        <input type="number" name="ano" id="ano" class="form-control" placeholder="2020" required>
                    </div>
                    <div>
                        <label for="km" style="font-size: 0.85rem; text-transform: none;">KM Rodado</label>
                        <input type="number" name="km" id="km" class="form-control" placeholder="50000" required>
                    </div>
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="form-group">
                <label for="imagem_capa">Imagem de Capa</label>
                <div class="file-input-wrapper">
                    <label for="imagem_capa" class="file-input-label">
                        <i class="fas fa-image"></i>
                        <span>Selecione a imagem principal</span>
                    </label>
                    <input type="file" name="imagem_capa" id="imagem_capa" accept="image/*" required onchange="updateFileName(this, 'file-name-capa')">
                    <span class="file-name" id="file-name-capa"></span>
                </div>
            </div>
        </div>

        <!-- Card 2: Galeria de Imagens (dentro do mesmo form) -->
        <div class="form-card secondary">
            <h2><i class="fas fa-images"></i> Galeria de Imagens</h2>

            <div class="form-group">
                <label for="imagens">Outras Imagens</label>
                <div class="file-input-wrapper">
                    <label for="imagens" class="file-input-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Selecione múltiplas imagens</span>
                    </label>
                    <input type="file" name="imagens[]" id="imagens" accept="image/*" multiple onchange="updateFileCount(this)">
                    <span class="file-name" id="file-count"></span>
                </div>
            </div>
        </div>

        <!-- Botão de Submit Unificado -->
        <div class="form-card" style="background: rgba(255, 215, 0, 0.15); border-color: rgba(255, 215, 0, 0.4); padding: 30px; text-align: center;">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Salvar Veículo
            </button>
        </div>

    </form>

</div>

<script>
    function updateFileName(input, elementId) {
        const fileName = input.files[0]?.name || '';
        document.getElementById(elementId).textContent = fileName ? `✓ ${fileName}` : '';
    }

    function updateFileCount(input) {
        const count = input.files.length;
        const element = document.getElementById('file-count');
        if (count > 0) {
            element.textContent = `✓ ${count} arquivo(s) selecionado(s)`;
        } else {
            element.textContent = '';
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    window.addEventListener("load", function () {
        const loader = document.getElementById("preloader");
        
        // Adiciona a classe que torna transparente
        loader.classList.add("loader-hide");
        
        // Remove o elemento do HTML totalmente após a transição (0.5s) para não bloquear cliques
        loader.addEventListener("transitionend", function() {
            document.body.removeChild(loader);
        });
    });
</script>

</body>
</html>