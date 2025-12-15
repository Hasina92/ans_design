<?php
$page = 'blog';
require_once 'ans-design-backoffice/config/db.php';
require_once 'init_user.php';
$articles = $pdo->query("SELECT * FROM blog ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
include 'header.php';

// Vérifier si un ID d’article est présent
if (!isset($_GET['id'])) {
    die("Article introuvable.");
}

$id = intval($_GET['id']);

// Récupérer l’article
$stmt = $pdo->prepare("SELECT * FROM blog WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die("Article introuvable.");
}

// Si tu veux gérer une date automatiquement via PHP :
$date_article = date("d/m/Y", strtotime($article["created_at"] ?? "now"));

// Récupérer 3 autres articles récents
$recent = $pdo->prepare("SELECT * FROM blog WHERE id != ? ORDER BY id DESC LIMIT 3");
$recent->execute([$id]);
$recent_articles = $recent->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($article['titre']) ?> - ANS Design</title>
    <link rel="stylesheet" href="assets/css/fancybox.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <main>
        <!-- BANNER BLOG -->
        <section id="banner-catalogue" class="scrolltop banner-single-blog">
            <img src="assets/img/fond.png" alt="" class="bg-image">
            <div class="wrapper t-center">
                <div class="section-title">
                    <h2><?= htmlspecialchars($article['titre']) ?></h2>
                </div>
                <div class="date">
                    <span><?= $date_article ?></span>
                </div>
            </div>
        </section>

        <!-- SINGLE BLOG -->
        <section id="single-blog">
            <div class="wrapper">
                <div class="container-single-blog">
                    <!-- IMAGE -->
                    <div class="single-blog-img">
                        <img src="ans-design-backoffice/upload/<?= htmlspecialchars($article['image']) ?>" alt="">
                    </div>

                    <!-- CONTENU -->
                    <div class="single-blog-text">
                        <?= nl2br($article['contenu']) ?>
                    </div>
                </div>

                <!-- ARTICLES RÉCENTS -->
                <div class="other-blog">
                    <h2>Articles Récents</h2>
                    <?php $articles = array_slice($articles, 0, 5); ?>
                    <?php foreach ($articles as $a): ?>
                        <div class="card-other-blog">
                            <a href="single-blog.php?id=<?= $a['id'] ?>" class="link-box"></a>

                            <div class="card-img">
                                <img src="ans-design-backoffice/upload/<?= $a['image'] ?>" alt="">
                            </div>

                            <div class="card-text">
                                <h3><?= htmlspecialchars($a['titre']) ?></h3>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>

</html>