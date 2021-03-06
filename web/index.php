<?php

require_once '../vendor/autoload.php';
require_once 'function.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');    
    header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS"); 
    header("Accept: application/json Content-Type: application/json");
}   
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");         
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
} 


// init Silex app
$app = new Silex\Application();
$app['debug']=true;

$pseudo = $_GET['pseudo'];
$password = $_GET['password'];
$HCP = $_GET['HCP'];
$image = $_FILES['image'];



//configure database connection
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'host' => 'justinecbase.mysql.db',
        'dbname' => 'justinecbase',
        'user' => 'justinecbase',
        'password' => 'JCbs1995',
        'charset' => 'utf8',
    ),
));



// route for "/countries" URI: load countries list and return it in JSON format
$app->get('/users', function () use ($app) {
    $sql = "SELECT * FROM golf_users";
    $users = $app['db']->fetchAll($sql);

    return $app->json($users);
});

// route for "/countries/{id}" URI: load specific country info and return it in JSON format
$app->get('/users/{id}', function ($id) use ($app) {

    $result = user($id);
    return $app->json($result);
})->assert('id', '\d+');

$app->post('/register', function (Request $request) use ($app) {

	 $post = array(
        'pseudo' => $request->request->get('pseudo'),
        'password'  => $request->request->get('password'),
        'HCP'  => $request->request->get('HCP'),
    );
    $result = userRegistration($post['pseudo'], $post['password'], $post['HCP']);

   
    return $app->json($result);
});

$app->get('/login', function ($pseudo, $password) use ($app) {

    $result = userConnection($pseudo, $password);

    return $app->json($result);
})->value('pseudo', $pseudo )->value('password', $password);

$app->put('/users/edit', function (Request $request) use ($app) {

    $post = array(
        'id' => $request->request->get('id'),
        'pseudo' => $request->request->get('pseudo'),
        'password'  => $request->request->get('password'),
        'HCP'  => $request->request->get('HCP'),
        'mail'  => $request->request->get('mail'),
    );
    $result = updateUser($post['id'], $post['pseudo'], $post['password'], $post['HCP'], $post['mail']);

    return $app->json($result);
});

$app->put('/users/edit_image', function ($id, $image) use ($app) {

    $result = updateProfilPicture($image, $id);

    return $app->json($result);
})->assert('id', '\d+')->value('image', $image);

$app->post('/enregistrer', function (Request $request) use ($app) {

     $post = array(
        'joueur0' => $request->request->get('joueur0'),
        'joueur1' => $request->request->get('joueur1'),
        'joueur2' => $request->request->get('joueur2'),
        'joueur3' => $request->request->get('joueur3'),
        'score0'  => $request->request->get('score0'),
        'score1'  => $request->request->get('score1'),
        'score2'  => $request->request->get('score2'),
        'score3'  => $request->request->get('score3'),
        'nom'  => $request->request->get('nom'),
        'adresse'  => $request->request->get('adresse'),
        'nb_trous'  => $request->request->get('nb_trous'),
        'joueurG'  => $request->request->get('joueur_gagnant'),
        'mode'  => $request->request->get('mode'),
        'pseudo_winner'  => $request->request->get('pseudo_winner'),

    );
    $result = enregistrer($post['joueur0'], $post['joueur1'], $post['joueur2'], $post['joueur3'], $post['score0'], $post['score1'], $post['score2'], $post['score3'], $post['nom'], $post['adresse'], $post['nb_trous'], $post['joueurG'], $post['mode'], $post['pseudo_winner']);

   
    return $app->json($result);
});

$app->get('/allParties/{id}', function ($id) use ($app) {

    $result = allParties($id);

    return $app->json($result);
})->assert('id', '\d+');

$app->get('/partie/{id}', function ($id) use ($app) {

    $result = infosPartie($id);

    return $app->json($result);
})->assert('id', '\d+');

$app->post('/addFriend', function (Request $request) use ($app) {

     $post = array(
        'id' => $request->request->get('id'),
        'pseudoFriend'  => $request->request->get('pseudoFriend'),
    );
    $result = addFriend($post['id'], $post['pseudoFriend']);

    return $app->json($result);
});
$app->delete('/deleteFriend', function (Request $request) use ($app) {

     $post = array(
        'id' => $request->request->get('id'),
        'pseudoFriend'  => $request->request->get('pseudoFriend'),
    );
    $result = deleteFriend($post['id'], $post['pseudoFriend']);

   
    return $app->json($result);
});

$app->get('/friend/{pseudo}', function ($pseudo) use ($app) {

    $result = infosFriend($pseudo);

    return $app->json($result);
})->assert('id', '\d+');

$app->get('/allfriends/{id}', function ($id) use ($app) {

    $result = allFriend($id);

    return $app->json($result);
})->assert('id', '\d+');


$app->post('/test', function (Request $request) use ($app) {

     $post = array(
        'all' => $request->request->get('all'),
        'id' => $request->request->get('id'),
    );
     
    $result = test($post['all'], $post['id']);

    return $app->json($result);
});

$app->get('/getTest/{id}', function ($id) use ($app) {

    $result = getTest($id);

    return $app->json($result);
})->assert('id', '\d+');




$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

//default route
$app->get('/', function () {
    return "Liste des méthodes:
  - /users - retourne tous les users;\n
  - /users/{id} - retourne les infos d'un utilisateur en fonction de son id;";
});

$app->run();
