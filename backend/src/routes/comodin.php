<?php 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    // Esta ruta atrapará cualquier otra que no fue definida antes
    $app->map(['GET', 'POST', 'PUT', 'DELETE'], '/{ruta:.*}', function (Request $request, Response $response, array $args) {
        $ruta = $args['ruta'];
        $response->getBody()->write(json_encode(["error"=>"Ruta no encontrada: " . $ruta]));
        return $reponse= $response->withHeader('Content-Type','application/json')->withStatus(404);;
    });
};

?>