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
            echo "<p style='color:red'>Veuillez remplir tous les champs marqués d'un *.</p>";
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
            echo "<p style='color:red'>Veuillez remplir tous les champs marqués d'un *.</p>";
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

// $request=$db->query("select * from hero;");

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
    <form action="index.php" method="get">
    <label for="search">Rechercher</label>
    <input type="text" name="search" id="search">
    <input type="submit" value="Search">
    </form>';

// Edition d'un héros
if ($editHero instanceof Hero) {
    echo '
    <form action="index.php" method="post" style="margin-top:15px;padding:10px;border:1px solid #ccc;">
        <h3>Modifier le héros #'.htmlspecialchars($editHero->getId()).'</h3>
        <input type="hidden" name="mode" value="update">
        <input type="hidden" name="id" value="'.htmlspecialchars($editHero->getId()).'">
        <label for="edit_name">Nom du héros* :</label>
        <input type="text" name="name" id="edit_name" value=" required'.htmlspecialchars($editHero->getName(), ENT_QUOTES).'">
        <br>
        <label for="edit_description">Description* :</label>
        <input type="text" name="description" id="edit_description" value=" required'.htmlspecialchars($editHero->getDescription(), ENT_QUOTES).'">
        <br>
        <label for="edit_power">Pouvoir* :</label>
        <input type="text" name="power" id="edit_power" value="'.htmlspecialchars($editHero->getPower(), ENT_QUOTES).'">
        <label for="edit_weakness">Faiblesse* :</label>
        <input type="text" name="weakness" id="edit_weakness" value="'.htmlspecialchars($editHero->getWeakness(), ENT_QUOTES).'">
        <input type="submit" value="Enregistrer">
        <a href="index.php" style="margin-left:10px;">Annuler</a>
    </form>';
}

// Ajout d'un héros
echo '
    <form action="index.php" method="post">
    <hr>
    <input type="hidden" name="mode" value="create">
    <label for="name">Nom du héros* :</label>
    <input type="text" name="name" id="name">
    <br>
    <label for="description">Description* :</label>
    <input type="text" name="description" id="description">
    <br>
    <label for="power">Pouvoir :</label>
    <input type="text" name="power" id="power">
    <label for="weakness">Faiblesse :</label>
    <input type="text" name="weakness" id="weakness">
    <input type="submit" value="Ajouter">

</form>';

// Affichage du tableau des héros
echo '<table border="5" cellpadding="10" style="border-collapse: collapse;">';
echo '<thead>';
echo '<tr>';
echo '<th>Nom du héros</th>';
echo '<th>Description</th>';
echo '<th>Pouvoir</th>';
echo '<th>Faiblesse</th>';
echo '<th>Actions</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($heroes as $hero) {
    echo "<tr>
    <td>".$hero->getName()."</td>
    <td>".$hero->getDescription()."</td>
    <td>".$hero->getPower()."</td>
    <td>".$hero->getWeakness()."</td>
    <td>
        <button onclick=\"window.location.href='index.php?edit=".$hero->getId()."'\">EDIT</button>
        <button onclick=\"window.location.href='index.php?delete=".$hero->getId()."'\">DELETE</button>
    </td>
    </tr>";
}

echo '</tbody>';
echo '</table>';
