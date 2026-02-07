<?php
// This script will generate basic CRUD for all tables
$tables = ['companies', 'applications', 'application_types', 'admins', 'roles'];

foreach ($tables as $table) {
    $dir = "crud/" . str_replace('_', '-', $table);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    // Create index.php
    $index_content = "<?php
require_once '../../config/database.php';
require_login();

\$sql = \"SELECT * FROM $table ORDER BY id DESC\";
\$result = \$conn->query(\$sql);
?>
<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navigation.php'; ?>
<main class=\"col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4\">
    <h1>" . ucfirst(str_replace('_', ' ', $table)) . "</h1>
    <a href='create.php' class='btn btn-success'>Yeni Əlavə</a>
    <table class='table'>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ad</th>
                <th>Əməliyyatlar</th>
            </tr>
        </thead>
        <tbody>
            <?php while(\$row = \$result->fetch_assoc()): ?>
            <tr>
                <td><?php echo \$row['id']; ?></td>
                <td><?php echo \$row['name'] ?? \$row['title'] ?? 'N/A'; ?></td>
                <td>
                    <a href='edit.php?id=<?php echo \$row[\"id\"]; ?>' class='btn btn-sm btn-primary'>Düzəlt</a>
                    <a href='delete.php?id=<?php echo \$row[\"id\"]; ?>' class='btn btn-sm btn-danger' onclick='return confirm(\"Silmək istədiyinizə əminsiniz?\")'>Sil</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>
<?php include '../../includes/footer.php'; ?>";
    
    file_put_contents("$dir/index.php", $index_content);
    
    // Create create.php
    $create_content = "<?php
require_once '../../config/database.php';
require_login();

if (\$_SERVER['REQUEST_METHOD'] == 'POST') {
    \$name = clean_input(\$_POST['name']);
    \$sql = \"INSERT INTO $table (name) VALUES ('\$name')\";
    if (\$conn->query(\$sql)) {
        header('Location: index.php?success=1');
    }
}
?>
<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navigation.php'; ?>
<main class=\"col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4\">
    <h1>Yeni Əlavə</h1>
    <form method='POST'>
        <div class='mb-3'>
            <label>Ad</label>
            <input type='text' name='name' class='form-control' required>
        </div>
        <button type='submit' class='btn btn-success'>Yadda Saxla</button>
    </form>
</main>
<?php include '../../includes/footer.php'; ?>";
    
    file_put_contents("$dir/create.php", $create_content);
    
    echo "Created CRUD for: $table<br>";
}
echo "CRUD generation completed!";
?>