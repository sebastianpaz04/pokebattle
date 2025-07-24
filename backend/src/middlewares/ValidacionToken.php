<?php
namespace App\middlewares;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use App\models\User;
use App\services\Token;
use App\services\Respuesta;
class ValidacionToken implements MiddlewareInterface{
  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    $token = $request->getHeaderLine("Authorization");

    if (empty($token)) {
        return Respuesta::respuesta(new Response(), ["error" => "no se envio token"], 400);
    } else {
        try {
            if (str_starts_with($token, "Bearer")) {
                $token = substr($token, 7); 
            }

            $id = Token::decodificar($token);

            if (!User::validarToken($token, $id)) {
                return Respuesta::respuesta(new Response(), ["error" => "Inicio de sesion expirado o no se encuentra token"], 401);
            }

            return $handler->handle($request);

        } catch (\Throwable $e) {
            return Respuesta::respuesta(new Response(), ["error en validacion de token; detalle" => $e->getMessage()], 400);
        }
    }
}

}
?>