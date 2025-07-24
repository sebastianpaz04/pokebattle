<?php
namespace App\middlewares;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use App\services\Respuesta;

class Alfanumerico implements MiddlewareInterface{
 
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface{
        $datos = $request->getParsedBody();
        //Strlen nos ayuda a contar la cantidad de caracteres que tiene el string
        $cantidadCharPass = strlen($datos['contraseña']);
        if($cantidadCharPass < 8 ){
            return Respuesta::respuesta(new Response(),['error:'=>'hay menos de 8 caracteres en la contraseña'],400);
        }
        if (!preg_match('/[A-Z]/',$datos['contraseña']) || 
            !preg_match('/[0-9]/',$datos['contraseña']) || 
            !preg_match('/\W/',$datos['contraseña'])){
                
            return Respuesta::respuesta(new Response,['error'=> 'falta mayuscula,numero o caracter especial en contraseña'],400); 
        }
        
        return $handler->handle($request);
    }
}
?>