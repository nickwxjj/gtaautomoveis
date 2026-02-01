<?php
// Script de debug para verificar se as imagens estão sendo salvas corretamente

include '../includes/verifica_login.php';
include '../includes/conexao.php';

// Listar todos os carros
$stmt = $pdo->prepare("SELECT id, titulo FROM carros ORDER BY id DESC LIMIT 5");
$stmt->execute();
$carros = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Imagens dos Carros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: #f5f5f5;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .carro-debug {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .carro-debug h3 {
            color: #FFD700;
            margin-bottom: 15px;
        }
        .imagem-thumb {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            margin: 5px;
            cursor: pointer;
        }
        .imagem-thumb:hover {
            opacity: 0.8;
        }
        .image-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .info-text {
            color: #666;
            font-size: 0.9rem;
        }
        .not-found {
            color: #d32f2f;
            font-weight: bold;
        }
        .found {
            color: #388e3c;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 style="color: #FFD700; margin-bottom: 30px;">🔍 Debug - Imagens dos Carros</h1>

    <?php foreach ($carros as $carro): ?>
        <div class="carro-debug">
            <h3>ID: <?= $carro['id'] ?> - <?= htmlspecialchars($carro['titulo']) ?></h3>
            
            <!-- Imagem de Capa -->
            <p><strong>Imagem de Capa:</strong></p>
            <p class="info-text">
                <?php
                    $stmt = $pdo->prepare("SELECT imagem_capa FROM carros WHERE id = ?");
                    $stmt->execute([$carro['id']]);
                    $carro_data = $stmt->fetch();
                    
                    if (!empty($carro_data['imagem_capa'])) {
                        $imagem_path = $carro_data['imagem_capa'];
                        $full_path = $_SERVER['DOCUMENT_ROOT'] . '/gta/' . $imagem_path;
                        if (file_exists($full_path)) {
                            echo '<span class="found">✓ Encontrada:</span> ' . htmlspecialchars($imagem_path);
                        } else {
                            echo '<span class="not-found">✗ NÃO ENCONTRADA:</span> ' . htmlspecialchars($imagem_path);
                        }
                    } else {
                        echo '<span class="not-found">✗ Nenhuma imagem de capa</span>';
                    }
                ?>
            </p>

            <!-- Imagens da Galeria -->
            <p><strong>Imagens da Galeria:</strong></p>
            <?php
                $pasta_absoluta = $_SERVER['DOCUMENT_ROOT'] . '/gta/uploads/carros/' . $carro['id'] . '/';
                $pasta_relativa = 'uploads/carros/' . $carro['id'] . '/';
                
                if (is_dir($pasta_absoluta)) {
                    $arquivos = glob($pasta_absoluta . '*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
                    
                    if ($arquivos && count($arquivos) > 0) {
                        echo '<p class="info-text"><span class="found">✓ ' . count($arquivos) . ' imagem(ns) encontrada(s)</span></p>';
                        echo '<div class="image-grid">';
                        
                        foreach ($arquivos as $arquivo) {
                            $relativo = $pasta_relativa . basename($arquivo);
                            echo '<img src="' . htmlspecialchars($relativo) . '" alt="Imagem" class="imagem-thumb" title="' . basename($arquivo) . '">';
                        }
                        
                        echo '</div>';
                    } else {
                        echo '<p class="info-text"><span class="not-found">✗ Nenhuma imagem na pasta</span></p>';
                    }
                } else {
                    echo '<p class="info-text"><span class="not-found">✗ Pasta não existe:</span> ' . htmlspecialchars($pasta_absoluta) . '</p>';
                }
            ?>
            
            <hr>
            <p class="info-text">
                <strong>Caminho da pasta:</strong> <?= htmlspecialchars($pasta_absoluta) ?><br>
                <strong>Carrossel deve exibir:</strong> <?php 
                    $stmt = $pdo->prepare("SELECT imagem_capa FROM carros WHERE id = ?");
                    $stmt->execute([$carro['id']]);
                    $carro_data = $stmt->fetch();
                    
                    $total = 1; // capa
                    $pasta_abs = $_SERVER['DOCUMENT_ROOT'] . '/gta/uploads/carros/' . $carro['id'] . '/';
                    if (is_dir($pasta_abs)) {
                        $arquivos = glob($pasta_abs . '*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
                        if ($arquivos) {
                            $total += count($arquivos);
                        }
                    }
                    echo $total . ' imagem(ns)';
                ?>
            </p>

            <a href="../detalhes_carro.php?id=<?= $carro['id'] ?>" class="btn btn-primary btn-sm" target="_blank">
                Ver Detalhes →
            </a>
        </div>
    <?php endforeach; ?>

</div>

</body>
</html>
