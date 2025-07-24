<?php
namespace App\controllers;
use App\services\Jugadas;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\services\Token;
use App\models\Juego;
use App\models\Mazo;
use App\models\MazoCarta;
use App\services\Respuesta;
use App\services\JugadaServidor;
class juegoController{
    /*
  
   
    se repite el mismo código muchas veces
    - Se ajusta y se sigue con la prueba
    - Error de tipo: Uncaught TypeError:
    App\services\Pertenece::pertenece(): Argument #1 ($idMazo) must be
    of type int,
    null given, called in
    C:\xampp\htdocs\Grupo--23\Pokemon\src\middlewares\MazoPertenece
    .php on line 32 and defined in
    se ajusta el código se sigue con la prueba
    - Se vuelve a repetir el error de la obtención del token
    - se ajusta el código y se continúa con la prueba
    - Se debe validar que se cree una sola partida al mismo tiempo para el
    mismo mazo y ese usuario
    */
    public function pertenece(Request $request, Response $response){
        try{
            $datos = $request->getParsedBody();
            $token = $request->getHeaderLine('Authorization');
            $idUsuario = Token::decodificar($token);

            if (!Juego::mazoDisponible($idUsuario, (int)$datos['idMazo'])){

                return Respuesta::respuesta($response,['error'=> 'el mazo no se encuentra disponible'],401);
            }
            $zona_horaria = 'America/Argentina/Buenos_Aires'; 
            $zona= new \DateTimeZone($zona_horaria);
            $fecha = new \DateTime();
            $fecha->setTimezone($zona);
            $dPartida=Juego::guardarPartida($idUsuario,$fecha->format('Y-m-d H:i:s'),$datos['idMazo'],'en_curso');
            if($dPartida){
                Mazo::actualizarEstadoMazo("en_mano",$datos['idMazo']);
                Mazo::actualizarEstadoMazo("en_mano",1);
            }   
            $cartas = MazoCarta::getDatos($datos['idMazo']);
            return Respuesta::respuesta($response,['id_partida'=> $dPartida,'carta'=>$cartas],200);
            }
        catch(Exception $e){
            return Respuesta::respuesta($response,["error en peticion"=>$e->getMessage()],404);
        }    
    }
    public function jugada(Request $request, Response $response):Response{
        try {
            //Recibe la carta jugada por el usuario y el id de la partida
            $datos = $request->getParsedBody();
            $idPartida = (int)$datos['idPartida'] ;
            if (Juego::getEstadoPartida($idPartida)){
                return Respuesta::respuesta($response,['error'=>'la partida ya finalizo'],404);
            }
            $idCarta =(int)$datos['idCarta'] ;
            $token = $request->getHeaderLine("Authorization");
            $idUsuario = Token::decodificar($token);
            $datos = Juego::validarPartida($idUsuario, $idPartida);
            if (!$datos)
                return Respuesta::respuesta($response,['error'=>'la partida no existe'],404);
            if($datos["usuario_id"]!= $idUsuario){
                return Respuesta::respuesta($response,['error'=>'la partida no pertenece al usuario'],401);
            }
            $idMazoUsuario = MazoCarta::idMazo($idPartida);
            //Verifica que la carta enviada sea valida para jugar
            if(!Juego::cartaValida($idMazoUsuario,$idCarta)){
                return Respuesta::respuesta($response,['error'=>'la carta elegida no es valida'],404);
            }
            //Crea un registro en la tabla "jugada".
            $idServ = JugadaServidor::jugadaServidor();
            $idJugada = Juego::crearJugada($idCarta,$idPartida,$idServ);
            if (!$idJugada){
                return Respuesta::respuesta($response,['error'=>'no se creo la jugada'],404);
            }
            //analiza cual es la carta ganadora
            
            //actualiza el estado de la carta en la tabla "mazo_carta" a estado "descartado"
           
            $estado = Juego::juego($idMazoUsuario,$idServ,$idCarta);
            $nombreCarta = MazoCarta::getDatosCarta($idServ);
            //guarda en el registro "jugada" recientemente creado el estado final de la misma "gano","perdio" o "empato"
            if (!(Juego::actualizarEstadoJugada($estado['el_usuario'],$idJugada))) {
                return Respuesta::respuesta($response,['error'=>'no se actualizo el estado de la jugada'],404);
            }
            if(Juego::esQuintaJugada($idPartida)){
                //lo comento porque no me lo pide el ejercicio pero hay q hacerlo xd
                $estadoPartida=Juego::ganadorPartida($idPartida);
                if(!$estadoPartida){
                    return Respuesta::respuesta($response,['error'=>'no se actualizo el estado de la partida'],404);
                }

                if(!Juego::actualizarEstadoPartida('finalizada',$idPartida)){
                    return Respuesta::respuesta($response,['error'=>'no se actualizo el estado de la partida'],404);
                }
                Mazo::actualizarEstadoMazo('en_mazo',$idMazoUsuario);   
                Mazo::actualizarEstadoMazo('en_mazo',1);  
                return Respuesta::respuesta($response,["carta de servidor"=> $nombreCarta,"poder carta servidor"=>$estado['ataqueServer'],"poder carta usuario"=>$estado['ataqueUser'],'el usuario'=>$estadoPartida .' la partida' ],200);             
            }

            //Si, es la quinta jugada debe cerrar la partida con el estado correspondiente ("finalizada")
            
            return Respuesta::respuesta($response,['carta de servidor'=>$nombreCarta,"poder carta servidor"=>$estado['ataqueServer'],"poder carta usuario"=>$estado['ataqueUser']],200);
        }
        catch(Exception $e){
            return Respuesta::respuesta($response,["error en controlador"=>$e->getMessage()],404);
        }
    }

    /*
        - Se debe respetar el nombre del endpoint: GET
        /usuarios/{usuario}/partidas/{partida}/cartas
        - Se vuelve a reiterar el error del token
        - Se ajusta y se continua
        - No se valida que el token se corresponda con el usuario enviado en
        los argumentos
        - No se valida la nulabilidad de los argumentos, lanzado un error
        inesperado y un status
        incorrecto

    */
    public function cartasEnMano(Request $request, Response $response, array $args): Response{

        $usuarioIdParam = (int)$args['usuario'] ?? 0;
        $partidaId = (int) $args['partida'] ?? 0;
        if (empty($usuarioIdParam))
            return Respuesta::respuesta($response,['error'=>'el parametro id de la url esta vacio'],400);

        if (empty($partidaId))
            return Respuesta::respuesta($response,['error'=>'el parametro id partida de la url esta vacio'],400);
        
        if (Token::decodificar($request->getHeaderLine("Authorization")) != $usuarioIdParam)
            return Respuesta::respuesta($response,['error'=>'el usuario de la url no coincide con el token'],400);
        if ($usuarioIdParam <= 0 || $partidaId <= 0) {
            $response->getBody()->write(json_encode(['error' => 'Parámetros inválidos']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400); // Bad Request
        }
    
    
        $cartas = Juego::obtenerCartasEnMano($usuarioIdParam, $partidaId);
        if (empty($cartas))
            return Respuesta::respuesta($response,['error'=>"no  se trajeron datos de cartas de la partida"],404);

        $response->getBody()->write(json_encode($cartas));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200); // OK
        }
 
}
?>