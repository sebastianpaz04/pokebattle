<?php
//nombre de la ruta para llamarla
namespace App\controllers; 
 //cuando la llame lo hare con 'use App\controllers\UserController'}
 use DateTime;
 use Exception;
 use App\services\Token;
use Psr\Http\Message\ResponseInterface as Response;
 use Psr\Http\Message\ServerRequestInterface as Request;
 use App\Models\User;
use App\services\Respuesta;
class UserController{ 

    public function login(Request $request, Response $response){
        //se trae el cuerpo de la solicitud http (datos de la peticion)
        $datos = $request->getParsedBody(); //getParsedBody trae los datos y los convierte en un array asociativo 
        //utilizo metodo logear de User
        $ok = User::logear($datos['usuario'],$datos['contraseña']);//loguear devuelve un arreglo
        //compruebo si el usuario pudo iniciar sesion
        if ($ok){
            $zona_horaria = 'America/Argentina/Buenos_Aires';   //ajusto la zona horaria en la que va a vecer el token creado 
            $zona= new \DateTimeZone($zona_horaria);
            $fecha = new DateTime();
            $fecha->setTimezone($zona);
            $fecha->modify('+1 hour');
            $fechaSQL = $fecha->format('Y-m-d H:i:s');
            $timestampExp = $fecha->getTimestamp();
            $token = Token::codificar($ok['id'],$timestampExp);
            User::guardarToken($datos['usuario'],$token,$fechaSQL);
            return Respuesta::respuesta($response,["token" => $token],200);
        }else{
           return Respuesta::respuesta($response,["error"=> "error al iniciar sesion, no se esncuentra usuario"],404);
        }
    }
    public function registro(Request $request, Response $response){
        $datos = $request->getParsedBody();
        $aux = User::registrar($datos['nombre'],$datos['usuario'],$datos['contraseña']);//registrar recibe un booleano
        if ($aux){
            return Respuesta::respuesta($response,["mensaje" =>"registro completado"],200);
        }else{
            return Respuesta::respuesta($response,["error" =>"el usuario ya existe"],404);
        }

    }
    /*
    - Si bien se valida al autorización del usuario mediante token, sin
embargo se obtiene mal
("Bearer
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7ImlkIjoiMjIifX0
._vIq81kyZdfZXZyfbpNuJ04KtxiiFdk0YllCj9j7ohg" => hay a sacar el
string "Bearer" antes de decodificar)
el token que viaja en el header, no pudiendo ser decodificado por JWT
- Se ajusta y se sigue con la prueba
- Mejorar los mensajes de respuestas, indicando que campo no cumple
las condiciones
- No se valida la nulabilida de los campos enviados en el body,
lanzando error inesperado
    */

    public function actualizar(Request $request, Response $response, array $args){
        try{
             $datos = $request->getParsedBody();
             if (empty($args))
                return Respuesta::respuesta($response,['error'=>'faltan parametros de la url'],400);
            $token = $request->getHeaderLine("Authorization");
            $id = Token::decodificar($token);
        //verifico que el id del token coincida con el de la url
            if ($args["usuario"] == $id){
               $aux = User::actualizar($args['usuario'],$datos['nombre'],$datos['contraseña']);
               if ($aux){
                return Respuesta::respuesta($response,["mensaje" =>"datos actualizados"],200);
                }
                else{
                    return Respuesta::respuesta($response,["mensaje" =>"no se pudo actualizar los datos"],404);
                };
        }
        else return Respuesta::respuesta($response,['error' => 'los ids no coinciden'],400);
        }catch(Exception $e){
            return Respuesta::respuesta($response,["error"=>"error de excepcion"],500);
        }
   
    }
    /*
        - Si bien se valida al autorización del usuario mediante token, sin
            embargo se obtiene mal
            ("Bearer
            eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7ImlkIjoiMjIifX0
            ._vIq81kyZdfZXZyfbpNuJ04KtxiiFdk0YllCj9j7ohg" => hay a sacar el
            string "Bearer" antes de decodificar)
            el token que viaja en el header, no pudiendo ser decodificado por JWT
            - Se ajusta y se sigue con la prueba
            - Se debe respetar el nombre del endpoint: GET /usuarios/{usuario}
            - Funionamiento incorrecto: no se valida que el token se corresponda
            con el id del usuario que se
            quiere consutar
 */

 public function traerDatos(Request $request, Response $response, array $args){
        //consulta si se reciben parametros de la url
        if (empty($args))
                return Respuesta::respuesta($response,['error'=>'faltan parametros de la url'],400);
        //recupero el id codificado en el token    
        $token = $request->getHeaderLine("Authorization");
       $id = Token::decodificar($token);
        if ((int)$args["usuario"] === (int)$id){
            $datos = User::traerDatos($args['usuario']);
        }
        else{ return Respuesta::respuesta($response,['error' => 'los ids de token y url no coinciden'],400);}

        //respuesta de la consulta si se realizo correctamente a consulta SQL
        if ($datos != null){
         return Respuesta::respuesta($response, $datos, 200);
        }else{
            return Respuesta::respuesta($response,["error" =>"no hay datos"],404);
        }    
    
    }     
}
?>