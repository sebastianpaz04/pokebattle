<?php
namespace App\middlewares;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use App\services\Token;
use App\services\Respuesta;
use App\models\Mazo;
class ValidacionMazos implements MiddlewareInterface{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface{
        $token = $request->getHeaderLine("Authorization");
        $id = Token::decodificar($token);
        if (!Mazo::cantMazos($id)){
            return Respuesta::respuesta(new Response(),['error'=> 'el usuario tiene ya 3 mazos'],400);
        }
    return $handler->handle($request);
    }
}
