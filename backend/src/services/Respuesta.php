<?php
namespace App\services;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;
class Respuesta {
    public static function respuesta(Response $response, array $msj, int $status): ResponseInterface {
        $response->getBody()->write(json_encode($msj));
        return $response->withHeader("Content-Type", "application/json")->withStatus($status);
    }
}
?>