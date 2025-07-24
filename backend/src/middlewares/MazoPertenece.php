<?php
    namespace App\middlewares;
    use App\services\Respuesta;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    use App\services\Token;
    use Slim\Psr7\Response;
    use App\services\Pertenece;
    class MazoPertenece implements MiddlewareInterface{
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface{
            //guardo los datos del body
            $idMazo = $request->getParsedBody();
            $token = $request->getHeaderLine("Authorization");
            //decodifico token para recuperar id dentro de try catch
            $idUsuario = Token::decodificar($token);
            //respuesta si no pertenece el mazo al usuario
            if (!Pertenece::pertenece((int)$idMazo['idMazo'],$idUsuario)){
               return Respuesta::respuesta(new Response(),['error'=>'mazo no pertenece al usuario'],404); 
            }
            return $handler->handle($request);
        }
    }

?>