<?php
use Slim\App;
use App\middlewares\ValidacionToken;
use App\controllers\JuegoController;
use App\middlewares\CamposVacios;
use App\middlewares\MazoPertenece;
return function(App $app){
$app->post('/partidas', JuegoController::class .':pertenece')
->add(MazoPertenece::class)
->add(CamposVacios::class)
->add(ValidacionToken::class);

$app->post('/jugadas',JuegoController::class . ':jugada')
->add(CamposVacios::class)
->add(ValidacionToken::class);

$app->get('/usuarios/{usuario}/partidas/{partida}',JuegoController::class .':cartasEnMano')->add(ValidacionToken::class);
}
?>