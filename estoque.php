<?php
include_once 'includes/conexao.php';

$stmt = $pdo->query("
    SELECT 
        id,
        titulo,
        ano,
        preco,
        imagem_capa
    FROM carros
");

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Garagem</title>

    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="css/estoque.css" rel="stylesheet" />
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

<section class="py-5">
    <div class="container px-4 px-lg-5 mt-5">
        <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">

            <?php while ($carro = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

                <div class="col mb-5">
                    <div class="card h-100">

                        <!-- Imagem -->
                        <img 
                            class="card-img-top" 
                            src="<?= !empty($carro['imagem_capa']) ? $carro['imagem_capa'] : 'img/sem-foto.jpg' ?>" 
                            alt="<?= htmlspecialchars($carro['titulo']) ?>"
                        >

                        <div class="card-body p-4" style="background-color: aliceblue;">
                            <div class="text-center">

                                <h5 class="fw-bolder" style="color: black;">
                                    <?= htmlspecialchars($carro['titulo']) ?><br>
                                    <?= $carro['ano'] ?>
                                </h5>

                                <p>
                                    R$ <?= number_format($carro['preco'], 2, ',', '.') ?>
                                </p>

                            </div>
                        </div>

                        <div class="card-footer p-4 pt-0 border-top-0 bg-white">
                            <div class="text-center">
                                <a 
                                    class="btn btn-outline-dark mt-auto"
                                    href="detalhes_carro.php?id=<?= $carro['id'] ?>"
                                >
                                    Ver Mais
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

            <?php } ?>

        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    window.addEventListener("load", function() {
      // Seleciona o container do loader
      const loader = document.querySelector(".loader-wrapper");
      
      // Aguarda 2 segundos antes de desaparecer
      setTimeout(function() {
        // Adiciona a classe que faz o loader desaparecer
        loader.classList.add("hide");
        
        // Remove do HTML após a animação
        loader.addEventListener("transitionend", function() {
            if (loader.parentNode) {
                loader.parentNode.removeChild(loader);
            }
        });
      }, 2000); // 2 segundos de delay
    });
  </script>

</body>
</html>
