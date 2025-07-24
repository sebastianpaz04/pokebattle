<?php
    namespace App\models;
    use App\models\BD;
    use DateTime;
    use Exception;
    //cuando se quiera acceder'use App\models\User'
    class User{
      public static function getID($token){
        
      }
      public static function guardarToken($usuario,$token,$fechaSQL){
        //Realizo la consulta sql
        $cnx = BD::conectar();
        $sql = 'UPDATE usuario SET token = :token, vencimiento_token = :fechaSQL where usuario = :usuario ';
        $result = $cnx->prepare($sql);
        $result->bindParam(':token',$token);
        $result->bindParam(':fechaSQL',$fechaSQL);
        $result->bindParam(':usuario',$usuario);
        $result->execute();
      }
      
      public static function logear($usuario,$contraseña){
        //me conecto a la base de datos
        $cnx = BD::conectar();
        //consulta sql para saber si existe el usuario que trae $usuario
        $sql = "SELECT * FROM  usuario WHERE usuario= :user AND password = :pass";
        $aux=$cnx->prepare($sql); //prepare trae un objeto PDOStatement
        $aux->bindParam(':user',$usuario); // relaciono el parametro :user con la variable
        $aux->bindParam(':pass', $contraseña);
        $aux->execute(); // ejecuto la consulta


        return  $aux->fetch(\PDO::FETCH_ASSOC); //fetch(PDO::FETCH_ASSOC) metodo que devuelve la fila obtenida en un arreglo asociativo o vacio si no hay datos
      }
      public static function registrar($nombre,$usuario,$password){
        try{
          //compruebo si el usuario ya existe
        $cnx = BD::conectar();
        $chequeoSql = 'SELECT COUNT(*) FROM usuario WHERE usuario = :user' ;
        $stmt = $cnx->prepare($chequeoSql);
        $stmt ->bindParam(':user',$usuario);
        $stmt -> execute();
        if ($stmt ->fetchColumn() > 0){
          return false;
        }
        //insertar nuevos datos a la tabla        
          $sql = 'INSERT INTO usuario(nombre,usuario,password) VALUES(:nombre,:usuario,:password)';
          $aux=$cnx->prepare($sql);
          $aux->bindParam(':nombre',$nombre);
          $aux->bindParam(':usuario',$usuario);
          $aux->bindParam(':password',$password);
          $aux->execute();
          return true;
      }catch(Exception $e){
        echo'Error en el controlador del registro'.$e->getMessage();
        return false;
      }
      }

public static function actualizar($id, $nombre, $password){
    $cnx = BD::conectar();
    $sql = 'UPDATE usuario SET nombre = :nombre, password = :password WHERE id = :id';
    $aux = $cnx->prepare($sql);
    $aux->bindParam(':nombre', $nombre);
    $aux->bindParam(':password', $password);
    $aux->bindParam(':id', $id);
    $result = $aux->execute();
    return $result; 
}
   public static function traerDatos($id){
    $cnx = BD::conectar();
    $sql = 'SELECT nombre,usuario,password FROM usuario WHERE id = :id';
    $aux=$cnx->prepare($sql);
    $aux->bindParam(':id',$id);
    $aux->execute();
    return $aux->fetch(\PDO::FETCH_ASSOC);
   }
   public static function validarToken($token,$id){
    $cnx = BD::conectar();
    $sql = 'SELECT vencimiento_token FROM usuario WHERE token = :token AND id = :id';
    $aux=$cnx->prepare($sql);
    $aux->bindParam(':token',$token);
    $aux->bindParam(':id',$id);
    $aux->execute();
    $fechaVencimiento = $aux->fetch(\PDO::FETCH_ASSOC);
    if (!$fechaVencimiento){
      return false;
    }
    $zona = new \DateTimeZone('America/Argentina/Buenos_Aires'); // o la que necesites
    $ahora = new DateTime('now', $zona);
    if ($ahora->format('Y-m-d H:i:s') >= $fechaVencimiento['vencimiento_token']) {
      return false;
    }
    return true;
   }
   public static function borrarUsuario (int $id) {
    $cnx = BD::conectar();
    $sql = 'DELETE * FROM usuario where id =:id';
    $aux = $cnx->prepare($sql);
    $aux->bindParam(':id', $id);
    return $aux->execute();
   }

   public static function esAdmin(int $id){
    $cnx = BD::conectar();
    $sql = 'SELECT es_admin FROM usuario WHERE id =:id';
    $aux = $cnx->prepare($sql);
    $aux->bindParam(':id', $id);
    if ($aux->execute()){
      $datos = $aux->fetch(\PDO::FETCH_ASSOC);
      if ($datos['es_admin']=== 1)
        return true;
      else
        return false;
    }
    return false;
   }
   public static function mazosActivos(int $id){
    $cnx = BD::conectar();
    $sql = "SELECT * FROM mazos WHERE usuario_id = :id";
    $aux = $cnx->prepare($sql);
    $aux->bindParam(':id', $id);
    $aux->execute();
    return $aux->fetch(\PDO::FETCH_ASSOC);
   }
   }
?>