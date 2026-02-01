<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/verifica_login.php';
include '../includes/conexao.php';

if (!isset($_GET['id'])) {
    die('ID do carro não informado');
}

$id = (int) $_GET['id'];

// Buscar dados do carro
$stmt = $pdo->prepare("SELECT * FROM carros WHERE id = ?");
$stmt->execute([$id]);
$carro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$carro) {
    die('Carro não encontrado');
}

// Processar POST (atualização)
if ($_POST) {
    $titulo = $_POST['titulo'];
    $preco = $_POST['preco'];
    $preco_antigo = !empty($_POST['preco_antigo']) ? $_POST['preco_antigo'] : null;
    $ano = $_POST['ano'];
    $km = $_POST['km'];
    $descricao = $_POST['descricao'];

    // Manter imagem de capa atual ou fazer upload da nova
    $imagem_capa = $carro['imagem_capa'];
    if (!empty($_FILES['imagem_capa']['name'])) {
        $nomeArquivo = uniqid() . '_' . $_FILES['imagem_capa']['name'];
        move_uploaded_file(
            $_FILES['imagem_capa']['tmp_name'],
            '../uploads/' . $nomeArquivo
        );
        $imagem_capa = 'uploads/' . $nomeArquivo;
    }

    // Processar imagens da galeria
    if (!empty($_FILES['imagens']['name'][0])) {
        // Criar pasta para este carro se não existir
        $pasta_carro = '../uploads/carros/' . $id;
        if (!file_exists($pasta_carro)) {
            mkdir($pasta_carro, 0777, true);
        }

        // Upload das imagens extras
        foreach ($_FILES['imagens']['name'] as $key => $nome_arquivo) {
            if (!empty($nome_arquivo)) {
                $nome_unico = uniqid() . '_' . $nome_arquivo;
                move_uploaded_file(
                    $_FILES['imagens']['tmp_name'][$key],
                    $pasta_carro . '/' . $nome_unico
                );
            }
        }
    }

    // Atualizar dados do carro
    $stmt = $pdo->prepare("
        UPDATE carros SET
            titulo = ?,
            preco = ?,
            preco_antigo = ?,
            ano = ?,
            km = ?,
            descricao = ?,
            imagem_capa = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $titulo,
        $preco,
        $preco_antigo,
        $ano,
        $km,
        $descricao,
        $imagem_capa,
        $id
    ]);

    echo "<script>
        alert('Carro atualizado com sucesso!');
        window.location.href = 'index.php';
    </script>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Veículo - GTA Automóveis</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v6.3.0/css/all.css" rel="stylesheet" />
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', 'Segoe UI', sans-serif; background-image: url('../img/fundo_cont.png'); background-size: cover; background-attachment: fixed; background-position: center; min-height: 100vh; padding: 80px 20px 40px; }
        .page-container { max-width: 900px; margin: 0 auto; }
        .page-header { text-align: center; color: white; margin-bottom: 40px; animation: fadeInDown 0.6s ease-out; }
        .page-header h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .page-header .subtitle { font-size: 1rem; opacity: 0.9; color: #FFD700; }
        .form-card { background: rgba(255,255,255,0.1); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; padding: 40px; box-shadow: 0 8px 32px rgba(0,0,0,0.2); margin-bottom: 30px; animation: fadeInUp 0.6s ease-out; }
        .form-card h2 { color: #000; font-size: 1.5rem; font-weight: 700; margin-bottom: 30px; display: flex; align-items: center; gap: 15px; }
        .form-card h2 i { color: #FFD700; font-size: 1.8rem; }
        .form-divider { height: 1px; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent); margin: 30px 0; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #000; font-weight: 600; margin-bottom: 10px; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control, textarea.form-control { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; padding: 14px 18px; color: #000; font-size: 0.95rem; transition: all 0.3s ease; backdrop-filter: blur(10px); }
        .form-control:focus, textarea.form-control:focus { background: rgba(255,255,255,0.18); border-color: rgba(255,255,255,0.4); color: #000; box-shadow: 0 0 20px rgba(255,215,0,0.2); outline: none; }
        .price-row, .spec-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .image-preview-section { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; padding: 20px; margin-bottom: 30px; }
        .current-image { border-radius: 12px; border: 2px solid rgba(255,215,0,0.3); box-shadow: 0 8px 20px rgba(0,0,0,0.2); max-width: 200px; height: auto; transition: all 0.3s ease; }
        .file-input-wrapper { position: relative; margin-bottom: 20px; }
        .file-input-label { display:flex; align-items:center; justify-content:center; gap:12px; padding:30px; background: rgba(255,255,255,0.08); border:2px dashed rgba(255,255,255,0.3); border-radius:12px; cursor:pointer; transition:all 0.3s ease; color:#000; font-weight:600; text-transform:uppercase; font-size:0.9rem }
        input[type="file"] { display:none; }
        .file-name { display:block; margin-top:8px; font-size:0.85rem; color:rgba(0,0,0,0.7); font-weight:500; }
        .btn-submit { background: linear-gradient(135deg,#FFD700 0%,#FFA500 100%); color:#000; border:none; padding:16px 40px; border-radius:12px; font-size:1rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; cursor:pointer; transition:all 0.3s ease; width:100%; display:flex; align-items:center; justify-content:center; gap:10px; margin-top:20px }
        .btn-link-back { display:inline-flex; align-items:center; gap:8px; color:#FFD700; text-decoration:none; font-weight:600; margin-bottom:20px }
        @media (max-width:768px){ .price-row,.spec-row{grid-template-columns:1fr; gap:15px} .form-card{padding:25px} .page-header h1{font-size:1.8rem} }
        @keyframes fadeInDown{ from{opacity:0; transform:translateY(-30px)} to{opacity:1; transform:translateY(0)} }
        @keyframes fadeInUp{ from{opacity:0; transform:translateY(30px)} to{opacity:1; transform:translateY(0)} }

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
        <h1>Editar Veículo</h1>
        <p class="subtitle">Atualize as informações do carro</p>
    </div>

    <form method="post" enctype="multipart/form-data">

        <div class="form-card">
            <h2><i class="fas fa-car"></i> Informações do Veículo</h2>

            <div class="form-group">
                <label for="titulo">Título do Carro</label>
                <input type="text" name="titulo" id="titulo" class="form-control" value="<?= htmlspecialchars($carro['titulo']) ?>" required>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição Completa</label>
                <textarea name="descricao" id="descricao" class="form-control" rows="4"><?= htmlspecialchars($carro['descricao']) ?></textarea>
            </div>

            <div class="form-divider"></div>

            <div class="form-group">
                <label>Preços</label>
                <div class="price-row">
                    <div>
                        <label for="preco" style="font-size: 0.85rem; text-transform: none;">Preço Atual</label>
                        <input type="number" name="preco" id="preco" class="form-control" value="<?= $carro['preco'] ?>" step="0.01" required>
                    </div>
                    <div>
                        <label for="preco_antigo" style="font-size: 0.85rem; text-transform: none;">Preço Anterior (opcional)</label>
                        <input type="number" name="preco_antigo" id="preco_antigo" class="form-control" value="<?= $carro['preco_antigo'] ?? '' ?>" step="0.01">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Especificações</label>
                <div class="spec-row">
                    <div>
                        <label for="ano" style="font-size: 0.85rem; text-transform: none;">Ano</label>
                        <input type="number" name="ano" id="ano" class="form-control" value="<?= $carro['ano'] ?>" required>
                    </div>
                    <div>
                        <label for="km" style="font-size: 0.85rem; text-transform: none;">KM Rodado</label>
                        <input type="number" name="km" id="km" class="form-control" value="<?= $carro['km'] ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="image-preview-section">
                <h3><i class="fas fa-image"></i> Imagem Atual</h3>
                <div class="current-image-wrapper">
                    <?php if (!empty($carro['imagem_capa'])): ?>
                        <img src="../<?= $carro['imagem_capa'] ?>" alt="Imagem atual" class="current-image">
                        <div class="image-info">
                            <p style="margin: 0 0 5px 0;"><strong>Imagem em uso</strong></p>
                            <p style="margin: 0; font-size: 0.85rem; opacity: 0.8;"><?= basename($carro['imagem_capa']) ?></p>
                        </div>
                    <?php else: ?>
                        <p class="image-info">Nenhuma imagem cadastrada</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="imagem_capa">Substituir Imagem Principal</label>
                <div class="file-input-wrapper">
                    <label for="imagem_capa" class="file-input-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Selecione uma nova imagem (opcional)</span>
                    </label>
                    <input type="file" name="imagem_capa" id="imagem_capa" accept="image/*" onchange="updateFileName(this, 'file-name-capa')">
                    <span class="file-name" id="file-name-capa"></span>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h2><i class="fas fa-images"></i> Galeria de Imagens</h2>

            <div class="form-group">
                <label for="imagens">Adicionar Imagens Extras</label>
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

        <button type="submit" class="form-card btn-submit" style="display:flex; align-items:center; justify-content:center;">
            <i class="fas fa-save"></i> Salvar Alterações
        </button>

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
