<?php

function user($id){
    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');
    $sql = "SELECT * FROM golf_users WHERE id = :id";

    $req = $bdd->prepare($sql);
    $req->execute(array(
        ':id' => $id
    ));

    $result = $req->fetch(PDO::FETCH_ASSOC);

    return $result;
}


function userConnection($pseudo, $password){
    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');

    if(empty($pseudo) || empty($password)){
        return false;
    } else{
    	$sql = " SELECT * FROM golf_users WHERE pseudo = :pseudo AND password = :password ";
	    $req = $bdd->prepare($sql);
	    $req->execute(array(
	    ':pseudo' => $pseudo,
	    ':password' => $password
	    ));
	    $result = $req->fetch(PDO::FETCH_ASSOC);
	    if($result == true){
	        $_SESSION['id'] = $result['id'];
	        $_SESSION['pseudo'] = $result['pseudo'];
	        $_SESSION['password'] = $result['password'];
	        return $result;

	    } else{
	        return false;
	    }
    }
    
}


function userRegistration($pseudo, $password, $HCP){
    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');

    if(empty($pseudo) || empty($password) || empty($HCP)){
        return false;
    } else{
        $verifPseudo = isPseudoAvailable($pseudo);

        if($verifPseudo == true){
            $sql = "INSERT INTO golf_users SET pseudo = :psd, password = :pass, HCP = :HCP";

            $req = $bdd->prepare($sql);
            $req->execute(array(
                ':psd' => $pseudo,
                ':pass' => $password,
                ':HCP' => $HCP
            ));
            return true;
        } else{
            return false;
        }
    }

}

function isPseudoAvailable($pseudo){

    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');

    $sql = "SELECT COUNT(*) AS count FROM golf_users WHERE pseudo = :psd";
    $req = $bdd->prepare($sql);
    $req->execute(array(
        ':psd' => $pseudo
    ));
    $result = $req->fetch();
    if($result['count'] > 0){
        return false;
    }else{
        return true;
    }
}

function updateUser($id, $pseudo, $password, $HCP){

    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');

    $user = user($id);

    $pseudo == null ? $pseudo = $user['pseudo'] : $pseudo = $pseudo ;
    $password == null ? $password = $user['password'] : $password = $password ;
    $HCP == null ? $HCP = $user['HCP'] : $HCP = $HCP ;

    if($id != ''){
    	 $sql = "UPDATE golf_users SET pseudo = :pseudo, password= :pass, HCP = :HCP WHERE id = :id ";

        $req = $bdd->prepare($sql);
        $req->execute(array(
            ':pass' => $password,
            ':pseudo' => $pseudo,
            ':HCP' => $HCP,
            ':id' => $id
        ));

        return true;
    }else{
    	return false;
    }

}

function deleteUser ($id){

    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');

    $sql = "DELETE FROM golf_users WHERE id = :id";

    $req = $bdd->prepare($sql);
    $req->execute(array(
        ':id' => $id
    ));
    return true;


}

function updateProfilPicture($imgInfos, $user_id){

    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');

    //on définit les extensions autorisées
    $allowedExtensions = array('.gif', '.png' ,'.jpg', '.jpeg');

    //on définit les types MIME autorisés
    $allowedMime = array("image/gif", "image/png", "image/jpeg");

    //on définit la largeur et la hauteur autorisée
    $width = 300;
    $height = 300;

    //on définit le dossier ou on veut enregistrer l'image
    $dir = 'profil_pic/';


    //ON RÉCUPÈRE LES INFORMATIONS DU FICHIER ENVOYÉ

    //on récupère l'extension du fichier envoyé (image ou non)
    $extension = strrchr($imgInfos['name'],'.');

    //on récupère le type MIME
    $mimeType = $imgInfos['type'];

    //on récupère des informations sur l'image dont la largeur [0] et la hauteur [1]
    //getimagesize retoune un tableau
    $sizes = getimagesize($imgInfos['tmp_name']);

    //ON EFFECTUE LES VÉRIFICATIONS
    //si l'extension est bonne
    if(in_array($extension, $allowedExtensions)){
        //echo'extension';
        //si le type MIME est bon
        if(in_array($mimeType, $allowedMime)){

            //echo 'mime';
            //si la largeur et la hauteur correspondent
            if($width >= $sizes[0] && $height >= $sizes[1]){

                //RENNOMAGE DE L'IMAGE POUR EVITER DIVERS PROBLEMES (espacements, caractères spéciaux, noms similaires ...)
                //Modèle : profile-pic-{user_id}{.extension}
                $imgName = 'profile-pic-'.$user_id.$extension;

                //INSERTION EN BASE DE DONNÉES | ON UTILISE UPDATE CAR L'UTILISATEUR EXISTE DÉJÀ
                $sql = "UPDATE golf_users SET image = :pic WHERE id = :id ";

                $req = $bdd->prepare($sql);
                $req->execute(array(
                    ':pic' => $imgName,
                    ':id' => $user_id
                ));

                //ON SUPPRIME L'ANCIENNE IMAGE DANS LES DOSSIERS
                unlink($dir.$_SESSION['picture']);

                //ENREGISTREMENT DE L'IMAGE DANS LES DOSSIERS
                move_uploaded_file($imgInfos['tmp_name'], $dir.$imgName);

                //ON MODIFIE L'IMAGE DANS LA SESSION
                //$_SESSION['picture'] = $imgName;

                return true;

            }
            else{
                return 'La taille de l\'image ne doit pas dépasser '.$width.'px de largeur et '.$height.' de hauteur';
            }

        }
        else{
            return 'Seul les images gif png et jpg sont autorisées';
        }

    }
    else{
        return 'Seul les extensions .gif .png et .jpg sont autorisées';
    }

}

function enregistrer($joueur_0, $joueur_1, $joueur_2, $joueur_3, $score_0, $score_1, $score_2, $score_3, $nom, $adresse, $nb_trous){

    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');

    $date = date("Y-m-d H:i:s");

    if(empty($joueur_0) || empty($score_0) || empty($nom) || empty($adresse)){
        return false;
    } else{
        $sql = "INSERT INTO golf_enregistrer (id_joueur_0, id_joueur_1, id_joueur_2, id_joueur_3, score_j0, score_j1 , score_j2 , score_j3, date, nom , adresse , nb_trous) VALUES ('".$joueur_0."','".$joueur_1."','".$joueur_2."','".$joueur_3."','".$score_0."','".$score_1."','".$score_2."','".$score_3."', '".$date."', '".$nom."','".$adresse."','".$nb_trous."')";

        $req = $bdd->prepare($sql);
        $req->execute();

        return true;
    }

}

function allParties($id){
    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');

    $sql = "SELECT * FROM golf_enregistrer
    			WHERE (id_joueur_0 = :id || id_joueur_1 = :id || id_joueur_2 = :id || id_joueur_3 = :id)";

    $req = $bdd->prepare($sql);
    $req->execute(array(
        ':id' => $id
    ));

    $response =  array();
    while ($row = $req->fetch(PDO::FETCH_ASSOC)){
    	$response[] = $row;
    }
    if(count($response) == 0){
        return false;
    } else{
        return $response;
    } 
}

function infosPartie($id){
    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');

    $sql = "SELECT * FROM golf_enregistrer WHERE id = :id";

    $req = $bdd->prepare($sql);
    $req->execute(array(
        ':id' => $id
    ));
    $result = $req->fetch(PDO::FETCH_ASSOC);

    $joueur = array();
    if($result['id_joueur_0'] != '0') $joueur[] .= $result['id_joueur_0'];
    if($result['id_joueur_1'] != '0') $joueur[] .= $result['id_joueur_1'];
    if($result['id_joueur_2'] != '0') $joueur[] .= $result['id_joueur_2'];
    if($result['id_joueur_3'] != '0') $joueur[] .= $result['id_joueur_3'];

    $response = array();
    for($i=0; $i<count($joueur); $i++){

    	$pseudo = "SELECT pseudo FROM golf_users WHERE id = :id_joueur";
    	$req1 = $bdd->prepare($pseudo);
	    $req1->execute(array(
	        ':id_joueur' => $joueur[$i]
	    ));
	    $response[] = $req1->fetch(PDO::FETCH_ASSOC);
    }

    $test = $result + $response;

    return $test;
}


function addFriend($id, $idFriend){
    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');

    if(empty($id) || empty($idFriend)){
        return false;
    } else{
        $verifFriend = alreadyFriend($id, $idFriend);

        if($verifFriend == true){
            $sql = "INSERT INTO golf_amis SET id_mon = :id, id_ami = :idFriend";

	        $req = $bdd->prepare($sql);
	        $req->execute(array(
	            ':id' => $id,
	            ':idFriend' => $idFriend
	        ));
            return true;
        } else{
            return 'Vous êtes déjà ami.';
        }
    }
}

function alreadyFriend($id, $idFriend){

    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');

    $sql = "SELECT COUNT(*) AS count FROM golf_amis WHERE id_mon = :id AND id_ami = :idFriend";
    $req = $bdd->prepare($sql);
    $req->execute(array(
        ':id' => $id,
        ':idFriend' => $idFriend
    ));
    $result = $req->fetch();
    if($result['count'] > 0){
        return false;
    }else{
        return true;
    }
}

function infosFriend($id){
    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');

    $sql = "SELECT * FROM golf_users WHERE id = :id";

    $req = $bdd->prepare($sql);
    $req->execute(array(
        ':id' => $id
    ));

    $result = $req->fetch(PDO::FETCH_ASSOC);

    return $result;
}

function allFriend($id){
    $bdd = new PDO('mysql:host=xx;dbname=xx', 'xx', 'xx');

    $sql = "SELECT GA.*, GU.*
    			FROM golf_amis AS GA 
    			LEFT JOIN golf_users AS GU ON GU.id = GA.id_ami 
    			WHERE GA.id_mon = :id";

    $req = $bdd->prepare($sql);
    $req->execute(array(
        ':id' => $id
    ));

    $response =  array();
    while ($row = $req->fetch(PDO::FETCH_ASSOC)){
    	$response[] = $row;
    }
    if(count($response) == 0){
        return false;
    } else{
        return $response;
    } 
}

