<?php
use Slim\App;
use App\controllers\EstadisticasController;
return function(App $app){
    $app->get('/estadisticas',EstadisticasController::class .':estadisticasC');
}
?>