<?php
include 'Hero.php';

//Connection à la base de données
$user="root";
$pass="";
$dbname="herocorp";
$host="localhost";

$db=new PDO("mysql:host=$host;dbname=$dbname",$user,$pass);

// Gestion de l'ajout (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['name'], $_POST['description'], $_POST['power'], $_POST['weakness'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $power = trim($_POST['power']);
    $weakness = trim($_POST['weakness']);

    if ($name !== '' && $description !== '' && $power !== '' && $weakness !== '') {
        $stmt = $db->prepare("INSERT INTO hero (name, description, power, weakness) VALUES (:name, :description, :power, :weakness)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':power', $power);
        $stmt->bindParam(':weakness', $weakness);
        $stmt->execute();

        // Redirection pour éviter la double soumission (PRG)
        header('Location: index.php');
        exit;
    } else {
        echo "<p style='color:red'>Veuillez remplir tous les champs marqués d'un *.</p>";
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

// Ajout d'un héros
echo '
    <form action="index.php" method="post">
    <hr>
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
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($heroes as $hero) {
    echo "<tr>
    <td>".$hero->getName()."</td>
    <td>".$hero->getDescription()."</td>
    <td>".$hero->getPower()."</td>
    <td>".$hero->getWeakness()."</td>
    </tr>";
}

echo '</tbody>';
echo '</table>';
