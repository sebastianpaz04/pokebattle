<?php
	namespace App\middlewares;
	use Psr\Http\Message\ServerRequestInterface;
	use Psr\Http\Message\ResponseInterface;
	use Psr\Http\Server\MiddlewareInterface;
	use Psr\Http\Server\RequestHandlerInterface;
	use Slim\Psr7\Response;
	use App\services\Respuesta;

	class VerificacionUsuario implements MiddlewareInterface{

		public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface{
			$datos = $request->getParsedBody();
	 	 	$cantidadCharUsuario = strlen($datos['usuario']);
        	if ($cantidadCharUsuario < 6)
            	return Respuesta::respuesta(new Response(),['error'=>'el usuario tiene menos de 6 caracteres'],400);
        	else{
            	if ($cantidadCharUsuario > 20)
                	return Respuesta::respuesta(new Response(),['error'=>'el usuario se pasa de 20 caracteres'],400);
        	}
        	return $handler->handle($request);
   		 }
	}
?>