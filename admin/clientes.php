<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<?php
include '../includes/verifica_login.php';
include '../includes/conexao.php';


?>
   
   <?php
include 'includes/conexao.php';

$clientes = $pdo->query("SELECT * FROM clientes_financiamento")->fetchAll();
?>

<h2>Clientes cadastrados para financiamento</h2>

<?php if (count($clientes) == 0): ?>
    <p>Nenhum cliente cadastrado.</p>
<?php else: ?>
<table border="1">
    <tr>
        <th>Nome</th>
        <th>Telefone</th>
        <th>CPF</th>
        <th>Veículo</th>
    </tr>

    <?php foreach ($clientes as $c): ?>
    <tr>
        <td><?= $c['nome'] ?></td>
        <td><?= $c['telefone'] ?></td>
        <td><?= $c['cpf'] ?></td>
        <td><?= $c['veiculo'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>