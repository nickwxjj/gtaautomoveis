<?php
session_start();
include_once __DIR__ . '/../includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recebe e sanitiza os dados
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha = $_POST['senha'];

    if (isset($pdo)) {
        // 2. Busca a linha que tenha EXATAMENTE esse usuário
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE usuario = :usuario LIMIT 1");
        $stmt->bindValue(':usuario', $usuario);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Se achou o usuário, verifica se a senha bate com o hash do banco
        if ($admin && password_verify($senha, $admin['senha'])) {
            $_SESSION['admin'] = true;
            $_SESSION['admin_nome'] = $admin['usuario']; // Opcional: salva o nome na sessão
            header("Location: index.php");
            exit;
        } else {
            $erro = "Usuário ou senha incorretos";
        }
    } else {
        $erro = "Erro de conexão com o banco de dados.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GTA Admin</title> 
    
    <style>
    /* Mensagem de erro */
    .erro-msg {
        color: #ff4444;
        margin-top: 1rem;
        font-weight: 600;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }

    html, body {
        height: 100%;
        margin: 0;
        background-image: url('../img/fundo_login.jpeg');
        background-attachment: fixed;
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow: hidden; /* Evita barras de rolagem indesejadas */
    }

    * {
        box-sizing: border-box; /* Garante que padding não aumente a largura total */
    }

    .container {
        display: flex;
        height: 100%;
        /* MUDANÇA 1: Joga tudo para o FINAL (Direita) */
        justify-content: right; 
        align-items: center;
    }

    .form-container {
        /* Aumentei um pouco para caber os inputs confortavelmente */
        width: 550px; 
        height: 100%;
        
        background-color: rgba(0, 0, 0, 0.6); 
        backdrop-filter: blur(25px);
        
        /* Sombra projetada para a ESQUERDA (negativo) */
        box-shadow: -5px 0 30px rgba(0, 0, 0, 0.5); 
        
        /* Borda na esquerda para destacar do fundo */
        border-left: 1px solid rgba(255, 255, 255, 0.1);
        border-right: none;
        
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 1.5rem;
        color: #fff;
    }

    .logo {
        /* Ajuste de tamanho para não quebrar em telas menores */
        max-width: 90%;
        height: auto; 
        padding-top: 0.5rem;
    }

    .inputs-area {
            width: 100%;
            margin-top: 4rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem; /* Espaço entre os inputs */
            align-items: center;
        }

        .input-group {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center; 
        }

        .input-group label {
            width: 350px; /* Alinhado com a largura do input */
            text-align: left;
            margin-bottom: 5px;
            font-weight: 600;
            color: #ddd;
            font-size: 0.9rem;
        }

        .input-group input {
            width: 350px; 
            border-radius: 30px;
            outline: 0;
            padding: 0.8rem 1.2rem;
            
            background-color: rgba(0, 0, 0, 0); 
            backdrop-filter: blur(10px);
            border: 1px solid rgba(61, 106, 255, 0.7);
            backdrop-filter: blur(15px);
            
            color: #fff; 
            caret-color: #fff;
            text-align: center; /* Digitação centralizada */
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .input-group input:focus {
            border-color: rgb(61, 106, 255); /* Realce ao clicar */
        }

        /* Estilo específico para Senha (bolinhas mais espaçadas) */
        .input-group input[type="password"] {
            letter-spacing: 3px;
            font-weight: bold;
        }
        
        /* Placeholder mais suave */
        .input-group input::placeholder {
            color: #bbb;
            opacity: 0.7;
            font-weight: normal;
            letter-spacing: normal;
        }

        .button {
            display: flex;
            justify-content: center;   
            align-items: center;
            position: relative;
        }

    /* From Uiverse.io by mrhyddenn */ 
.sign {
    width: 100%;
    margin-top: 2rem;
  position: relative;
  padding: 10px 20px;
  border-radius: 7px;
  border: 1px solid rgb(61, 106, 255);
  font-size: 14px;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 2px;
  background: transparent;
  color: #fff;
  overflow: hidden;
  box-shadow: 0 0 0 0 transparent;
  -webkit-transition: all 0.2s ease-in;
  -moz-transition: all 0.2s ease-in;
  transition: all 0.2s ease-in;
}

.sign:hover {
  background: rgb(61, 106, 255);
  box-shadow: 0 0 30px 5px rgba(0, 142, 236, 0.815);
  -webkit-transition: all 0.2s ease-out;
  -moz-transition: all 0.2s ease-out;
  transition: all 0.2s ease-out;
}

.sign:hover::before {
  -webkit-animation: sh02 0.5s 0s linear;
  -moz-animation: sh02 0.5s 0s linear;
  animation: sh02 0.5s 0s linear;
}

.sign::before {
  content: '';
  display: block;
  width: 0px;
  height: 86%;
  position: absolute;
  top: 7%;
  left: 0%;
  opacity: 0;
  background: #fff;
  box-shadow: 0 0 50px 30px #fff;
  -webkit-transform: skewX(-20deg);
  -moz-transform: skewX(-20deg);
  -ms-transform: skewX(-20deg);
  -o-transform: skewX(-20deg);
  transform: skewX(-20deg);
}

@keyframes sh02 {
  from {
    opacity: 0;
    left: 0%;
  }

  50% {
    opacity: 1;
  }

  to {
    opacity: 0;
    left: 100%;
  }
}

button:active {
  box-shadow: 0 0 0 0 transparent;
  -webkit-transition: box-shadow 0.2s ease-in;
  -moz-transition: box-shadow 0.2s ease-in;
  transition: box-shadow 0.2s ease-in;
}


    .logout {
        display: inline-block;
        margin-top: 1rem;
        text-decoration: none;
        color: #000;
        font-weight: bold;
    }

    /* =========================================
   PRELOADER (TELA DE CARREGAMENTO)
   ========================================= */
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
    <div class="container">
        <div class="form-container">
            <img src="../img/logo_admin.png" alt="Logo" class="logo">
            
            <form method="post">
                <div class="inputs-area">
                    <div class="input-group">
                        <label for="usuario">Usuário</label>
                        <input type="text" name="usuario" id="usuario" placeholder="Digite seu usuário" required autocomplete="off">
                    </div>

                    <div class="input-group">
                        <label for="senha">Senha</label>
                        <input type="password" name="senha" id="senha" placeholder="Digite sua senha" required>
                    </div>
                </div>

                <div class="button"><button type="submit" class="sign">Entrar</button></div>

                <?php if (isset($erro)): ?>
                    <div class="erro-msg">
                        <?php echo $erro; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
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