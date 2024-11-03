<?php
// INCLUE FUNCOES DE ADDONS -----------------------------------------------------------------------
include('addons.class.php');

// VERIFICA SE O USUARIO ESTA LOGADO --------------------------------------------------------------
session_name('mka');
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['mka_logado']) && !isset($_SESSION['MKA_Logado'])) exit('Acesso negado... <a href="/admin/login.php">Fazer Login</a>');
// VERIFICA SE O USUARIO ESTA LOGADO --------------------------------------------------------------

// Assuming $Manifest is defined somewhere before this code
$manifestTitle = $Manifest->{'name'} ?? '';
$manifestVersion = $Manifest->{'version'} ?? '';
?>

<!DOCTYPE html>
<html lang="pt-BR" class="has-navbar-fixed-top">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="iso-8859-1">
<title>MK-AUTH :: <?php echo $Manifest->{'name'}; ?></title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link href="../../estilos/mk-auth.css" rel="stylesheet" type="text/css" />
<link href="../../estilos/font-awesome.css" rel="stylesheet" type="text/css" />
<link href="../../estilos/bi-icons.css" rel="stylesheet" type="text/css" />

<script src="../../scripts/jquery.js"></script>
<script src="../../scripts/mk-auth.js"></script>
	
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .container {
			width: 100%; /* Definindo largura em porcentagem */
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2, h3 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
        padding: 6px; /* Reduzindo o padding para diminuir a altura */
        text-align: left;
        border-bottom: 1px solid #ddd;
        /* Adicionando mais propriedades */
        font-size: 15px; /* Altera o tamanho da fonte */
        font-family: Arial, sans-serif; /* Define a fonte */
        vertical-align: middle; /* Alinha verticalmente o conteúdo */
        /* Adicione outras propriedades conforme necessário */
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        .date-picker {
            margin-right: 10px;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="date"],
        button[type="submit"] {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }
        input[type="date"] {
            width: 180px;
        }
        button[type="submit"] {
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            color: #666;
            margin-bottom: 20px;
        }
        .message.error {
            color: #c00;
        }
		.styled-select {
        display: inline-block;
        font-size: 14px;
        font-family: Arial, sans-serif;
        padding: 6px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #fff;
        color: #333;
        }

        .styled-select:hover {
        border-color: #007bff;
        }

        .styled-select:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.3);
        }
	
        #nome_cliente:hover {
        border-color: #007bff; /* Altere para a cor desejada ao passar o mouse */
        }
    </style>
</head>
<body>
    <?php include('../../topo.php'); ?>
    <div class="container">
        <nav class="breadcrumb has-bullet-separator is-centered" aria-label="breadcrumbs">
            <ul>
                <li><a href="#"> ADDON</a></li>
                <li class="is-active">
                    <a href="#" aria-current="page"> <?php echo htmlspecialchars($manifestTitle . " - V " . $manifestVersion); ?> </a>
                </li>
            </ul>
        </nav>
        <?php include('config.php'); ?>
        <?php if ($acesso_permitido) : ?>
<?php
// Define a data de início como 30 dias atrás
$data_inicio_padrao = date('Y-m-01');
// Define a data de fim como a data atual
$data_fim_padrao = date('Y-m-t');

// Defina a observação padrão como vazia
$observacao_padrao = '';

// Verifica se as datas, a observação e o nome do cliente foram enviados via GET
if (isset($_GET['data_inicio']) && isset($_GET['data_fim'])) {
    $data_inicio = $_GET['data_inicio'];
    $data_fim = $_GET['data_fim'];
    // Verifica se a observação foi selecionada
    if(isset($_GET['observacao']) && ($_GET['observacao'] == 'sim' || $_GET['observacao'] == 'nao')) {
        $observacao = $_GET['observacao'];
    } else {
        // Caso contrário, use a observação padrão
        $observacao = $observacao_padrao;
    }
    // Verifica se o nome do cliente foi fornecido
    if(isset($_GET['nome_cliente']) && !empty($_GET['nome_cliente'])) {
        $nome_cliente = $_GET['nome_cliente'];
    } else {
        // Caso contrário, defina como vazio
        $nome_cliente = '';
    }
} else {
    // Utiliza as datas padrão
    $data_inicio = $data_inicio_padrao;
    $data_fim = $data_fim_padrao;
    // Caso a observação não seja selecionada, use a observação padrão
    $observacao = $observacao_padrao;
    // Define o nome do cliente como vazio
    $nome_cliente = '';
}

// Construa a parte da consulta SQL para a cláusula WHERE com base no nome do cliente fornecido
$nome_cliente_query = '';
if (!empty($nome_cliente)) {
    $nome_cliente_query = "AND cl.nome LIKE '%$nome_cliente%'";
}

// Construa a parte da consulta SQL para a cláusula WHERE com base na observação selecionada
$observacao_query = '';
if ($observacao != '') {
    $observacao_query = "AND cl.observacao = '$observacao'";
}

// Consulta SQL para obter os clientes com títulos vencidos dentro do intervalo fornecido, com a observação selecionada e correspondendo ao nome do cliente
$query = "SELECT cl.*, sl.id, sl.datavenc, sl.datapag, sl.datavenc as datavenc_boleto,
          (SELECT datavenc FROM sis_lanc WHERE login = cl.login AND status = 'vencido' AND deltitulo = 0 ORDER BY datavenc DESC LIMIT 1) as datavenc_vencido,
          cl.bloqueado
          FROM sis_cliente cl 
          INNER JOIN sis_lanc sl ON cl.login = sl.login 
          WHERE cl.tit_vencidos > 0 
          AND cl.cli_ativado = 's'
          $observacao_query
          $nome_cliente_query
          AND sl.datapag BETWEEN '$data_inicio' AND '$data_fim'
          AND sl.datavenc >= sl.datapag
          ORDER BY sl.datapag DESC";


// Executa a consulta
$result = mysqli_query($link, $query);
?>


<form method="GET">
    <label for="data_inicio" class="date-picker">Data de Início:</label>
    <input type="date" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>" class="date-picker" style="color: #333; background-color: #fff; border: 1px solid #007bff;">
    <label for="data_fim" class="date-picker">Data Final:</label>
    <input type="date" id="data_fim" name="data_fim" value="<?php echo htmlspecialchars($data_fim); ?>" class="date-picker" style="color: #333; background-color: #fff; border: 1px solid #007bff;">
    
    <label for="observacao">Obs.</label>
    <select id="observacao" name="observacao" class="styled-select">
        <option value="" <?php if(isset($_GET['observacao']) && $_GET['observacao'] == '') echo 'selected'; ?>>Todos</option>
        <option value="sim" <?php if(isset($_GET['observacao']) && $_GET['observacao'] == 'sim') echo 'selected'; ?>>Sim</option>
        <option value="nao" <?php if(isset($_GET['observacao']) && $_GET['observacao'] == 'nao') echo 'selected'; ?>>Não</option>
    </select>

    <!-- Campo de busca por nome do cliente -->
    <label for="nome_cliente"></label>
    <input type="text" id="nome_cliente" name="nome_cliente" placeholder="Digite o nome do cliente" value="<?php echo isset($_GET['nome_cliente']) ? htmlspecialchars($_GET['nome_cliente']) : ''; ?>" style="height: 32px; border-radius: 5px; transition: border-color 0.3s;">

    <button type="submit" class="button is-info">
        <span class="icon">
            <i class="fas fa-search"></i>
        </span>
        <span>Filtrar</span>
    </button>

    <a href="?data_inicio=<?php echo htmlspecialchars($data_inicio_padrao); ?>&data_fim=<?php echo htmlspecialchars($data_fim_padrao); ?>" class="button is-light">
        <span class="icon">
            <i class="fas fa-broom"></i>
        </span>
        <span>Limpar</span>
    </a>
</form>

            <?php if ($result && mysqli_num_rows($result) > 0) : ?>
    <table>
    <thead>
        <tr>
            <th style="border-right: 1px solid #fff; color: white; font-weight: bold; text-align: center;">Nome do Cliente</th>
            <th style="border-right: 1px solid #fff; color: white; font-weight: bold; text-align: center;">Login</th>
            <th style="border-right: 1px solid #fff; color: white; font-weight: bold; text-align: center;">Titulos Vencidos</th>
            <th style="border-right: 1px solid #fff; color: white; font-weight: bold; text-align: center;">Data de Pagamento</th>
		    <th style="border-right: 1px solid #fff; color: white; font-weight: bold; text-align: center;">Boleto Pago</th>
            <th style="border-right: 1px solid #fff; color: white; font-weight: bold; text-align: center;">Boleto Vencido</th>
			<th style="border-right: 1px solid #fff; color: white; font-weight: bold; text-align: center;">Observação</th> 
			<th style="border-right: 1px solid #fff; color: white; font-weight: bold; text-align: center;">Bloqueado</th>
        </tr>
    </thead>
<tbody>
			    <?php 
                // Inicialize o contador de clientes
                $total_clientes = 0;
                  
			    // Loop para exibir os clientes
                while ($row = mysqli_fetch_assoc($result)) : 
                  
                // Incremente o contador de clientes
                $total_clientes++; ?>
               
                <td style="border-right: 1px solid #fff; font-weight: bold; color: #120bff; position: relative; padding-left: 30px; text-align: center;">
                <?php
                // Defina o comprimento máximo do nome do cliente
                $max_length_nome_cliente = 28;
                // Abrevie o nome do cliente, se necessário
                $nome_cliente_abreviado = strlen($row['nome']) > $max_length_nome_cliente ? substr($row['nome'], 0, $max_length_nome_cliente - 3) . '...' : $row['nome'];
                ?>
                <a href="../../cliente_det.hhvm?uuid=<?php echo htmlspecialchars($row['uuid_cliente']); ?>" target="_blank" style="color: #434343; display: flex; align-items: center; justify-content: center; height: 100%;" title="<?php echo htmlspecialchars($row['nome']); ?>">
                <img src="img/icon_cliente.png" alt="Ícone do cliente" style="position: absolute; left: 5px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px;"> <!-- Substitua 'caminho/para/o/seu/icon.png' pelo caminho real do seu ícone -->
                <?php echo htmlspecialchars($nome_cliente_abreviado); ?>
                </a>
                </td>

                <!--Login-->
                <td style="border-right: 1px solid #fff; font-weight: bold; color: #434343; text-align: center; vertical-align: middle;">
                <?php echo htmlspecialchars($row['login']); ?>
                </td>
				
                <!--Titulos Vencidos-->
                <td style="border-right: 1px solid #fff; color: red; font-weight: bold; text-align: center; position: relative;">
                <img src="img/icon_boleto.png" alt="Ícone de Títulos Vencidos" style="position: absolute; left: 5px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px;"> <!-- Substitua 'caminho/para/o/seu/icon.png' pelo caminho real do seu ícone -->
                <?php echo htmlspecialchars($row['tit_vencidos']); ?>
                </td>

                <!--Data de Pagamento-->
                <td style="border-right: 1px solid #fff; font-weight: bold; text-align: center; color: #3832ff; position: relative;">
                <img src="img/calendario.png" alt="Ícone de Data de Pagamento" style="position: absolute; left: 5px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px;"> <!-- Substitua 'caminho/para/o/seu/icon.png' pelo caminho real do seu ícone -->
                <?php echo $row['datapag'] ? date('d-m-Y', strtotime($row['datapag'])) : 'N/A'; ?>
                </td>

				
				<!--Boleto Pago-->
                <td style="border-right: 1px solid #fff; color: green; font-weight: bold; text-align: center;">
                <?php echo date('d-m-Y', strtotime($row['datavenc'])); ?>
                </td>

                <!--Boleto Vencido-->
                <td style="color: red; font-weight: bold; text-align: center;">
                <?php echo ($row['datavenc_vencido'] && $row['datavenc_vencido'] != '0000-00-00') ? date('d-m-Y', strtotime($row['datavenc_vencido'])) : '---'; ?>
                </td>
                <!-- Nova coluna para mostrar a observação -->
                <td style="font-weight: bold; text-align: center; <?php echo ($row['observacao'] == 'nao') ? 'color: red;' : ''; ?><?php echo ($row['observacao'] == 'sim') ? 'color: green;' : ''; ?>">
                    <?php echo ($row['observacao'] == 'sim') ? 'Sim' : 'Não'; ?>
                </td>
                
				<!-- Adicione a exibição na célula da tabela -->
                <td style="border-right: 1px solid #fff; font-weight: bold; text-align: center; color: <?php echo ($row['bloqueado'] == 'sim') ? 'red' : 'green'; ?>">
                <?php echo ($row['bloqueado'] == 'sim') ? 'Sim' : 'Não'; ?>
                </td>
            </tr>
        <?php endwhile; ?>
		
        <!-- Exiba o total de clientes -->
        <div style="text-align: center;">
        <p style="font-weight: bold;">Total de clientes: <?php echo $total_clientes; ?></p>
        </div>

		
                    </tbody>
                </table>
            <?php else : ?>
              <p style="text-align: center; margin: auto; color: #009347; font-weight: bold;">Nenhum cliente com títulos vencidos encontrado.</p>
            <?php endif; ?>
        <?php else : ?>
            <p>Acesso não permitido!</p>
        <?php endif; ?>
        <?php include('../../baixo.php'); ?>
    </div>
    <script src="../../menu.js.php"></script>
    <?php include('../../rodape.php'); ?>
</body>
</html>

