<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';



if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) { // Mudança para verificar o valor numérico de is_admin
    // Redirecionar se o usuário não for um administrador
    header('Location: /pages/userlogin/login.php'); // Caminho ajustado
    exit();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Painel de Administração</h1>
    <div class="admin-options">
       
        <a href="manage-product.php" class="btn btn-secondary">Gerenciar Produtos</a>
        <a href="manage-orders.php" class="btn btn-primary">Gerenciar Pedidos</a>
        <a href="manage-users.php" class="btn btn-secondary">Gerenciar Usuários</a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
