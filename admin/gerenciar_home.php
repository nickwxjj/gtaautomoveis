<?php
include_once '../includes/verifica_login.php';
include_once '../includes/conexao.php';

// Buscar carros designados para o home
$home_carros = $pdo->query("
    SELECT hc.id, hc.id_carro, hc.posicao, c.titulo, c.imagem_capa 
    FROM home_carros hc
    JOIN carros c ON c.id = hc.id_carro
    ORDER BY hc.posicao ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Processar remoção
if (isset($_GET['remover'])) {
    $id_remove = (int) $_GET['remover'];
    $stmt = $pdo->prepare("DELETE FROM home_carros WHERE id = ?");
    $stmt->execute([$id_remove]);
    header("Location: index.php");
    exit;
}

// Processar adição
if (isset($_POST['adicionar_home'])) {
    $id_carro = (int) $_POST['id_carro'];
    $proxima_posicao = count($home_carros) + 1;
    
    $stmt = $pdo->prepare("INSERT INTO home_carros (id_carro, posicao) VALUES (?, ?)");
    $stmt->execute([$id_carro, $proxima_posicao]);
    
    header("Location: index.php");
    exit;
}

// Processar edição de título
if (isset($_POST['editar_titulo'])) {
    $id_carro = (int) $_POST['id_carro'];
    $novo_titulo = $_POST['titulo'];
    
    $stmt = $pdo->prepare("UPDATE carros SET titulo = ? WHERE id = ?");
    $stmt->execute([$novo_titulo, $id_carro]);
    
    header("Location: index.php");
    exit;
}

// Processar edição de imagem
if (isset($_POST['editar_imagem'])) {
    $id_carro = (int) $_POST['id_carro'];
    
    if (!empty($_FILES['imagem_capa']['name'])) {
        $nomeArquivo = uniqid() . '_' . $_FILES['imagem_capa']['name'];
        move_uploaded_file(
            $_FILES['imagem_capa']['tmp_name'],
            '../uploads/' . $nomeArquivo
        );
        $imagem_capa = 'uploads/' . $nomeArquivo;
        
        $stmt = $pdo->prepare("UPDATE carros SET imagem_capa = ? WHERE id = ?");
        $stmt->execute([$imagem_capa, $id_carro]);
    }
    
    header("Location: index.php");
    exit;
}

// Buscar carros disponíveis para adicionar (que não estão no home)
$carros_disponiveis = $pdo->query("
    SELECT id, titulo FROM carros 
    WHERE id NOT IN (SELECT id_carro FROM home_carros)
    ORDER BY titulo ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
