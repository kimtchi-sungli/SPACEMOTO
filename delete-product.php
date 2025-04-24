<?php
session_start();
require_once __DIR__ . '/../../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Redirecionar se o usuário não for um administrador
    header('Location: /login.php');
    exit();
}

$product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);

if (!$product_id) {
    die('ID do produto inválido.');
}

// Consultar os detalhes do produto
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();
$product = $product_result->fetch_assoc();

if (!$product) {
    die('Produto não encontrado.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Excluir o produto do banco de dados
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        echo 'Produto excluído com sucesso!';
    } else {
        echo 'Erro ao excluir produto: ' . $conn->error;
    }
}

include __DIR__ . '/../../../includes/header.php';
?>

<div class="container">
    <h2>Excluir Produto</h2>
    <p>Tem certeza de que deseja excluir o produto <strong><?php echo htmlspecialchars($product['name']); ?></strong>?</p>
    <form action="delete-product.php?product_id=<?php echo $product_id; ?>" method="POST">
        <button type="submit" class="btn btn-danger">Excluir Produto</button>
        <a href="/pages/orders/products.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
</body>
</html>
