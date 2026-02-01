<?php
include_once 'includes/conexao.php';

// Validar se ID foi informado
if (!isset($_GET['id'])) {
    die('ID do carro não informado');
}

$id = (int) $_GET['id'];

// Buscar dados do carro
$stmt = $pdo->prepare("SELECT * FROM carros WHERE id = ?");
$stmt->execute([$id]);
$carro = $stmt->fetch(PDO::FETCH_ASSOC);

// Se carro não existir
if (!$carro) {
    die('Carro não encontrado');
}

// Coletar todas as imagens (capa + extras)
$todas_imagens = [];

// Adicionar imagem de capa como primeira imagem
if (!empty($carro['imagem_capa'])) {
    $todas_imagens[] = $carro['imagem_capa'];
}

// Caminhos seguros
$pasta_carro_relativa = 'uploads/carros/' . $carro['id'] . '/';
$pasta_carro_absoluta = __DIR__ . '/uploads/carros/' . $carro['id'] . '/';

// Verificar qual caminho existe e buscar imagens
if (is_dir($pasta_carro_absoluta)) {
    $arquivos = glob($pasta_carro_absoluta . '*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
    
    if ($arquivos && is_array($arquivos)) {
        usort($arquivos, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        foreach ($arquivos as $arquivo) {
            $todas_imagens[] = $pasta_carro_relativa . basename($arquivo);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($carro['titulo']) ?> - GTA Automóveis</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v6.3.0/css/all.css" rel="stylesheet" />
    
    <style>

        .spinner {
  --size: 30px;
  --first: #005bba;
  --second: #fed500;
  width: 100px;
  height: 100px;
  position: relative;
  animation: spin 3s linear infinite;
}

.spinner::before,
.spinner::after {
  content: "";
  width: var(--size);
  height: var(--size);
  border: 4px solid var(--first);
  border-top: 4px solid var(--second);
  border-radius: 50%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  animation: spinRing 1.5s ease-out infinite;
  box-shadow: 0 0 10px var(--first);
}

.spinner::before {
  filter: blur(10px);
}

@keyframes spinRing {
  0% {
    transform: translate(-50%, -50%) rotate(0deg);
  }
  100% {
    transform: translate(-50%, -50%) rotate(360deg);
  }
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Container que cobre a tela inteira */
.loader-wrapper {
  position: fixed; /* Fixa na tela */
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: #000000; /* Fundo preto (ou a cor que desejar) */
  display: flex;
  justify-content: center; /* Centraliza horizontalmente */
  align-items: center; /* Centraliza verticalmente */
  z-index: 9999; /* Garante que fique em cima de tudo */
  transition: opacity 0.5s ease, visibility 0.5s ease; /* Animação de sumir */
}

/* Classe para esconder o loader */
.loader-wrapper.hide {
  opacity: 0;
  visibility: hidden;
}

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background-image: url('img/fundo_cont.png');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
            padding: 80px 20px 40px;
        }

        /* HEADER & MENU */
        .top-bar { display: flex; align-items: center; justify-content: flex-start; gap: 30px; color: white; width: 100vw; left: 0; right: 0; margin-left: calc(-50vw + 50%); background: rgba(0, 0, 0, 0.4); -webkit-backdrop-filter: blur(20px); backdrop-filter: blur(20px); position: fixed; z-index: 1000; padding: 20px 25px; box-shadow: 0 25px 45px rgba(0, 0, 0, 0.4); top: 0; border-bottom: 3px solid #FFD700; }
        .top-bar .logo { height: 40px; width: auto; flex-shrink: 0; }
        .menu-bar { list-style-type: none; display: flex; align-items: center; gap: 20px; margin: 0; padding: 0; }
        .menu-bar li { float: none; }
        .menu-bar li a { display: block; color: white; text-align: center; padding: 8px 12px; text-decoration: none; transition: all 0.3s ease; white-space: nowrap; }
        .menu-bar li a:hover { color: #FFD700; text-shadow: 0px 0px 5px #FFD700; }
        
        /* CONTAINER & LAYOUT */
        .page-container { max-width: 1000px; margin: 0 auto; }
        .voltar { padding-top: 5%; display: flex; flex-direction: right; align-items: center; width: 100%; height: auto; }
        .btn-link-back { display: inline-flex; align-items: center; gap: 8px; color: #FFD700; text-decoration: none; font-weight: 600; margin-bottom: 30px; transition: all 0.3s ease; font-size: 1rem; }
        .btn-link-back:hover { color: #FFA500; transform: translateX(-5px); }
        .page-header { text-align: center; color: white; margin-bottom: 40px; animation: fadeInDown 0.6s ease-out; }
        .page-header h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
        
        /* CARD PRINCIPAL */
        .details-card { 
            background: rgba(255, 255, 255, 0.1); 
            backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.2); 
            border-radius: 20px; 
            padding: 40px; 
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); 
            margin-bottom: 30px; 
            animation: fadeInUp 0.6s ease-out; 
            display: grid; 
            grid-template-columns: 1fr; 
            gap: 40px; 
        }
        
        .image-section { margin-bottom: 0; height: 20%;}
        .main-image { width: 100%; height: auto; object-fit: contain; border-radius: 15px; border: 2px solid rgba(255, 215, 0, 0.3); box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3); transition: all 0.3s ease; }
        .main-image:hover { box-shadow: 0 12px 40px rgba(255, 215, 0, 0.3); border-color: rgba(255, 215, 0, 0.6); }

        .btn-ver-mais-fotos { display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 15px; padding: 12px 25px; background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #000; border: none; border-radius: 12px; font-size: 0.95rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s ease; width: 100%; }
        .btn-ver-mais-fotos:hover { background: linear-gradient(135deg, #FFA500 0%, #FFD700 100%); transform: translateY(-3px); box-shadow: 0 12px 30px rgba(255, 215, 0, 0.4); }
        .btn-ver-mais-fotos:active { transform: translateY(-1px); }

        /* MODAL */
        .modal-galeria { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(5px); z-index: 1000; align-items: center; justify-content: center; animation: fadeIn 0.3s ease; }
        .modal-galeria.ativo { display: flex; }
        .modal-conteudo { background: rgba(255, 255, 255, 0.95); border-radius: 20px; padding: 30px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4); animation: slideUp 0.3s ease; position: relative; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid rgba(255, 215, 0, 0.3); }
        .modal-header h2 { color: #000; font-size: 1.6rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 12px; }
        .modal-header h2 i { color: #FFD700; font-size: 1.8rem; }
        .modal-fechar { background: rgba(255, 215, 0, 0.2); border: 2px solid rgba(255, 215, 0, 0.5); color: #000; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
        .modal-fechar:hover { background: rgba(255, 215, 0, 0.4); border-color: rgba(255, 215, 0, 0.8); }
        .carousel-modal { margin-bottom: 0; }
        .carousel-modal .carousel-item { height: 450px; background: rgba(0, 0, 0, 0.05); }
        .carousel-modal .carousel-item img { border-radius: 12px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; }
        .carousel-modal .carousel-control-prev-icon, .carousel-modal .carousel-control-next-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath d='M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z' fill='%23FFD700'/%3e%3c/svg%3e"); }
        .carousel-modal .carousel-control-next-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath d='M4.646 1.646a.5.5 0 0 1 0 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z' fill='%23FFD700'/%3e%3c/svg%3e"); }
        .carousel-control-prev, .carousel-control-next { width: 60px; height: 60px; background: rgba(255, 215, 0, 0.2); border: 2px solid rgba(255, 215, 0, 0.5); border-radius: 50%; top: 50%; transform: translateY(-50%); opacity: 0.8; transition: all 0.3s ease; }
        .carousel-control-prev:hover, .carousel-control-next:hover { background: rgba(255, 215, 0, 0.4); border-color: rgba(255, 215, 0, 0.8); opacity: 1; }

        /* GRID INFO E DESCRIÇÃO */
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .info-card { background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 15px; padding: 25px; text-align: center; transition: all 0.3s ease; }
        .info-card:hover { background: rgba(255, 255, 255, 0.12); border-color: rgba(255, 215, 0, 0.4); transform: translateY(-5px); }
        .info-card .label { color: rgba(0, 0, 0, 0.7); font-size: 0.85rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 10px; }
        .info-card .value { color: #000; font-size: 1.8rem; font-weight: 800; margin-bottom: 5px; }
        .info-card .unit { color: rgba(0, 0, 0, 0.6); font-size: 0.9rem; font-weight: 500; }
        .info-card:nth-child(1) .value, .info-card:nth-child(2) .value, .info-card:nth-child(2) .unit { color: #fff; }
        .price-highlight { background: linear-gradient(135deg, rgba(255, 215, 0, 0.25), rgba(255, 165, 0, 0.15)); border: 2px solid rgba(255, 215, 0, 0.4); }
        .price-highlight .value { color: #FFD700; font-size: 2.2rem; }
        
        /* ESTILO DA DESCRIÇÃO */
        .description-section { background: rgba(255, 255, 255, 0.08); width: 100%; border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 15px; padding: 30px; margin-bottom: 30px; display: flex; flex-direction: column; }
        .description-section h2 { color: #000; font-size: 1.4rem; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
        .description-section h2 i { color: #FFD700; font-size: 1.6rem; }
        .description-text { color: #fff; font-size: 1rem; line-height: 1.8; font-weight: 500; font-family: 'Segoe UI', 'Helvetica Neue', -apple-system, sans-serif; flex-grow: 1; overflow-y: auto; }
        
        .action-buttons { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px; }
        .btn-action { padding: 16px 30px; border-radius: 12px; font-size: 1rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; transition: all 0.3s ease; border: none; display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none; }
        .btn-financiar { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #000; box-shadow: 0 8px 20px rgba(255, 215, 0, 0.3); }
        .btn-financiar:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(255, 215, 0, 0.5); background: linear-gradient(135deg, #FFA500 0%, #FFD700 100%); }
        .btn-voltar { background: rgba(255, 255, 255, 0.15); color: #000; border: 2px solid rgba(255, 255, 255, 0.3); }
        .btn-voltar:hover { background: rgba(255, 255, 255, 0.25); border-color: rgba(255, 255, 255, 0.5); color: #000; }
        .btn-whatsapp { background: linear-gradient(135deg, #25D366 0%, #20BA5C 100%); color: #fff; border: none; box-shadow: 0 8px 20px rgba(37, 211, 102, 0.3); }
        .btn-whatsapp:hover { background: linear-gradient(135deg, #20BA5C 0%, #25D366 100%); transform: translateY(-3px); box-shadow: 0 12px 30px rgba(37, 211, 102, 0.5); color: #fff; }
        .divider { height: 1px; background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent); margin: 40px 0; }
        
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        /* --- CORREÇÃO DO LAYOUT PARA PC (AQUI ESTÁ A MÁGICA) --- */
        /* --- CORREÇÃO DO LAYOUT PARA PC --- */
        @media (min-width: 1024px) {
            .details-card { 
                /* Define 2 colunas: Imagem (esq) e Info (dir) */
                grid-template-columns: 1fr 1.2fr; 
                /* Define linhas automáticas: Conteúdo Topo / Descrição / Botões */
                grid-template-rows: auto auto auto; 
                align-items: start;
            }

            .image-section { 
                grid-column: 1; 
                grid-row: 1; /* Fica na linha 1, coluna 1 */
                height: auto; /* Correção para a imagem não achatar */
            }

            .info-grid { 
                grid-column: 2; 
                grid-row: 1; /* Fica na linha 1, coluna 2 (ao lado da imagem) */
                margin-bottom: 0; 
            }

            .divider { display: none; }
            
            .description-section { 
                /* O PULO DO GATO: */
                grid-column: 1 / -1; /* Ocupa da coluna 1 até a última (-1) -> Largura Total */
                grid-row: 2; /* Vai para a linha de baixo */
                
                margin-top: 40px; 
                margin-bottom: 0; 
                min-height: auto;
                height: auto;
            }
            
            .action-buttons { 
                grid-column: 1 / -1; /* Botões também ocupam largura total abaixo de tudo */
                grid-row: 3; 
                margin-top: 30px; 
            }
        }

        @media (max-width: 1023px) {
            .details-card { grid-template-columns: 1fr; }
            .image-section { margin-bottom: 40px; }
            .divider { display: block; }
        }
        @media (max-width: 768px) { .page-container { padding: 0 15px; } .details-card { padding: 25px; } .page-header h1 { font-size: 1.8rem; } .image-section { width: 100%; } .info-grid { grid-template-columns: 1fr; gap: 15px; } .action-buttons { grid-template-columns: 1fr; gap: 15px; } .info-card .value { font-size: 1.5rem; } .description-section { padding: 20px; } .top-bar { padding: 12px 15px; gap: 20px; } .top-bar .logo { height: 32px; } .menu-bar { gap: 12px; } .menu-bar li a { padding: 6px 8px; font-size: 0.85rem; } body { padding: 70px 15px 30px; } }
        @media (max-width: 600px) { .top-bar { padding: 10px 12px; gap: 15px; } .top-bar .logo { height: 30px; } .menu-bar { gap: 8px; } .menu-bar li a { padding: 5px 6px; font-size: 0.80rem; } body { padding: 65px 12px 30px; } }
        @media (max-width: 480px) { body { padding: 60px 10px 30px; } .page-header h1 { font-size: 1.5rem; } .carousel-control-prev, .carousel-control-next { width: 45px; } .info-card .value { font-size: 1.3rem; } .top-bar { padding: 8px 10px; gap: 12px; } .top-bar .logo { height: 28px; } .menu-bar { gap: 6px; } .menu-bar li a { padding: 4px 5px; font-size: 0.70rem; } .page-container { padding: 0 10px; } }
    </style>
</head>
<body>

    <div class="loader-wrapper">
    <div class="spinner"></div>
  </div>

<header class="top-bar">
    <img src="img/topbar.png" class="logo">
    <ul class="menu-bar">   
        <li class="links1"><a href="home.php">Home</a></li>
        <li class="links"><a href="estoque.php">Estoque</a></li>
        <li class="links"><a href="financiamento.php">Financiamento</a></li>
        <li class="links"><a href="sobre.html">Sobre</a></li>
    </ul>
</header>

<main>
    <div class="page-container">

        <div class="voltar">
            <a href="estoque.php" class="btn-link-back">
                <i class="fas fa-arrow-left"></i> Voltar ao Estoque
            </a>
        </div>

        <div class="page-header">
            <h1><?= htmlspecialchars($carro['titulo']) ?></h1>
        </div>

        <div class="details-card">

            <div class="image-section">
                <?php if (!empty($carro['imagem_capa'])): ?>
                    <img src="<?= $carro['imagem_capa'] ?>" alt="<?= htmlspecialchars($carro['titulo']) ?>" class="main-image">
                <?php else: ?>
                    <img src="img/sem-foto.jpg" alt="Sem imagem" class="main-image">
                <?php endif; ?>
                
                <?php if (count($todas_imagens) > 1): ?>
                    <button class="btn-ver-mais-fotos" onclick="abrirModalGaleria()">
                        <i class="fas fa-images"></i> Ver Mais Fotos
                    </button>
                <?php endif; ?>
            </div>

            <div class="info-grid">
                
                <div class="info-card">
                    <div class="label"><i class="fas fa-calendar"></i> Ano</div>
                    <div class="value"><?= $carro['ano'] ?></div>
                </div>

                <div class="info-card">
                    <div class="label"><i class="fas fa-road"></i> KM Rodado</div>
                    <div class="value"><?= number_format($carro['km'], 0, ',', '.') ?></div>
                    <div class="unit">km</div>
                </div>

                <div class="info-card price-highlight">
                    <div class="label"><i class="fas fa-tag"></i> Preço</div>
                    <div class="value">R$ <?= number_format($carro['preco'], 2, ',', '.') ?></div>
                    <?php if (!empty($carro['preco_antigo']) && $carro['preco_antigo'] > $carro['preco']): ?>
                        <div class="unit" style="color: #FFD700;">
                            <i class="fas fa-arrow-down"></i> De R$ <?= number_format($carro['preco_antigo'], 2, ',', '.') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="divider"></div>

            <div class="description-section">
                <h2>
                    <i class="fas fa-info-circle"></i> Descrição do Veículo
                </h2>
                <div class="description-text">
                    <?= !empty($carro['descricao']) ? nl2br(htmlspecialchars($carro['descricao'])) : 'Sem descrição disponível.' ?>
                </div>
            </div>

            <div class="action-buttons">
                <a href="financiamento.php?id_carro=<?= $carro['id'] ?>" class="btn-action btn-financiar">
                    <i class="fas fa-handshake"></i> Financiar Este Carro
                </a>
                <a href="https://wa.me/551120350736?text=Olá! Vim atravéz do site da loja e Gostaria de mais informações sobre o <?= urlencode($carro['titulo']) ?>" target="_blank" class="btn-action btn-whatsapp">
                    <i class="fab fa-whatsapp"></i> Falar Conosco
                </a>
            </div>

        </div>

    </div>
</main>

<div class="modal-galeria" id="modalGaleria">
    <div class="modal-conteudo">
        <div class="modal-header">
            <h2><i class="fas fa-image"></i> Galeria de Fotos</h2>
            <button class="modal-fechar" onclick="fecharModalGaleria()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="carouselGaleria" class="carousel slide carousel-modal" data-bs-ride="false">
            <div class="carousel-inner">
                <?php foreach ($todas_imagens as $i => $img): ?>
                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($carro['titulo']) ?>" class="d-block">
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#carouselGaleria" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselGaleria" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Próxima</span>
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function abrirModalGaleria() {
        const modal = document.getElementById('modalGaleria');
        modal.classList.add('ativo');
        document.body.style.overflow = 'hidden';
    }

    function fecharModalGaleria() {
        const modal = document.getElementById('modalGaleria');
        modal.classList.remove('ativo');
        document.body.style.overflow = 'auto';
    }

    document.getElementById('modalGaleria').addEventListener('click', function(event) {
        if (event.target === this) {
            fecharModalGaleria();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            fecharModalGaleria();
        }
    });
</script>


<script>
    window.addEventListener("load", function() {
      const loader = document.querySelector(".loader-wrapper");
      
      setTimeout(function() {
        loader.classList.add("hide");
        
        loader.addEventListener("transitionend", function() {
            if (loader.parentNode) {
                loader.parentNode.removeChild(loader);
            }
        });
      }, 2000);
    });
  </script>

</body>
</html>