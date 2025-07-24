<?php
namespace App\controllers;
 use Psr\Http\Message\ResponseInterface as Response;
 use Psr\Http\Message\ServerRequestInterface as Request;
 use App\models\Mazo;
 use App\services\Respuesta;
 use App\services\Token;

class MazoController{
    public function recibirCartas(Request $request,Response $response){
        //recibo 5 cartas y el nombre
        $datos = $request->getParsedBody();
        //decodifico el token y recibo el id de usuario
        $token = $request->getHeaderLine('Authorization');
        $idUsuario = Token::decodificar($token);
        //respuesta sino se pudo crear el mazo
        $idMazo = Mazo::crearMazo($idUsuario,$datos['nombre']);// crearMazo devuleve el id de mazo si hay exito, sino devuelve false
        if (!$idMazo){   
            return Respuesta::respuesta($response,['error'=>'no se pudo crear mazo'],404);
        }

        foreach($datos as $key => $value){
            if ($key !== 'nombre' && !empty($value)){
                //verifico si recibo los ids de las cartas en string, en el caso de que si valido que se pueda hacer la conversion
                if (preg_match('/^\d+$/', (string)$value)) {
                    $ok=Mazo::mazo_carta((int)$value,$idMazo,'en_mazo');//(int) pasa la variable a entero si es posible, mazo_carta devuelve boolean
                    if (!$ok)
                    return Respuesta::respuesta( $response,['error'=>'no se pudo guardar carta en el mazo'],404);
                }else{
                    return Respuesta::respuesta($response,["error"=>"id de carta invalida $value"],400);
                }
            }
        }
        return Respuesta::respuesta( $response,['nombre mazo'=> $datos['nombre'],'id mazo'=>$idMazo],200);
    }

    public function cartas(Request $request, Response $response, array $args) {
        $queryParams = $request->getQueryParams();
        $atributo = $queryParams['atributo'] ?? null;
        $nombre = $queryParams['nombre'] ?? null;
        
        $cartas =Mazo::buscarCartas($atributo, $nombre);
    
        if (!empty($cartas)) {
            return Respuesta::respuesta($response,$cartas, 200, );
        } else {
            return Respuesta::respuesta($response,["error" => "No se encontraron cartas"], 404 );
        }
    }

    public function actualizarMazo(Request $request, Response $response, array $args): Response {
        $token = $request->getHeaderLine('Authorization');
        $TokenuserId = Token::decodificar($token);
        $mazoId = (int)$args['mazo'];
        $data = $request->getParsedBody();
        $nuevoNombre = $data['nombre'];      
        if (empty($mazoId))
            return Respuesta::respuesta($response,['error' => "faltan argumentos de la url"],400);
        $ok = Mazo::cambiarNombreMazo($mazoId, $nuevoNombre, $TokenuserId);
    
        if (!$ok) {
           return Respuesta::respuesta($response,["error" => "Mazo no encontrado o no pertenece al usuario."],404);
        }
        return Respuesta::respuesta($response, ["mensaje" => "Mazo actualizado correctamente."], 200);
    }
    public function obtenerMazo(Request $request, Response $response, array $args): Response {
        $token = $request->getHeaderLine('Authorization');
        $TokenuserId = Token::decodificar($token);
        $paramUserId = (int)$args['usuario'];
        if (empty($paramUserId))
            return Respuesta::respuesta($response,['error'=>'faltan parametros de la url'],400);
        if ($TokenuserId != $paramUserId){
            return Respuesta::respuesta($response,["error"=>"id no valido para ver mazos"],401);
        }
        $mazos = Mazo::obtenerMazoId($paramUserId);
        if (!$mazos)
            return Respuesta::respuesta($response,["usuario"=>"el usuario no tiene mazos"],404);
        return Respuesta::respuesta($response,["mazos"=>$mazos],200);
    }
    public function deleteMazo(Request $request, Response $response, array $args): Response {
        $token = $request->getHeaderLine('Authorization');
        $userId = Token::decodificar($token); 
        $mazoId = (int)$args['mazo']??null;
        
        try {
            $deleted = Mazo::borrarMazo($mazoId, $userId);
    
            if (! $deleted) {
                $response->getBody()->write(json_encode(["error" => "Mazo no encontrado o no pertenece al usuario."]));
                return $response
                    ->withStatus(404)
                    ->withHeader("Content-Type", "application/json");
            }
    
            $response->getBody()->write(json_encode(["msj" => "Mazo eliminado correctamente."]));
            return $response->withStatus(200)->withHeader("Content-Type", "application/json");
    
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([ "error" => "Error al eliminar el mazo: " . $e->getMessage()  ]));
            return $response->withStatus(409)->withHeader("Content-Type", "application/json");

                
        }
    }


}

?>