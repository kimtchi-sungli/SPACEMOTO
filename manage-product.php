<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db_connect.php';

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: /pages/userlogin/login.php');
    exit();
}

// Fetch products
$products_query = "SELECT * FROM products ORDER BY created_at DESC";
$products_result = $conn->query($products_query);

include __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Gerenciar Produtos</h2>
        <a href="add-product.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Adicionar Produto
        </a>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($product = $products_result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($product['id']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full object-cover" 
                                         src="<?php echo isset($product['image']) ? '/uploads/products/' . htmlspecialchars($product['image']) : '/assets/images/default-product.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-xs truncate">
                                <?php echo htmlspecialchars($product['description']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                €<?php echo number_format($product['price'], 2, ',', '.'); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $stockClass = $product['stock'] > 0 
                                ? 'bg-green-100 text-green-800' 
                                : 'bg-red-100 text-red-800';
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $stockClass; ?>">
                                <?php echo htmlspecialchars($product['stock']); ?> unidades
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="edit-product.php?id=<?php echo $product['id']; ?>" 
                               class="text-indigo-600 hover:text-indigo-900 mx-2">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-md hover:bg-blue-200">
                                    Editar
                                </span>
                            </a>
                            <a href="delete-product.php?id=<?php echo $product['id']; ?>" 
                               class="text-red-600 hover:text-red-900"
                               onclick="return confirm('Tem certeza que deseja excluir este produto?');">
                                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-md hover:bg-red-200">
                                    Excluir
                                </span>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .container {
        max-width: 1280px;
    }
    
    .rounded-lg {
        border-radius: 0.5rem;
    }
    
    .shadow-md {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .transition-colors {
        transition: background-color 0.2s ease;
    }
    
    .truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .hover\:bg-gray-50:hover {
        background-color: #F9FAFB;
    }
    
    .hover\:bg-blue-200:hover {
        background-color: #BFDBFE;
    }
    
    .hover\:bg-red-200:hover {
        background-color: #FCA5A5;
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>