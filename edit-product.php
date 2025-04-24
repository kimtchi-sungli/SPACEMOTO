<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
// Adicione instruções de depuração para verificar os valores das variáveis de sessão
var_dump($_SESSION['user_id'], $_SESSION['is_admin']);

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) { // Mudança para verificar o valor numérico de is_admin
    // Redirecionar se o usuário não for um administrador
    header('Location: /pages/userlogin/login.php'); // Caminho ajustado
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
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];
    $product_price = $_POST['product_price'];
    $product_stock = $_POST['product_stock'];
    
    // Processamento do upload da imagem do produto
    $image_path = $product['image_path'];
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['product_image']['tmp_name'];
        $image_name = basename($_FILES['product_image']['name']);
        $upload_dir = __DIR__ . '/../../../uploads/';
        $image_path = $upload_dir . $image_name;

        if (move_uploaded_file($image_tmp_name, $image_path)) {
            $image_path = '/uploads/' . $image_name; // Caminho relativo para salvar no banco de dados
        } else {
            echo 'Erro ao fazer upload da imagem.';
            exit();
        }
    }

    // Atualizar os detalhes do produto no banco de dados
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image_path = ? WHERE id = ?");
    $stmt->bind_param("ssdisi", $product_name, $product_description, $product_price, $product_stock, $image_path, $product_id);
    if ($stmt->execute()) {
        echo 'Produto atualizado com sucesso!';
    } else {
        echo 'Erro ao atualizar produto: ' . $conn->error;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h2>Editar Produto</h2>
    <form action="edit-product.php?product_id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="product_name">Nome do Produto</label>
            <input type="text" name="product_name" id="product_name" value="<?php echo htmlspecialchars($product['name']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="product_description">Descrição do Produto</label>
            <textarea name="product_description" id="product_description" required class="form-control"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="product_price">Preço do Produto (€)</label>
            <input type="number" step="0.01" name="product_price" id="product_price" value="<?php echo htmlspecialchars($product['price']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="product_stock">Estoque do Produto</label>
            <input type="number" name="product_stock" id="product_stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="product_image">Imagem do Produto</label>
            <input type="file" name="product_image" id="product_image" class="form-control">
            <?php if ($product['image_path']): ?>
                <img src="<?php echo $product['image_path']; ?>" alt="Imagem do Produto" style="max-width: 150px; margin-top: 10px;">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Atualizar Produto</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
