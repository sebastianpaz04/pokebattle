<?php

use Slim\App;
use App\Controllers\UserController;
use App\middlewares\CamposVacios;
use App\middlewares\Alfanumerico;
use App\middlewares\ValidacionToken;
use App\middlewares\VerificacionUsuario;
return function(App $app) { 
    $app->post('/login', UserController::class . ':login')
    ->add(CamposVacios::class);
    $app->post('/registro', UserController::class . ':registro')
    ->add(Alfanumerico::class)
    ->add(VerificacionUsuario::class)
    ->add(CamposVacios::class);
    $app->put('/usuarios/{usuario}',UserController::class. ':actualizar')
    ->add(Alfanumerico::class)
    ->add(CamposVacios::class)
    ->add(ValidacionToken::class);
    $app->get('/usuarios/{usuario}',UserController::class. ':traerDatos')->add(ValidacionToken::class);
  
};