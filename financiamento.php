<?php
include_once('config.php');

// Pega o id_carro da URL para popular o hidden
$id_carro = isset($_GET['id_carro']) ? intval($_GET['id_carro']) : 0;

// Quando o formulário é enviado
if(isset($_POST['submit'])) {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $cpf = $_POST['cpf'];
    $data_nasc = $_POST['data_nasc'];
    $id_carro = isset($_POST['id_carro']) ? intval($_POST['id_carro']) : 0;

    $sql = "INSERT INTO clientes(id_carro, nome, telefone, cpf, data_nasc) 
            VALUES ('$id_carro', '$nome', '$telefone', '$cpf', '$data_nasc')";

    if(mysqli_query($conexao, $sql)){
        echo "<script>alert('Cadastro realizado com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao cadastrar.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulação de Financiamento</title>
    <link rel="wensite icon" type="png" href="img/topbar.png">
    <link rel="stylesheet" href="css/finan.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="loader-wrapper">
        <div class="spinner"></div>
    </div>

    <header class="top-bar">
        <img src="img/topbar.png" alt="logotipo" class="logo">
     <ul class="menu-bar">   
    <li class="links1"><a href="home.php">Home</a></li>
    <li class="links"><a href="estoque.php">Estoque</a></li>
    <li class="links"><a href="financiamento.php">Financiamento</a></li>
    <li class="links"><a href="sobre.html">Sobre</a></li>
    </ul>
    </header>

<div class="container">
  <div class="form-container"z>
    <p class="title">Simulação</p>
    <form class="form" action="financiamento.php" method="POST">
  <input type="hidden" name="id_carro" value="<?= $id_carro ?>">
      <div class="input-group">

      <input type="hidden" name="id_carro" value="<?= $id_carro ?>">

        <label for="name">Nome Completo</label>
        <input placeholder="" id="nome" name="nome" type="text" />
      </div>
      <div class="input-group">
        <label for="phone">Whatsapp</label>
        <input placeholder="" id="telefone" name="telefone" type="phone" />
        <label for="cpf">CPF:</label>
<input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" oninput="mascaraCPF(this)">

<script>
function mascaraCPF(campo) {
  campo.value = campo.value
    .replace(/\D/g, '') // tira tudo que não for número
    .replace(/(\d{3})(\d)/, '$1.$2') // coloca o primeiro ponto
    .replace(/(\d{3})(\d)/, '$1.$2') // coloca o segundo ponto
    .replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // coloca o traço
}
</script>


        <label for="data_nasc">Data de Nascimento</label>
<input type="date" id="data_nasci" name="data_nasc" required />
      </div>
      <button style="margin-top:6%;" class="sign" type="submit" name="submit">Enviar</button>

    </form>
    <div class="social-message">
      <div class="line"></div>
      <p class="message">Mande seus dados em formulario para fazermos sua simulação sem compromisso!!</p>
      <div class="line"></div>
    </div>
  </div>
</div>

            
    </div>
    
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