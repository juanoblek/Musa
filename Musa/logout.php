<?php
// logout.php - Manejo del cierre de sesión
session_start();

// Destruir todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Redirigir al login con mensaje de confirmación
header('Location: login.php?logout=1');
exit();
?>
