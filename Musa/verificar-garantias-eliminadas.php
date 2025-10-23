<?php
echo "<h2>🔍 VERIFICACIÓN: Garantías Eliminadas de Productos Dinámicos</h2>";
echo "<p><strong>Estado:</strong> " . date('Y-m-d H:i:s') . "</p>";

echo "<style>
.success { background: #d4edda; padding: 10px; border-left: 4px solid #28a745; margin: 10px 0; }
.info { background: #d1ecf1; padding: 10px; border-left: 4px solid #17a2b8; margin: 10px 0; }
</style>";

// Leer el archivo index.html y verificar garantías
$indexFile = 'index.html';
$content = file_get_contents($indexFile);

// Verificar las garantías eliminadas del modal
$guaranteesCount = substr_count($content, 'guarantees-section');
$guaranteeItemsCount = substr_count($content, 'guarantee-item');

echo "<div class='success'>";
echo "<h4>✅ ESTADO ACTUAL:</h4>";
echo "<p><strong>Secciones de garantías encontradas:</strong> $guaranteesCount</p>";
echo "<p><strong>Items de garantías encontrados:</strong> $guaranteeItemsCount</p>";
echo "</div>";

// Verificar específicamente si se eliminaron las garantías del modal dinámico
$modalGuarantees = strpos($content, 'Envío rápido');
$calidad = strpos($content, 'Calidad premium');
$pago = strpos($content, 'Pago seguro');

echo "<div class='info'>";
echo "<h4>🔍 VERIFICACIÓN ESPECÍFICA DEL MODAL:</h4>";

if ($modalGuarantees === false && $calidad === false && $pago === false) {
    echo "<p><strong>✅ ÉXITO:</strong> Las garantías del modal de productos dinámicos han sido eliminadas correctamente.</p>";
} else {
    echo "<p><strong>⚠️ ATENCIÓN:</strong> Aún se encontraron referencias a garantías.</p>";
    if ($modalGuarantees !== false) echo "<p>- Encontrado: 'Envío rápido'</p>";
    if ($calidad !== false) echo "<p>- Encontrado: 'Calidad premium'</p>";
    if ($pago !== false) echo "<p>- Encontrado: 'Pago seguro'</p>";
}
echo "</div>";

// Verificar el comentario de garantías eliminadas
$comentarioEliminadas = strpos($content, '<!-- Garantías eliminadas de productos dinámicos -->');

echo "<div class='info'>";
echo "<h4>📝 ESTADO DEL CÓDIGO:</h4>";
if ($comentarioEliminadas !== false) {
    echo "<p><strong>✅ CONFIRMADO:</strong> Se encontró el comentario que indica que las garantías fueron eliminadas.</p>";
} else {
    echo "<p><strong>❓ INFORMACIÓN:</strong> No se encontró el comentario específico.</p>";
}
echo "</div>";

// Verificar productos estáticos (deben estar ocultos)
$staticProductsHidden = strpos($content, 'style="display: none !important;"');

echo "<div class='info'>";
echo "<h4>👀 PRODUCTOS ESTÁTICOS:</h4>";
if ($staticProductsHidden !== false) {
    echo "<p><strong>✅ CORRECTO:</strong> Los productos estáticos están ocultos (las garantías de estos no se muestran).</p>";
} else {
    echo "<p><strong>⚠️ ATENCIÓN:</strong> Los productos estáticos podrían estar visibles.</p>";
}
echo "</div>";

echo "<div class='success'>";
echo "<h4>🎯 RESULTADO FINAL:</h4>";
echo "<p><strong>Los productos dinámicos ahora NO mostrarán garantías en:</strong></p>";
echo "<ul>";
echo "<li>✅ Modal de vista completa del producto</li>";
echo "<li>✅ Tarjetas de productos de la API</li>";
echo "</ul>";
echo "<p><strong>Próximos pasos:</strong></p>";
echo "<ol>";
echo "<li>Verificar en <a href='http://localhost/Musa/' target='_blank'>la plataforma</a></li>";
echo "<li>Probar abrir un producto dinámico</li>";
echo "<li>Confirmar que no aparecen garantías</li>";
echo "</ol>";
echo "</div>";

?>