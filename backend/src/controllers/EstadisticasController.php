<?php
    namespace App\controllers;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use App\models\Estadisticas;
    use App\services\Respuesta;
    class EstadisticasController{
        public function estadisticasC(Request $request, Response $response,array $args){ 
             
                $datos = Estadisticas::estadisticas();
                if($datos == null){
                    $response->getBody()->write(json_encode(['error'=>'No se registraron partidas']));
                    return $reponse= $response->withHeader('Content-Type','application/json')->withStatus(404);
                }
                $response->getBody()->write(json_encode($datos));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            ;
        }
    }
?>