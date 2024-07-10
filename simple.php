<?php
// Configuració
$baseDir = __DIR__; // Directori base
$currentDir = isset($_GET['dir']) ? $baseDir . '/' . $_GET['dir'] : $baseDir;

// Funció per eliminar arxius
function deleteFile($file) {
    if (file_exists($file)) {
        unlink($file);
    }
}

// Funció per eliminar carpetes
function deleteFolder($folder) {
    if (is_dir($folder)) {
        rmdir($folder);
    }
}

// Processar la pujada d'arxius
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileToUpload'])) {
    $targetFile = $currentDir . '/' . basename($_FILES['fileToUpload']['name']);
    move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $targetFile);
    header('Location: ' . $_SERVER['PHP_SELF'] . '?dir=' . urlencode($_GET['dir']));
    exit;
}

// Processar la creació de carpetes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folderName'])) {
    $newFolder = $currentDir . '/' . basename($_POST['folderName']);
    if (!file_exists($newFolder)) {
        mkdir($newFolder);
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?dir=' . urlencode($_GET['dir']));
    exit;
}

// Processar l'eliminació d'arxius o carpetes
if (isset($_GET['delete'])) {
    $itemToDelete = $currentDir . '/' . basename($_GET['delete']);
    if (is_dir($itemToDelete)) {
        deleteFolder($itemToDelete);
    } else {
        deleteFile($itemToDelete);
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?dir=' . urlencode($_GET['dir']));
    exit;
}

// Llistar arxius i directoris
$items = array_diff(scandir($currentDir), array('.', '..'));
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Simple File Manager</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Simple File Manager</h1>

    <h2>Pujar un fitxer</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" required>
        <button type="submit">Pujar fitxer</button>
    </form>

    <h2>Crear una carpeta</h2>
    <form action="" method="post">
        <input type="text" name="folderName" placeholder="Nom de la carpeta" required>
        <button type="submit">Crear carpeta</button>
    </form>

    <h2>Elements en el directori</h2>
    <table>
        <tr>
            <th>Nom de l'element</th>
            <th>Tipus</th>
            <th>Acció</th>
        </tr>
        <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <?php if (is_dir($currentDir . '/' . $item)): ?>
                        <a href="?dir=<?php echo urlencode((isset($_GET['dir']) ? $_GET['dir'] . '/' : '') . $item); ?>"><?php echo htmlspecialchars($item); ?></a>
                    <?php else: ?>
                        <?php echo htmlspecialchars($item); ?>
                    <?php endif; ?>
                </td>
                <td><?php echo is_dir($currentDir . '/' . $item) ? 'Carpeta' : 'Fitxer'; ?></td>
                <td>
                    <a href="?dir=<?php echo urlencode(isset($_GET['dir']) ? $_GET['dir'] : ''); ?>&delete=<?php echo urlencode($item); ?>" onclick="return confirm('Estàs segur que vols eliminar aquest element?');">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

