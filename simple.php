<?php
// Definim la carpeta base
$baseDir = __DIR__ . '/uploads';
if (!is_dir($baseDir)) mkdir($baseDir, 0777, true);

// Definim la ruta actual segons GET['dir']
$currentDir = isset($_GET['dir']) ? realpath($baseDir . '/' . $_GET['dir']) : $baseDir;

// Evitem que l'usuari surti de la carpeta base
if (strpos($currentDir, $baseDir) !== 0) $currentDir = $baseDir;

// Handle upload
if (isset($_POST['upload']) && isset($_FILES['fileToUpload'])) {
    $targetFile = $currentDir . '/' . basename($_FILES['fileToUpload']['name']);
    move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $targetFile);
}

// Handle folder creation
if (isset($_POST['createFolder']) && !empty($_POST['folderName'])) {
    $newFolder = $currentDir . '/' . basename($_POST['folderName']);
    if (!is_dir($newFolder)) mkdir($newFolder, 0777, true);
}

// Handle delete
if (isset($_GET['delete'])) {
    $fileToDelete = $currentDir . '/' . basename($_GET['delete']);
    if (is_file($fileToDelete)) unlink($fileToDelete);
    if (is_dir($fileToDelete)) rmdir($fileToDelete);
}

// Handle download
if (isset($_GET['download'])) {
    $fileToDownload = $currentDir . '/' . basename($_GET['download']);
    if (file_exists($fileToDownload)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileToDownload) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fileToDownload));
        readfile($fileToDownload);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
<meta charset="UTF-8">
<title>File Manager Modern</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Inter', sans-serif; background: #f5f5f7; color: #1c1c1e; margin: 0; padding: 0; }
    header { padding: 20px; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
    h1 { margin: 0; font-weight: 600; font-size: 24px; }
    main { padding: 20px; max-width: 1000px; margin: auto; }
    form { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
    input[type="file"], input[type="text"] { padding: 10px; border: 1px solid #ccc; border-radius: 10px; flex: 1; }
    button { padding: 10px 20px; background: #007aff; color: #fff; border: none; border-radius: 10px; cursor: pointer; transition: 0.2s; }
    button:hover { background: #005bb5; }
    table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
    th, td { padding: 12px 15px; text-align: left; }
    th { font-weight: 500; font-size: 14px; text-transform: uppercase; color: #6e6e73; }
    tr { background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    tr td { border: none; }
    a { color: #007aff; text-decoration: none; font-weight: 500; }
    a:hover { text-decoration: underline; }
    .breadcrumb { margin-bottom: 20px; font-size: 14px; }
    .breadcrumb a { color: #007aff; text-decoration: none; }
    .breadcrumb a:hover { text-decoration: underline; }
</style>
</head>
<body>
<header>
    <h1>Simple File Manager</h1>
</header>
<main>

<!-- Breadcrumb per navegar carpetes -->
<div class="breadcrumb">
    <a href="?">Home</a>
    <?php
    $relativePath = str_replace($baseDir, '', $currentDir);
    $parts = array_filter(explode('/', $relativePath));
    $pathAcc = '';
    foreach ($parts as $part) {
        $pathAcc .= '/' . $part;
        echo " / <a href='?dir=" . urlencode(trim($pathAcc, '/')) . "'>$part</a>";
    }
    ?>
</div>

<!-- Formularis -->
<form action="?dir=<?= urlencode(trim($relativePath, '/')) ?>" method="post" enctype="multipart/form-data">
    <input type="file" name="fileToUpload" required>
    <button type="submit" name="upload">Pujar fitxer</button>
</form>

<form action="?dir=<?= urlencode(trim($relativePath, '/')) ?>" method="post">
    <input type="text" name="folderName" placeholder="Nom de la carpeta" required>
    <button type="submit" name="createFolder">Crear carpeta</button>
</form>

<!-- Llistar fitxers i carpetes -->
<table>
<tr>
    <th>Nom</th>
    <th>Tipus</th>
    <th>Accions</th>
</tr>
<?php
$items = scandir($currentDir);
foreach ($items as $item) {
    if ($item == '.' || $item == '..') continue;
    $fullPath = $currentDir . '/' . $item;
    $type = is_dir($fullPath) ? 'Carpeta' : 'Fitxer';
    echo "<tr>
            <td>";
    if ($type == 'Carpeta') {
        $nextDir = trim($relativePath . '/' . $item, '/');
        echo "<a href='?dir=" . urlencode($nextDir) . "'>📁 $item</a>";
    } else {
        echo $item;
    }
    echo "</td>
          <td>$type</td>
          <td>";
    if ($type == 'Fitxer') {
        echo "<a href='?dir=" . urlencode(trim($relativePath, '/')) . "&download=" . urlencode($item) . "'>Descarregar</a> | ";
    }
    echo "<a href='?dir=" . urlencode(trim($relativePath, '/')) . "&delete=" . urlencode($item) . "' onclick=\"return confirm('Segur que vols eliminar?');\">Eliminar</a>
          </td>
          </tr>";
}
?>
</table>

</main>
</body>
</html>
