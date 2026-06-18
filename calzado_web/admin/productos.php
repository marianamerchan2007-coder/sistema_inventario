<?php
session_start();
include("../config/conexion.php"); 
include("includes/auth.php");
include("includes/header.php");
include("includes/sidebar.php"); 
?>

<?php
$sql = "SELECT 
    p.id_producto, 
    p.nombre_producto, 
    MAX(i.ruta_imagen) AS ruta_imagen,
    c.nombre_categoria, 
    s.nombre_subcategoria, 
    p.precio, 
    GROUP_CONCAT(DISTINCT t.numero_talla ORDER BY t.numero_talla ASC SEPARATOR ', ') AS tallas,
    p.estado 
FROM producto p
INNER JOIN categorias c ON p.id_categoria = c.id_categoria
INNER JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
INNER JOIN producto_talla t ON p.id_producto = t.id_producto
LEFT JOIN producto_imagen i ON p.id_producto = i.id_producto AND i.principal = 1
GROUP BY 
    p.id_producto, p.nombre_producto, c.nombre_categoria, s.nombre_subcategoria, p.precio, p.estado
ORDER BY p.fecha_creacion DESC";

$stmt = $conexion->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="main">
        <div class="cabecera-productos d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 mt-2">
            <h5>Lista de productos</h5>
            <div class="input-group buscador-producto">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" placeholder="Buscar producto"  id="buscarProducto" class="form-control">
            </div>
            <a href="agregar_producto.php" class="btn btn-agregar">+ Nuevo producto</a>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div id="alertSuccess" class="alert alert-success alert-dismissible fade show">
                Producto <strong><?= htmlspecialchars($_GET['producto']) ?></strong> agregado correctamente.
                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
                </button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría - tipo</th>
                        <th>Precio</th>
                        <th>Tallas</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <tr class="fila-producto"
                            data-producto="<?= strtolower($producto['nombre_producto']); ?>"
                            data-categoria="<?= $producto['nombre_categoria']; ?>"
                            data-estado="<?= $producto['estado']; ?>">
                            <td data-label="Producto">
                                <div class="producto-item">
                                    <img src="<?= $producto['ruta_imagen']; ?>" alt="<?= $producto['nombre_producto']; ?>">

                                    <div class="producto-info">
                                        <div class="producto-nombre">
                                            <?= $producto['nombre_producto']; ?>
                                        </div>

                                        <div class="producto-categorias-mobile">
                                            <span class="badge categoria-badge">
                                                <?= $producto['nombre_categoria']; ?> 
                                            </span>

                                            <span class="badge tipo-badge">
                                                <?= $producto['nombre_subcategoria']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Categoría" class="col-categoria">
                                <span class="badge categoria-badge">
                                    <?= $producto['nombre_categoria']; ?> 
                                </span>

                                <span class="badge tipo-badge">
                                    <?= $producto['nombre_subcategoria']; ?>
                                </span>
                            </td>
                            <td  data-label="Precio">
                                <span class="precio-producto fw-medium">
                                    $<?= number_format($producto['precio'], 0, ',', '.'); ?>
                                </span>
                            </td>

                            <td data-label="Tallas">
                                <small class="text-muted">
                                    <?= $producto['tallas'];?>
                                </small>
                            </td>
                            <td data-label="Estado">
                                <small class="fw-light">
                                    <?= $producto['estado'];?>
                                </small>
                            </td>
                            <td data-label="Acciones">
                                <a href="editar_producto.php?id=<?= $producto['id_producto']; ?>" class="btn btn-small btn-editar"><i class="bi bi-pencil-square"></i></a>
                                <a href="eliminar_producto.php?id=<?= $producto['id_producto']; ?>" onclick="this.blur(); return confirm('¿Eliminar este producto?')" class="btn btn-small btn-eliminar"><i class="bi bi-trash3"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div id="mensajeBusqueda"
            class="alert alert-warning text-center mt-3" style="display:none;">
            No se encontraron productos con este nombre.
        </div>
</div>    
<?php include("includes/footer.php"); ?>

<script>
const buscador =
    document.getElementById('buscarProducto');

const mensajeBusqueda =
    document.getElementById('mensajeBusqueda');

buscador.addEventListener('keyup', function(){

    const texto =
        this.value.toLowerCase();

    const filas =
        document.querySelectorAll('.fila-producto');

    let encontrados = 0;

    filas.forEach(fila => {

        const nombre =
            fila.dataset.producto;

        if(nombre.includes(texto)){

            fila.style.display = '';
            encontrados++;

        }else{

            fila.style.display = 'none';
        }
    });

    mensajeBusqueda.style.display =
        encontrados === 0 ? 'block' : 'none';
});
</script>

<style>
body{
    font-family: 'Inter', sans-serif;
}
</style>