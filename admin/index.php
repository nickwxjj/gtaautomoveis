<?php
include_once '../includes/verifica_login.php';
include_once '../includes/conexao.php';
include_once 'gerenciar_home.php';

/* --- CARROS --- */
$carros = $pdo->query("SELECT id, titulo, ano, preco, imagem_capa, created_at FROM carros ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

/* --- CLIENTES --- */
$busca = $_GET['busca'] ?? '';
$stmt = $pdo->prepare("
    SELECT c.nome, c.telefone, c.cpf, c.data_nasc, ca.titulo AS carro
    FROM clientes c
    LEFT JOIN carros ca ON ca.id = c.id_carro
    WHERE c.nome LIKE ?
    ORDER BY c.nome ASC
");
$stmt->execute(['%' . $busca . '%']);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_carros = count($carros);
$total_clientes = count($clientes);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Painel Admin - GTA</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <style>
        /* ===== RESET E CONFIGURAÇÕES GLOBAIS ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* CSS GERAL */
        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('../img/fundo_cont.png');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            padding-top: 70px;
            min-height: 100vh;
        }

        /* NAVBAR (TOPO) */
        .top-nav {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 3px solid #FFD700;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .brand-text {
            color: #ffffff !important;
            font-weight: 800;
            font-size: 1.4rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
        }
        
        .brand-span {
            color: #FFD700;
            font-weight: 400;
            margin-left: 5px;
        }

        /* LINKS DO MENU */
        .nav-link-custom {
            color: #ffffff !important;
            font-weight: 600;
            margin-right: 20px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link-custom::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #FFD700, #FFA500);
            transition: width 0.3s ease;
        }
        
        .nav-link-custom:hover::after, 
        .nav-link-custom.active::after {
            width: 100%;
        }
        
        .nav-link-custom:hover, 
        .nav-link-custom.active {
            color: #FFD700 !important;
            transform: translateY(-2px);
        }
        
        .dropdown-toggle {
            color: #ffffff !important;
        }
        
        .user-dropdown .nav-link {
            color: #ffffff !important;
        }

        /* CONTAINER PRINCIPAL */
        main {
            padding: 30px 0;
        }

        .container {
            max-width: 1200px;
        }

        /* TÍTULO PRINCIPAL */
        .principal-title {
            color: white;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        /* ===== CARDS DE RESUMO ===== */
        .card-box {
            border: none;
            border-radius: 15px;
            padding: 25px;
            position: relative;
            overflow: hidden;
            height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .card-box:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            transform: translateY(-10px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
            border-color: rgba(255, 215, 0, 0.4);
        }

        .bg-azul { 
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.3), rgba(30, 58, 138, 0.2)) !important; 
            color: #000; 
        }
        
        .bg-preto { 
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.3), rgba(50, 50, 50, 0.2)) !important; 
            color: #000; 
        }
        
        .bg-amarelo { 
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.25), rgba(255, 165, 0, 0.15)) !important; 
            color: #000; 
        }

        .card-count { 
            font-size: 2.8rem; 
            font-weight: 900; 
            line-height: 1; 
            color: #000;
            margin-bottom: 8px;
        }
        
        .card-label { 
            font-size: 0.85rem; 
            text-transform: uppercase; 
            opacity: 0.85; 
            font-weight: 700; 
            color: #000;
            letter-spacing: 0.5px;
        }
        
        .card-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 5rem;
            opacity: 0.12;
        }

        /* ===== TABELAS ===== */
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .card:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
            border-color: rgba(255, 215, 0, 0.3);
        }

        .card-header {
            background-color: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 15px 15px 0 0;
            border: none;
        }

        .card-header > div:first-child {
            color: #000;
            font-weight: 700;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header i {
            color: #FFD700;
            font-size: 1.4rem;
        }

        .card-body {
            background-color: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #000;
            padding: 25px;
        }

        /* ===== DATATABLES ===== */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        .datatable-table thead th {
            background: rgba(255, 255, 255, 0.12);
            color: #000;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            padding: 15px;
            border-bottom: 2px solid rgba(255, 215, 0, 0.3);
            letter-spacing: 0.5px;
        }

        .datatable-table tbody tr {
            background: transparent;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .datatable-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.12);
            border-bottom-color: rgba(255, 215, 0, 0.2);
        }

        .datatable-table tbody td {
            color: #000;
            padding: 15px;
            font-weight: 500;
        }

        .table-img {
            width: 60px; 
            height: 40px; 
            object-fit: cover; 
            border-radius: 8px; 
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* ===== BOTÕES ===== */
        .btn-gold {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #000 !important;
            font-weight: 700;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }

        .btn-gold:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.5);
            background: linear-gradient(135deg, #FFA500, #FFD700);
        }

        .btn-gold:active {
            transform: translateY(-1px);
        }

        .btn-outline-primary,
        .btn-outline-danger {
            color: #FFD700 !important;
            border-color: rgba(255, 215, 0, 0.5) !important;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: rgba(255, 215, 0, 0.15);
            border-color: #FFD700 !important;
        }

        .btn-outline-danger:hover {
            background: rgba(220, 53, 69, 0.15);
            border-color: #dc3545 !important;
        }

        /* ===== INPUTS DE BUSCA ===== */
        .form-control {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #000;
            border-radius: 8px;
            transition: all 0.3s ease;
            padding: 10px 15px;
            font-weight: 500;
        }

        .form-control::placeholder {
            color: rgba(0, 0, 0, 0.5);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 215, 0, 0.5);
            color: #000;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.2);
        }

        /* ===== FOOTER ===== */
        footer {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            text-align: center;
            padding: 20px;
            margin-top: 60px;
            font-weight: 500;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .principal-title {
                font-size: 1.5rem;
            }

            .card-box {
                height: auto;
                padding: 20px;
            }

            .card-count {
                font-size: 2rem;
            }

            .card-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .card-header > div {
                width: 100%;
            }

            .btn-gold {
                width: 100%;
            }

            table {
                font-size: 0.9rem;
            }

            .datatable-table tbody td {
                padding: 10px;
            }
        }

        /* ===== ANIMAÇÕES ===== */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .principal-title {
            animation: slideInDown 0.6s ease-out;
        }

        .card-box {
            animation: slideInUp 0.6s ease-out;
        }

        .card {
            animation: slideInUp 0.6s ease-out;
        }

        /* ===== BADGE ===== */
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .badge.bg-warning {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.3), rgba(255, 165, 0, 0.2)) !important;
            color: #000 !important;
            border: 1px solid rgba(255, 215, 0, 0.4);
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
    <nav class="navbar navbar-expand-lg top-nav">
        <div class="container-fluid">
            
            <a class="navbar-brand brand-text" href="index.php">
                GTA Automóveis <span>Administradores</span>
            </a>

            <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <i class="fas fa-bars"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom active" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Visão Geral
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="adicionar_carro.php">
                            <i class="fas fa-plus-circle"></i> Adicionar Carro
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto user-dropdown">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-lg"></i> Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Configurações</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../admin/logout.php">Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <div class="container"> <h2 class="principal-title">Visão Geral do Sistema</h2>

            <div class="row mb-4">
                <div class="col-xl-4 col-md-6 mb-3">
                    <div class="card dashboard-card bg-blue shadow">
                        <div class="card-body">
                            <div class="count text-white"><?= $total_carros ?></div>
                            <div class="label text-white">Veículos em Estoque</div>
                            <i class="fas fa-car icon text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-3">
                    <div class="card dashboard-card bg-dark-card shadow">
                        <div class="card-body">
                            <div class="count text-white"><?= $total_clientes ?></div>
                            <div class="label text-white">Clientes Cadastrados</div>
                            <i class="fas fa-users icon text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-3">
                    <div class="card dashboard-card bg-yellow shadow">
                        <div class="card-body">
                            <div class="count text-white">R$ Depende de você meu parceiro</div>
                            <div class="label text-white">Vendas do Mês</div>
                            <i class="fas fa-chart-line icon text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-5">
                <div class="card-header">
                    <div><i class="fas fa-car me-2"></i> Estoque de Veículos</div>
                    <a href="adicionar_carro.php" class="btn btn-gold btn-sm text-dark shadow-sm">
                        <i class="fas fa-plus"></i> Novo
                    </a>
                </div>
                <div class="card-body">
                    <table id="datatablesSimple1">
                        <thead>
                            <tr>
                                <th>Imagem</th>
                                <th>Modelo</th>
                                <th>Ano</th>
                                <th>Preço</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carros as $c): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($c['imagem_capa'])): ?>
                                            <img src="../<?= $c['imagem_capa'] ?>" class="table-img" alt="Foto">
                                        <?php else: ?>
                                            <span class="text-muted small">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold"><?= htmlspecialchars($c['titulo']) ?></td>
                                    <td><?= $c['ano'] ?></td>
                                    <td class="text-primary fw-bold">R$ <?= number_format($c['preco'], 2, ',', '.') ?></td>
                                    <td>
                                        <a href="editar_carro.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                        <a href="excluir_carro.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <div><i class="fas fa-users me-2"></i> Base de Clientes</div>
                </div>
                <div class="card-body">
                    <form method="get" class="row g-2 mb-3 align-items-center" style="max-width: 500px;">
                        <div class="col-auto">
                            <input type="text" name="busca" class="form-control form-control-sm" placeholder="Filtrar por nome (PHP)..." value="<?= htmlspecialchars($busca) ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-gold btn-sm">Filtrar</button>
                        </div>
                    </form>

                    <table id="datatablesSimple2">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>CPF</th>
                                <th>Nasc.</th>
                                <th>Interesse</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $c): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($c['nome']) ?></td>
                                    <td><?= htmlspecialchars($c['telefone']) ?></td>
                                    <td><?= htmlspecialchars($c['cpf']) ?></td>
                                    <td><?= !empty($c['data_nasc']) ? date('d/m/Y', strtotime($c['data_nasc'])) : '-' ?></td>
                                    <td>
                                        <?php if($c['carro']): ?>
                                            <span class="badge bg-warning text-dark"><?= htmlspecialchars($c['carro']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- NOVA TABELA: CARROS DO HOME -->
            <div class="card mb-4">
                <div class="card-header">
                    <div><i class="fas fa-star me-2"></i> Carros em Destaque (Home)</div>
                </div>
                <div class="card-body">
                    <form method="post" class="row g-2 mb-3 align-items-center" style="max-width: 500px;">
                        <div class="col-auto">
                            <select name="id_carro" class="form-control form-control-sm" required>
                                <option value="">Selecione um carro...</option>
                                <?php foreach ($carros_disponiveis as $carro): ?>
                                    <option value="<?= $carro['id'] ?>"><?= htmlspecialchars($carro['titulo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="adicionar_home" class="btn btn-gold btn-sm">+ Adicionar ao Home</button>
                        </div>
                    </form>

                    <table id="datatablesSimple3">
                        <thead>
                            <tr>
                                <th>Posição</th>
                                <th>Foto</th>
                                <th>Nome do Card</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($home_carros as $idx => $hc): ?>
                                <tr>
                                    <td>Card <?= $idx + 1 ?></td>
                                    <td>
                                        <?php if (!empty($hc['imagem_capa'])): ?>
                                            <img src="../<?= htmlspecialchars($hc['imagem_capa']) ?>" alt="<?= htmlspecialchars($hc['titulo']) ?>" class="table-img" style="width: 60px; height: 60px; border-radius: 8px; object-fit: cover;">
                                        <?php else: ?>
                                            <span class="text-muted">Sem imagem</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="id_carro" value="<?= $hc['id_carro'] ?>">
                                            <input type="text" name="titulo" value="<?= htmlspecialchars($hc['titulo']) ?>" class="form-control form-control-sm" style="width: 300px;">
                                            <button type="submit" name="editar_titulo" class="btn btn-sm btn-warning mt-2">Atualizar Nome</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="post" enctype="multipart/form-data" style="display: inline;">
                                            <input type="hidden" name="id_carro" value="<?= $hc['id_carro'] ?>">
                                            <input type="file" name="imagem_capa" class="form-control form-control-sm" accept="image/*" style="width: 150px; display: inline-block;">
                                            <button type="submit" name="editar_imagem" class="btn btn-sm btn-info">Trocar Foto</button>
                                        </form>
                                        <a href="?remover=<?= $hc['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?');">Remover</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if (empty($home_carros)): ?>
                        <div class="alert alert-info">Nenhum carro em destaque no home. Adicione um usando o formulário acima.</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <footer>
        <div class="container">
            Copyright &copy; GTA Automóveis 2026
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', event => {
            const table1 = document.getElementById('datatablesSimple1');
            if (table1) new simpleDatatables.DataTable(table1, { labels: { placeholder: "Buscar carro...", perPage: "Linhas", noRows: "Nenhum registro" }});

            const table2 = document.getElementById('datatablesSimple2');
            if (table2) new simpleDatatables.DataTable(table2, { labels: { placeholder: "Buscar cliente...", perPage: "Linhas", noRows: "Nenhum registro" }});

            const table3 = document.getElementById('datatablesSimple3');
            if (table3) new simpleDatatables.DataTable(table3, { labels: { placeholder: "Buscar destaque...", perPage: "Linhas", noRows: "Nenhum registro" }});
        });
    </script>
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