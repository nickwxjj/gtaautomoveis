<?php
include_once '../includes/verifica_login.php';
include_once '../includes/conexao.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

/* Busca a imagem de capa correta */
$stmt = $pdo->prepare("SELECT imagem_capa FROM carros WHERE id = ?");
$stmt->execute([$id]);
$carro = $stmt->fetch(PDO::FETCH_ASSOC);

/* Remove a imagem do servidor (se existir) */
if ($carro && !empty($carro['imagem_capa'])) {
    $caminho = '../' . $carro['imagem_capa'];
    if (file_exists($caminho)) {
        unlink($caminho);
    }
}

/* Exclui o carro */
$stmt = $pdo->prepare("DELETE FROM carros WHERE id = ?");
$stmt->execute([$id]);

header("Location: index.php");
exit;

