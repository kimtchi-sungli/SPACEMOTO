<?php
session_start();
include __DIR__ . '/includes/db_connect.php';
include __DIR__ . '/includes/functions.php';

$featured_result = false;
$categories_result = false;
$search_result = false;

try {
    // Check if database connection is established
    if (!isset($conn)) {
        throw new Exception("A conexão com o banco de dados não foi estabelecida.");
    }

    // Fetch featured products
    $featured_query = "SELECT * FROM products WHERE stock > 0 ORDER BY created_at DESC LIMIT 6";
    $featured_result = $conn->query($featured_query);
    if (!$featured_result) {
        throw new Exception("Erro ao buscar produtos em destaque: " . $conn->error);
    }

    // Fetch distinct categories
    $categories_query = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category";
    $categories_result = $conn->query($categories_query);
    if (!$categories_result) {
        throw new Exception("Erro ao buscar categorias: " . $conn->error);
    }

    // Perform search if query parameter is present
    if (isset($_GET['query'])) {
        $query = "%" . $conn->real_escape_string($_GET['query']) . "%";
        $search_query = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ?";
        $stmt = $conn->prepare($search_query);
        $stmt->bind_param("ss", $query, $query);
        $stmt->execute();
        $search_result = $stmt->get_result();
    }

} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpaceMoto - Venda de Equipamentos e Peças de Motos</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var csrf_token = '<?php echo $csrf_token; ?>';
        var isLoggedIn = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;

        function showDetails(productId) {
            $.ajax({
                url: '/pages/produtos/getitemdet.php',
                type: 'GET',
                data: { id: productId },
                success: function(data) {
                    var product = JSON.parse(data);
                    if (!product.error) {
                        $('#productName').text(product.name);
                        $('#productImage').attr('src', '/uploads/products/' + product.image);
                        $('#productDescription').text(product.description);
                        $('#productPrice').text('R$ ' + product.price);
                        $('#productCategory').text(product.category);
                        $('#productId').val(product.id);
                        $('#productModal').show();
                    } else {
                        alert('Erro: ' + product.error);
                    }
                },
                error: function() {
                    alert('Erro ao buscar detalhes do produto.');
                }
            });
        }

        function closeModal() {
            $('#productModal').hide();
        }
    </script>
    <meta name="description" content="SpaceMoto, sua loja especializada em equipamentos e peças para motos com entrega rápida e suporte 24/7.">
    <meta name="keywords" content="moto, peças de moto, equipamentos de moto, SpaceMoto">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <!-- Banner principal -->
    <div class="hero-banner">
        <div class="banner-content">
            <h1>Bem-vindo à SpaceMoto</h1>
            <p>Sua loja especializada em equipamentos e peças para motos</p>
        </div>
    </div>

    <!-- Seção de Destaques -->
    <section class="featured-section">
        <div class="container">
            <h2>Produtos em Destaque</h2>
            <div class="products-grid">
                <?php if ($featured_result && $featured_result->num_rows > 0): ?>
                    <?php while ($product = $featured_result->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="<?php echo getProductImage(htmlspecialchars($product['image'])); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-description">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                                </p>
                                <p class="product-price">R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></p>
                                <div class="product-actions">
                                    <button onclick="showDetails(<?php echo $product['id']; ?>)" 
                                            class="btn btn-primary">Ver Detalhes</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php elseif (isset($_GET['query'])): ?>
                    <p>Nenhum produto encontrado para "<?php echo htmlspecialchars($_GET['query']); ?>".</p>
                <?php else: ?>
                    <p>Nenhum produto em destaque encontrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Modal de Detalhes do Produto -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="productName"></h2>
            <img id="productImage" src="" alt="Imagem do Produto">
            <p id="productDescription"></p>
            <p id="productPrice"></p>
            <p id="productCategory"></p>
        </div>
    </div>

    <div id="searchResults"></div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
<script src="https://accounts.google.com/gsi/client" async defer></script>
</html>
