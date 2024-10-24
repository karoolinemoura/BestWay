<!-- 
    Essa página será inspirada no arquivo do projeto do Rui chamado perfil1.php que está na pasta exemplos/projeto_rui/perfil1.php

    Pra conseguir editar que ainda colocar o perfil2.php
-->
<?php

include '../config/valida.php';
include '../config/liga_bd.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$activeMenu = 'profile';

// Definir o ID do usuário logado
$id = $_SESSION['id']; // ID do usuário logado

// Gerenciar solicitações de amizade
if (isset($_POST['id_solicitacao'])) {
    $id_solicitacao = $_POST['id_solicitacao'];
    if (isset($_POST['aceitar'])) {
        // Aceitar a solicitação
        $stmt = $ligacao->prepare("UPDATE amizades SET status = 'aceito' WHERE id = ?");
        $stmt->bind_param("i", $id_solicitacao);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Amizade aceita.']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao aceitar a amizade.']);
            exit;
        }
    } elseif (isset($_POST['rejeitar'])) {
        // Rejeitar a solicitação
        $stmt = $ligacao->prepare("DELETE FROM amizades WHERE id = ?");
        $stmt->bind_param("i", $id_solicitacao);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Amizade rejeitada.']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao rejeitar a amizade.']);
            exit;
        }
    }
}

// Mostrar solicitações de amizade pendentes
$sql_solicitacoes = "SELECT * FROM amizades WHERE id_usuario2 = ? AND status = 'pendente'";
$stmt_solicitacoes = $ligacao->prepare($sql_solicitacoes);
$stmt_solicitacoes->bind_param("i", $id);
$stmt_solicitacoes->execute();
$resultado_solicitacoes = $stmt_solicitacoes->get_result();

// Verificar se há solicitações pendentes e exibi-las antes do restante da página
$solicitacoes = []; // Inicializar a variável

if ($resultado_solicitacoes->num_rows > 0) {
    echo "";
    while ($solicitacao = $resultado_solicitacoes->fetch_assoc()) {
        $solicitacoes[] = $solicitacao; // Adicionar cada solicitação ao array
    }
    echo "<hr>"; // Opcional: Adicionar uma linha separadora
} else {
    echo ""; // Mensagem caso não haja solicitações
}


    echo "<hr>"; // Opcional: Adicionar uma linha separadora


// Atualizar dados do perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['id_solicitacao'])) {
    $nick = $_POST['nick'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $data_nasc = $_POST['data_nasc'];
    $fotoAntiga = $_POST['nome_foto'];
    $fotoNova = $fotoAntiga; // Manter a foto antiga por padrão

    // Lidar com upload de foto
    if (isset($_FILES['ficheiro']) && $_FILES['ficheiro']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 800 * 1024;  // 800 KB

        // Verificar tipo e tamanho do arquivo
        if (in_array($_FILES['ficheiro']['type'], $allowed_types) && $_FILES['ficheiro']['size'] <= $max_size) {
            $fotoNova = uniqid() . '_' . $_FILES['ficheiro']['name'];  // Nome único para evitar conflitos
            $destino = '../assets/images/pics/' . $fotoNova;

            // Verificar se o diretório 'pics/' existe, caso contrário, cria-o
            if (!is_dir('../assets/images/pics')) {
                mkdir('../assets/images/pics', 0777, true);
            }

            // Mover o arquivo para o diretório desejado
            if (move_uploaded_file($_FILES['ficheiro']['tmp_name'], $destino)) {
                // Remover a foto antiga, se for o caso
                if ($fotoAntiga && file_exists('../assets/images/pics/' . $fotoAntiga)) {
                    unlink('../assets/images/pics/' . $fotoAntiga);
                }
            } else {
                echo "Erro ao fazer upload da imagem!";
                exit;
            }
        } else {
            echo "Arquivo inválido ou excedeu o tamanho permitido!";
            exit;
        }
    }

    // Atualizar dados no banco de dados usando prepared statements
    $stmt = $ligacao->prepare("UPDATE t_user SET nick = ?, nome = ?, email = ?, data_nasc = ?, foto = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $nick, $nome, $email, $data_nasc, $fotoNova, $id);

    if ($stmt->execute()) {
        // Redirecionar após o sucesso
        header('Location: perfil.php?msg=perfil_atualizado');
        exit;
    } else {
        echo "Erro ao atualizar os dados: " . $stmt->error;
        exit;
    }
}

// Exibir dados do perfil
$sql = "SELECT * FROM t_user WHERE id=?";
$stmt = $ligacao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$linha = $resultado->fetch_assoc();

mysqli_close($ligacao);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/styleperfil.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    
    
    <style>
        
        header{
        position: fixed;
        top: 0;
        right: 0;
        width: 100%;
        z-index:100;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 30px 18%;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .navbar {
    display: flex;
}

.navbar a {
    color: black; /* Define a cor padrão dos links */
}


        .menu-container {
            background-color: #f8f9fa;
            border-right: 1px solid #e0e0e0;
        }

        .menu {
            display: flex;
            flex-direction: column;
        }

        .menu-item {
            color: #007AFF;
            padding: 10px;
            margin: 5px 0;
            text-decoration: none;
            font-size: 18px;
            border-radius: 10px;
            transition: background-color 0.3s ease, transform 0.2s ease-in-out;
        }

        .menu-item:hover {
            background-color: #f0f0f0;
            transform: translateX(8px);
        }

        .menu-item i {
            margin-right: 8px;
            font-size: 20px;
        }

        .menu-item.active {
            background-color: #dfe4ea;
            font-weight: bold;
        }

        .menu-item:active {
            transform: scale(0.98);
       
        }

        .card {
            border-radius: 15px;
            overflow: hidden;
        }

        .card .tab-content {
            border-top: 1px solid #e0e0e0;
        }

        .tab-pane {
            padding: 20px;
        }

        /* Header */
        .navbar {
            display: flex;
        }

        .navbar a {
            color: var(--bg-color);
            font-size: var(--p-font);
            font-weight: 500;
            padding: 10px 22px;
            border-radius: 4px;
           
        }

        .navbar a:hover {
            background: var(--bg-color);
            color: var(--text-color);
            box-shadow: 5px 10px 30px rgb(85 85 85 / 20%);
            border-radius: 4px;
        }

      /* Escurecer o fundo quando o popup estiver ativo */
.popup-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 998; /* Deve estar abaixo do popup */
    display: none; /* Oculto por padrão */
}

.popup-background.active {
    display: block;
}

/* Animação de fade-in suave */
.popup {
    transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
}

    /* Redefinir estilos do popup */
.popup {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0);
    width: 90%;
    max-width: 600px;
    background-color: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    opacity: 0;
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.popup.active {
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
}

.top-bar {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 50px;
    background: #000;
    color: #fff;
    text-align: center;
    line-height: 50px;
    font-weight: 300;
}

.close-btn::before {
    content: '×';
    font-size: 16px;
    line-height: 16px;
    color: white;
}

/* Botão de fechar */
.close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 25px;
    height: 25px;
    background: rgba(0, 0, 0, 0.5);
    color: #fff;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer
}

/* Imagem do popup */
.large-image {
    width: 100%;
    height: auto;
    object-fit: cover;
    border-radius: 10px;
}

.index {
    position: absolute;
    bottom: 10px;
    right: 10px;
    font-size: 80px;
    font-weight: 100;
    color: rgba(255, 255, 255, 0.4);
}

 /* Estilos do modal */
 .modal {
            display: none; /* Ocultar modal por padrão */
            z-index: 2000;
            left: 0;
            top: 100px;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0); /* Cor de fundo */
            background-color: rgba(0,0,0,0.4); /* Fundo escuro */
        }

        .modal-content {
            background-color: #fefefe;
            padding: 100px;
            border: 1px solid #888;
            width: 80%; /* Largura do modal */
            max-width: 500px; /* Largura máxima do modal */
          
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

       
    </style>
</head>

<body>
        <!--header-->
    
        <header>
        <a href="../index.html" style="font-size: 35px; font-weight: 600; letter-spacing: 1px; color: black;">BestWay</a>
        <div class="bx bx-menu" id="menu-icon"></div>

        <ul class="navbar">
            <li><a href="/SiteViagens/public/hotels/hotels.php" style="color: black;" >Hospedagem</a></li>
            <li><a href="#package" style="color: black;" >Passagens</a></li>
            <li><a href="/SiteViagens/public/tours/tours.php" style="color: black; ">Tours</a></li>
            <li><a href="#contact" style="color: black;">Pacotes</a></li>
            <li><a href="/SiteViagens/public/carrinho/carrinho.php" style="color: black !important;"><i class='bx bx-cart'></i></a></li>

        </ul>
        </header>

    <br><br><br><br><br><br>

<!-- Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <form id="solicitacaoForm" method="POST">
            <input type="hidden" id="solicitacaoId" name="id_solicitacao" value="">
            <p>Você tem certeza que deseja aceitar esta solicitação de amizade?</p>
            <button type="submit" name="aceitar">Aceitar</button>
            <button type="button" onclick="closeModal()">Cancelar</button>
        </form>
    </div>
</div>

<?php
    foreach ($solicitacoes as $solicitacao) {
        echo "<form method='post' onsubmit='openModal(" . $solicitacao['id'] . "); return false;'>"; 
        echo "<input type='hidden' name='id_solicitacao' value='" . $solicitacao['id'] . "'>";
        echo "<button type='button' onclick='openModal(" . $solicitacao['id'] . ");'>Aceitar</button>";
        echo "<button type='submit' name='rejeitar'>Rejeitar</button>";
        echo "</form>";
    }
?>


    <div class="container light-style flex-grow-1 container-p-y">
        <div class="card shadow-sm rounded-lg">
            <div class="row no-gutters">
                <div class="col-md-3 p-3 bg-light rounded-left menu-container">
                    <nav class="menu">
                         <?php echo htmlspecialchars($linha['nome']); ?>
                        <a class="menu-item <?php echo $activeMenu === 'profile' ? 'active' : ''; ?>"
                            href="perfil.php#account-general"><i class="bi bi-person-circle"></i> Editar perfil</a>
                        <a class="menu-item" href="reservas.php"><i class="bi bi-clipboard2"></i> Reservas</a>
                        <a class="menu-item" href="favoritos.php"><i class="bi bi-heart"></i> Favoritos</a>
                        <a class="menu-item" href="chat2.php"><i class="bi bi-chat-dots"></i> Chat</a>
                        <a class="menu-item" href="configuracoes.php"><i class="bi bi-gear"></i> Configurações</a>
                    </nav>
                </div>

                                        

                <div class="col-md-9">
                    <div class="tab-content p-4">
                        <div class="tab-pane fade active show" id="account-general">
                            <form action="perfil.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($linha['id']); ?>">

                                <!-- Avatar e upload de nova foto -->
                                <div class="media align-items-center mb-3">
                                <img src="<?php echo $linha['foto'] ? '../assets/images/pics/' . htmlspecialchars($linha['foto']) : 'https://bootdey.com/img/Content/avatar/avatar1.png'; ?>"
                                alt="avatar" class="rounded-circle mr-3 image" style="width: 80px;">
                                    <div class="media-body">
                                        <label class="btn btn-outline-primary rounded-pill">
                                            Upload Nova Foto
                                            <input type="file" name="ficheiro" class="account-settings-fileinput"
                                                accept=".jpg, .jpeg, .png, .gif">
                                        </label>
                                        <div class="small text-muted mt-1">Permitido JPG, GIF ou PNG. Tamanho máximo de
                                            800K.
                                        </div>

                                           

                                    </div>
                                </div>

                            <!-- Popup container -->
                            <div class="popup-background"></div> <!-- Mover para fora do popup -->
                            <div class="popup">
                                <div class="top-bar">
                                    <span class="image-name"></span>
                                    <div class="close-btn"></div>
                                </div>
                                <img class="large-image" src="" alt="Imagem Grande">
                                <div class="index"></div>
                            </div>

                            <input type="hidden" name="nome_foto"
                                value="<?php echo htmlspecialchars($linha['foto']); ?>">

                                

                                <!-- Campos de informações pessoais -->
                                <div class="form-group">
                                    <label for="username" class="form-label">Nick</label>
                                    <input type="text" id="username" class="form-control rounded-pill" name="nick"
                                        value="<?php echo htmlspecialchars($linha['nick']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="name" class="form-label">Nome</label>
                                    <input type="text" id="name" class="form-control rounded-pill" name="nome"
                                        value="<?php echo htmlspecialchars($linha['nome']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" id="email" class="form-control rounded-pill" name="email"
                                        value="<?php echo htmlspecialchars($linha['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="data_nasc" class="form-label">Data de Nascimento</label>
                                    <input type="date" id="data_nasc" class="form-control rounded-pill" name="data_nasc"
                                        value="<?php echo htmlspecialchars($linha['data_nasc']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="pass" class="form-label">Senha atual</label>
                                    <input type="password" id="pass" class="form-control rounded-pill" name="pass"
                                        required>
                                </div>

                                <!-- Botões de ação -->
                                <div class="text-right mt-3">
                                    <input type="submit" class="btn btn-primary rounded-pill" value="Alterar">
                                   
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
   const images = [...document.querySelectorAll('.image')];
const popup = document.querySelector('.popup');
const closeBtn = document.querySelector('.close-btn');
const largeImage = document.querySelector('.large-image');
const imageName = document.querySelector('.image-name');
const popupBackground = document.querySelector('.popup-background');
let index = 0;

images.forEach((item, i) => {
    item.addEventListener('click', () => {
        updateImage(i);
        popup.classList.add('active');
        popupBackground.classList.add('active'); // Ativar fundo escurecido
    });
});

const updateImage = (i) => {
    let path = images[i].src;
    largeImage.src = path;
    imageName.innerHTML = path.split('/').pop();
    // Remova ou comente esta linha para não exibir o número da imagem:
    // imageIndex.innerHTML = `0${i + 1}`;
    index = i;
};

closeBtn.addEventListener('click', () => {
    popup.classList.remove('active');
    popupBackground.classList.remove('active'); // Desativar fundo escurecido
});




function openModal(id) {
    document.getElementById('solicitacaoId').value = id; // Define o ID da solicitação no campo oculto
    document.getElementById('myModal').style.display = "block"; // Exibe o modal
}

function closeModal() {
    document.getElementById('myModal').style.display = "none"; // Oculta o modal
}

// Fecha o modal se o usuário clicar fora do conteúdo do modal
window.onclick = function(event) {
    if (event.target == document.getElementById('myModal')) {
        closeModal();
    }
}

// AJAX para aceitar ou rejeitar a solicitação
$(document).ready(function() {
    $('#solicitacaoForm').on('submit', function(event) {
        event.preventDefault(); // Impede o envio padrão do formulário

        $.ajax({
            url: 'perfil.php', // A URL onde o formulário será enviado
            type: 'POST',
            data: $(this).serialize(), // Serializa os dados do formulário
            success: function(response) {
                // Aqui você pode verificar a resposta e atualizar a interface do usuário, se necessário
                closeModal(); // Fecha o modal
                location.reload(); // Recarrega a página para ver as mudanças
            },
            error: function(xhr, status, error) {
                console.error('Erro ao processar a solicitação:', error);
            }
        });
    });
});
   
</script>
    


    <!--footer-->
    <?php include '../views/partials/footer.php' ?>
</body>

</html>
