<?php
namespace App\middlewares;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use App\services\Respuesta;
 
 class CamposVacios implements MiddlewareInterface{
    public  function process( ServerRequestInterface $request,RequestHandlerInterface $handler): ResponseInterface{
        $datos = $request->getParsedBody();
     if (empty($datos)) {
        return Respuesta::respuesta(new Response(), ["error" => "Datos inválidos o cuerpo vacío"], 400);
    }

    // Campos requeridos
    foreach ($datos as $key => $value) {
            $camposRequeridos[] = $key;

    }

    foreach ($camposRequeridos as $campo) {
        if (!isset($datos[$campo]) || empty($datos[$campo])) {
            return Respuesta::respuesta(new Response(), ["error" => "El campo '$campo' está vacío o es nulo"], 400);
        }
    }

    // Si todo está bien, pasa al siguiente middleware/handler
    return $handler->handle($request);    
  }
}
?> 