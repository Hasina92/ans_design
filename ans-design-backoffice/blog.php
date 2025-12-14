<?php
$currentPage = 'blog';
require_once 'includes/header.php';
require_once 'config/db.php';

$message = '';

/*---------------------------------------------
    SUPPRIMER UN ARTICLE
----------------------------------------------*/
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM blog WHERE id = ?");
    $stmt->execute([$delete_id]);

    header("Location: blog.php");
    exit;
}

/*---------------------------------------------
    AJOUT / MODIFICATION D’UN ARTICLE
----------------------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre = $_POST['titre'];
    $extrait = $_POST['extrait'];
    $contenu = $_POST['contenu'];
    $edit_id = $_POST['edit_id'] ?? '';
    $image = $_POST['old_image'] ?? '';

    // Upload image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

        $uploadDir = __DIR__ . '/upload/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid() . "." . $ext;

        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
    }

    if (!empty($edit_id)) {
        // Mise à jour
        $stmt = $pdo->prepare("UPDATE blog SET titre=?, extrait=?, contenu=?, image=? WHERE id=?");
        $stmt->execute([$titre, $extrait, $contenu, $image, $edit_id]);
        $message = "Article modifié avec succès !";
    } else {
        // Ajout
        $stmt = $pdo->prepare("INSERT INTO blog (titre, extrait, contenu, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$titre, $extrait, $contenu, $image]);
        $message = "Article ajouté avec succès !";
    }
}

/*---------------------------------------------
    RÉCUPÉRER LES ARTICLES
----------------------------------------------*/
$articles = $pdo->query("SELECT * FROM blog ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
    <h1>Blog</h1>

    <?php if (!empty($message)): ?>
        <p style="color:green;"><?= $message ?></p>
    <?php endif; ?>

    <button id="showForm"
        style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px; border: none; font-size: 16px;">+
        Ajouter un article</button>
</div>

<div class="panel">
    <!-- Formulaire caché -->
    <div id="formContainer" style="display:none; margin-top:20px;" class="formulaire_ajout">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="edit_id" id="edit_id">
            <input type="hidden" name="old_image" id="old_image">

            <label>Titre :</label><br>
            <input type="text" name="titre" id="titre" required><br><br>

            <label>Extrait :</label><br>
            <textarea name="extrait" id="extrait" required></textarea><br><br>

            <label>Contenu :</label><br>
            <textarea name="contenu" id="contenu" required style="height:150px;"></textarea><br><br>

            <label>Image :</label><br>
            <input type="file" name="image" accept="image/*"><br><br>

            <button type="submit" id="submitBtn">+
                Ajouter un article</button>
        </form>
    </div>

    <hr>

    <!-- Tableau des articles -->
    <table border="1" cellpadding="10">
        <tr>
            <th>Titre</th>
            <th>Extrait</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($articles as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['titre']) ?></td>

                <td><?= substr(htmlspecialchars($a['extrait']), 0, 100) ?>...</td>

                <td style="display: flex; gap: 20px; border: none; width: 250px">
                    <a href="blog.php?delete_id=<?= $a['id'] ?>" onclick="return confirm('Supprimer cet article ?');"
                        style="padding: 10px 20px; background-color: #DF4D34; color: white; text-decoration: none; text-align: center; border-radius: 5px; border: none; font-size: 16px;">Supprimer</a>
                    <button onclick="editBlog(
                        `<?= addslashes($a['id']) ?>`,
                        `<?= addslashes($a['titre']) ?>`,
                        `<?= addslashes($a['extrait']) ?>`,
                        `<?= addslashes($a['contenu']) ?>`,
                        `<?= addslashes($a['image']) ?>`
                    )"
                        style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px; border: none; font-size: 16px;">Modifier</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <script>
        document.getElementById('showForm').onclick = function () {
            const form = document.getElementById('formContainer');
            form.style.display = form.style.display === "none" ? "block" : "none";

            document.getElementById('submitBtn').textContent = 'Ajouter';
            document.getElementById('edit_id').value = '';
            document.getElementById('titre').value = '';
            document.getElementById('extrait').value = '';
            document.getElementById('contenu').value = '';
            document.getElementById('old_image').value = '';
        };

        function editBlog(id, titre, extrait, contenu, image) {
            document.getElementById('formContainer').style.display = 'block';

            document.getElementById('edit_id').value = id;
            document.getElementById('titre').value = titre;
            document.getElementById('extrait').value = extrait;
            document.getElementById('contenu').value = contenu;
            document.getElementById('old_image').value = image;

            document.getElementById('submitBtn').textContent = 'Modifier';
        }
    </script>
</div>