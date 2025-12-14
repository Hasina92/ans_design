<?php
$page = 'blog';
require_once 'ans-design-backoffice/config/db.php';
require_once 'init_user.php'; 
$articles = $pdo->query("SELECT * FROM blog ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
// On inclut le header qui contient déjà session_start() et la logique d'auth
include 'header.php';
?>
<!-- MAIN -->
<main>
    <!-- BANNER BLOG -->
    <section id="banner-catalogue" class="scrolltop banner-blog">
        <img src="assets/img/fond.png" alt="" class="bg-image">
        <div class="wrapper t-center">
            <h1>Nos Derniers Conseils</h1>
            <p class="medium white">Retrouvez nos actualités, astuces et inspirations pour une communication
                réussie.</p>
        </div>
    </section>
    <!--CONSEILS-->
    <section id="conseils">
        <div class="wrapper">
            <div class="container-conseils">
                <?php foreach ($articles as $a): ?>
                    <div class="card-conseil">

                        <!-- Lien vers single post -->
                        <a href="single-blog.php?id=<?= $a['id'] ?>" class="link-box"></a>

                        <div class="conseil-img">
                            <img src="ans-design-backoffice/upload/<?= $a['image'] ?>" alt="">
                        </div>

                        <div class="conseil-text">
                            <h3><?= htmlspecialchars($a['titre']) ?></h3>

                            <!-- Extrait -->
                            <p><?= htmlspecialchars(substr($a['extrait'], 0, 200)) ?>...</p>

                            <a href="single-blog.php?id=<?= $a['id'] ?>">Lire la suite →</a>
                        </div>

                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </section>
</main>
<!-- FOOTER -->
<?php
// On inclut le footer
include 'footer.php';
?>