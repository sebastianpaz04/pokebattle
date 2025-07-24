<?php
    use App\controllers\MazoController;
    use App\middlewares\CamposVacios;
    use App\middlewares\ValidarIDsCartas;
    use App\middlewares\ValidacionToken;
    use App\middlewares\ValidacionMazos;
    use Slim\App;
    return function (App $app) {
    $app->post('/mazos',MazoController::class .':recibirCartas')
    ->add(ValidacionMazos::class)
    ->add(ValidarIDsCartas::class)
    ->add(CamposVacios::class)
    ->add(ValidacionToken::class);
    $app->delete('/mazos/{mazo}',MazoController::class .':deleteMazo')->add(ValidacionToken::class);
    $app->get('/usuarios/{usuario}/mazos',MazoController::class .':obtenerMazo')->add(ValidacionToken::class);
    $app->put('/mazos/{mazo}',MazoController::class .':actualizarMazo')->add(CamposVacios::class)->add(ValidacionToken::class);
    $app->get('/cartas',MazoController::class .':cartas');    
    }
?>