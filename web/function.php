<?php

function user($id){
    $bdd = new PDO('mysql:host=justinecbase.mysql.db;dbname=justinecbase', 'justinecbase', 'JCbs1995');
    $sql = "SELECT * FROM golf_users WHERE id = :id";

    $req = $bdd->prepare($sql);
    $req->execute(array(
        ':id' => $id
    ));

    $result = $req->fetch(PDO::FETCH_ASSOC);

    return $result;
}


function userConnection($pseudo, $password){
    $bdd = new PDO('mysql:host=justinecbase.mysql.db;dbname=justinecbase', 'justinecbase', 'JCbs1995');

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
    $bdd = new PDO('mysql:host=justinecbase.mysql.db;dbname=justinecbase', 'justinecbase', 'JCbs1995');

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

    $bdd = new PDO('mysql:host=justinecbase.mysql.db;dbname=justinecbase', 'justinecbase', 'JCbs1995');

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

    $bdd = new PDO('mysql:host=justinecbase.mysql.db;dbname=justinecbase', 'justinecbase', 'JCbs1995');

    $user = user($id);

    $pseudo == NULL ? $pseudo = $user['pseudo'] : $pseudo = $pseudo ;
    $password == NULL ? $password = $user['password'] : $password = $password ;
    $HCP == NULL ? $HCP = $user['HCP'] : $HCP = $HCP ;

        $sql = "UPDATE golf_users SET pseudo = :pseudo, password= :pass, HCP = :HCP WHERE id = :id ";

        $req = $bdd->prepare($sql);
        $req->execute(array(
            ':pass' => $password,
            ':pseudo' => $pseudo,
            ':HCP' => $HCP,
            ':id' => $id
        ));
        return true;

}

function deleteUser ($id){

    $bdd = new PDO('mysql:host=justinecbase.mysql.db;dbname=justinecbase', 'justinecbase', 'JCbs1995');

    $sql = "DELETE FROM golf_users WHERE id = :id";

    $req = $bdd->prepare($sql);
    $req->execute(array(
        ':id' => $id
    ));
    return true;


}

function updateProfilPicture($imgInfos, $user_id){

    $bdd = new PDO('mysql:host=justinecbase.mysql.db;dbname=justinecbase', 'justinecbase', 'JCbs1995');

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

function enregistrer($joueur_1, $joueur_2, $joueur_3, $joueur_4, $score_1, $score_2, $score_3, $score_4, $nom, $adresse, $nb_trous){

    $bdd = new PDO('mysql:host=justinecbase.mysql.db;dbname=justinecbase', 'justinecbase', 'JCbs1995');

   $date = date("Y-m-d H:i:s");

    if(empty($joueur_1) || empty($score_1) || empty($nom) || empty($adresse)){
        return false;
    } else{
        $sql = "INSERT INTO golf_enregistrer (id_joueur_1, id_joueur_2, id_joueur_3, id_joueur_4, score_j1, score_j2 , score_j3 , score_j4, date, nom , adresse , nb_trous) VALUES ('".$joueur_1."','".$joueur_2."','".$joueur_3."','".$joueur_4."','".$score_1."','".$score_2."','".$score_3."','".$score_4."', '".$date."', '".$nom."','".$adresse."','".$nb_trous."')";

        $req = $bdd->prepare($sql);
        $req->execute();

        return true;
    }

}
