<?php
namespace App\middlewares;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use App\models\Mazo;
use App\services\Respuesta;
class ValidarIDsCartas implements MiddlewareInterface{
 public function elementosIguales(array $datos): bool {
    // Ya son enteros si viene de $ids, no hace falta convertirlos de nuevo
    return count($datos) !== count(array_unique($datos));
}

public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    $datos = $request->getParsedBody();
    $ids = [];

    foreach ($datos as $key => $value) {
        if ($key !== 'nombre' && $value !== null && $value !== '') {
            $value = trim((string)$value); // Normalizamos el string
            if (!preg_match('/^\d+$/', $value)) {
                return Respuesta::respuesta(new Response(), ["error" => "id de carta inválida $value"], 400);
            } else {
                $ids[] = (int)$value; // Todos quedan como enteros aquí
            }
        }
    }

    // Depuración opcional
    // error_log("IDs recibidos: " . implode(', ', $ids));

    foreach ($ids as $value) {
        if (!Mazo::cartaValida($value)) {
            return Respuesta::respuesta(new Response(), ["error" => "el id $value no existe"], 400);
        }
    }

    if ($this->elementosIguales($ids)) {
        return Respuesta::respuesta(new Response(), ["error" => "hay cartas iguales en el mazo"], 400);
    }

    return $handler->handle($request);
}

}

?>