<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HeroCorp - Gestion des Héros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #backToTop {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: none;
            z-index: 1050;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            border: none;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }

        #backToTop:hover {
            background-color: #0056b3;
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.4);
        }

        /*#backToTop.show {
            display: block;
            animation: fadeIn 0.3s;
        }*/

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">HeroCorp - Gestion des Héros</h1>

<?php
include 'Hero.php';

//Connection à la base de données
$user="root";
$pass="";
$dbname="herocorp";
$host="localhost";

$db=new PDO("mysql:host=$host;dbname=$dbname",$user,$pass);

// Gestion des actions CRUD (POST/GET)
$editHero = null; // servira à préremplir le formulaire d'édition

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode'])) {
    $mode = $_POST['mode'];
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $power = isset($_POST['power']) ? trim($_POST['power']) : '';
    $weakness = isset($_POST['weakness']) ? trim($_POST['weakness']) : '';

    if ($mode === 'create') {
        if ($name !== '' && $description !== '') {
            $stmt = $db->prepare("INSERT INTO hero (name, description, power, weakness) VALUES (:name, :description, :power, :weakness)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':power', $power);
            $stmt->bindParam(':weakness', $weakness);
            $stmt->execute();
            header('Location: index.php');
            exit;
        } else {
            echo '<div class="alert alert-danger">Veuillez remplir tous les champs marqués d\'un *.</div>';
        }
    } elseif ($mode === 'update' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if ($id > 0 && $name !== '' && $description !== '') {
            $stmt = $db->prepare("UPDATE hero SET name = :name, description = :description, power = :power, weakness = :weakness WHERE id = :id");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':power', $power);
            $stmt->bindParam(':weakness', $weakness);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            header('Location: index.php');
            exit;
        } else {
            echo '<div class="alert alert-danger">Veuillez remplir tous les champs marqués d\'un *.</div>';
        }
    }
}

// Suppression (GET)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $db->prepare("DELETE FROM hero WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        header('Location: index.php');
        exit;
    }
}

// Préparation de l'édition (GET)
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    if ($id > 0) {
        $stmt = $db->prepare("SELECT * FROM hero WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Hero');
        $editHero = $stmt->fetch();
    }
}

if(isset($_GET['search']) && !empty($_GET['search'])){
    $request=$db->prepare("select * from hero 
         where name like ? or description like ? or power like ? or weakness like ?");

    $valeur="%".$_GET['search']."%";
    $request->bindParam(1, $valeur);
    $request->bindParam(2, $valeur);
    $request->bindParam(3, $valeur);
    $request->bindParam(4, $valeur);
    $request->execute();
}
else {
    $request=$db->query("select * from hero");
}

//récupérer la requête
$request->setFetchMode(PDO::FETCH_CLASS, 'Hero');
$heroes=$request->fetchAll();

// Formulaire de recherche
echo '
    <div class="card mb-4 sticky-top">
        <div class="card-body">
            <form action="index.php" method="get" class="row g-3">
                <div class="col-auto">
                    <label for="search" class="col-form-label">Rechercher :</label>
                </div>
                <div class="col-auto">
                    <input type="text" name="search" id="search" class="form-control" placeholder="Nom, description...">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Rechercher
                    </button>
                    <a href="index.php" class="btn btn-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>';

// Edition d'un héros
if ($editHero instanceof Hero) {
    echo '
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Modifier le héros #'.htmlspecialchars($editHero->getId()).'</h5>
        </div>
        <div class="card-body">
            <form action="index.php" method="post">
                <input type="hidden" name="mode" value="update">
                <input type="hidden" name="id" value="'.htmlspecialchars($editHero->getId()).'">
                
                <div class="mb-3">
                    <label for="edit_name" class="form-label">Nom du héros* :</label>
                    <input type="text" name="name" id="edit_name" class="form-control" value="'.htmlspecialchars($editHero->getName(), ENT_QUOTES).'" required>
                </div>
                
                <div class="mb-3">
                    <label for="edit_description" class="form-label">Description* :</label>
                    <input type="text" name="description" id="edit_description" class="form-control" value="'.htmlspecialchars($editHero->getDescription(), ENT_QUOTES).'" required>
                </div>
                
                <div class="mb-3">
                    <label for="edit_power" class="form-label">Pouvoir :</label>
                    <input type="text" name="power" id="edit_power" class="form-control" value="'.htmlspecialchars($editHero->getPower(), ENT_QUOTES).'">
                </div>
                
                <div class="mb-3">
                    <label for="edit_weakness" class="form-label">Faiblesse :</label>
                    <input type="text" name="weakness" id="edit_weakness" class="form-control" value="'.htmlspecialchars($editHero->getWeakness(), ENT_QUOTES).'">
                </div>
                
                <button type="submit" class="btn btn-success">Enregistrer</button>
                <a href="index.php" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>';
}

// Ajout d'un héros
echo '
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Ajouter un nouveau héros</h5>
        </div>
        <div class="card-body">
            <form action="index.php" method="post">
                <input type="hidden" name="mode" value="create">
                
                <div class="mb-3">
                    <label for="name" class="form-label">Nom du héros* :</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description* :</label>
                    <input type="text" name="description" id="description" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label for="power" class="form-label">Pouvoir :</label>
                    <input type="text" name="power" id="power" class="form-control">
                </div>
                
                <div class="mb-3">
                    <label for="weakness" class="form-label">Faiblesse :</label>
                    <input type="text" name="weakness" id="weakness" class="form-control">
                </div>
                
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </form>
        </div>
    </div>';

// Affichage du tableau des héros
    echo '<div class="card mb-5">';
        echo '<div class="card-header bg-dark text-white">';
            echo '<h5 class="mb-0">Liste des héros ('.count($heroes).')</h5>';
        echo '</div>';
        echo '<div class="card-body p-0">';
            echo '<div class="table-responsive">';
            echo '<table class="table table-striped table-hover mb-0">';
                echo '<thead class="table-dark">';
                    echo '<tr>';
                    echo '<th>Nom du héros</th>';
                    echo '<th>Description</th>';
                    echo '<th>Pouvoir</th>';
                    echo '<th>Faiblesse</th>';
                    echo '<th class="text-center">Actions</th>';
                    echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                foreach ($heroes as $hero) {
                    echo "
                        <tr>
                        <td><strong>".$hero->getName()."</strong></td>
                        <td>".$hero->getDescription()."</td>
                        <td>".$hero->getPower()."</td>
                        <td>".$hero->getWeakness()."</td>
                        <td class='text-center'>
                            <a href='index.php?edit=".$hero->getId()."' class='btn btn-sm btn-warning'>Modifier</a>
                            <a href='index.php?delete=".$hero->getId()."' class='btn btn-sm btn-danger' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce héros ?\")'>Supprimer</a>
                        </td>
                    </tr>";
                }

                echo '</tbody>';
            echo '</table>';
        echo '</div>';
    echo '</div>';
echo '</div>';
?>
    </div>

    <!-- Bouton Back to Top -->
    <button id="backToTop" title="Retour en haut">
        ↑
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Récupération du bouton
        const backToTopButton = document.getElementById('backToTop');

        // Afficher/Masquer le bouton lors du scroll
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });

        // Remonter en haut lors du clic
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
