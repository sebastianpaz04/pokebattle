<?php 
namespace App\models;
use App\models\BD;
use Exception;
class Juego{
    public static function guardarPartida(int $id_usuario,string $fecha,int $id_mazo,string $estado){
        $sql = "INSERT INTO partida (usuario_id,fecha,mazo_id,estado) VALUES (:id_usuario,:fecha,:id_mazo,:estado)";
        $cnx = BD::conectar();
        $consulta = $cnx->prepare($sql);
        $consulta->bindParam(":id_usuario", $id_usuario);
        $consulta->bindParam(":fecha", $fecha);
        $consulta->bindParam(":id_mazo", $id_mazo);
        $consulta->bindParam(":estado", $estado);
        if ($consulta->execute()){
            return $cnx->lastInsertId();
        }else{ 
            throw new Exception('error en guardar partida');
        }
    }

    public static function cartaValida(int $idMazo,int $idCarta){
        $sql = "SELECT estado FROM  mazo_carta WHERE mazo_id = :idMazo AND carta_id = :idCarta";
        $cnx = BD::conectar();
        $consulta = $cnx->prepare($sql);
        $consulta->bindParam(':idMazo',$idMazo);
        $consulta->bindParam(':idCarta',$idCarta);
        $consulta->execute();
        $aux = $consulta->fetch(\PDO::FETCH_ASSOC);
        if($aux){
            if ($aux['estado'] == 'descartado' || $aux['estado']=='en_mazo')
                return false;
            return true;
        }
        return false;
        
    }
    public static function crearJugada(int $idCarta,int $idPartida,int $idCartaServ){
        $cnx = BD::conectar();
        $sql = "INSERT INTO jugada(carta_id_a,carta_id_b,partida_id) VALUES (:idCarta,:idCartaServ,:idPartida)";
        $aux = $cnx->prepare($sql);
        $aux->bindParam(':idCarta',$idCarta);
        $aux->bindParam(':idCartaServ',$idCartaServ);
        $aux->bindParam(':idPartida',$idPartida);
        if($aux->execute()){
            return  $cnx->lastInsertId();//200
        }
        return false;
    }
    public static function actualizarEstadoCarta(string $estado, int $idCarta, int $idMazo): bool {
        $sql = "UPDATE mazo_carta SET estado = :estado WHERE carta_id = :idCarta AND mazo_id = :mazo";
        $cnx = BD::conectar();
        $consulta = $cnx->prepare($sql);
        $consulta->bindParam(':estado', $estado);
        $consulta->bindParam(':idCarta', $idCarta);
        $consulta->bindParam(':mazo', $idMazo);
        return $consulta->execute();
    }
    
    public static function juego(int $idMazo,int $idServ,int $idUser){
        $ataqueUsuario = Juego::getPoderCarta($idUser);
        $ataqueServidor = Juego::getPoderCarta($idServ);
        if (Juego::gana($idUser, $idServ) === 'fuerte') {
            $ataqueUsuario *= 1.3;
        } elseif (Juego::gana($idServ, $idUser) === 'fuerte') {
            $ataqueServidor *= 1.3;
        }

        if (abs($ataqueUsuario - $ataqueServidor) < 0.01) {
            $estado = ['el_usuario' => 'empato', 'ataqueUser' => $ataqueUsuario, 'ataqueServer' => $ataqueServidor];
        } elseif ($ataqueUsuario > $ataqueServidor) {
            $estado = ['el_usuario' => 'gano', 'ataqueUser' => $ataqueUsuario, 'ataqueServer' => $ataqueServidor];
        } else {
            $estado = ['el_usuario' => 'perdio', 'ataqueUser' => $ataqueUsuario, 'ataqueServer' => $ataqueServidor];
        }

        Juego::actualizarEstadoCarta('descartado', $idUser, $idMazo) ;
        Juego::actualizarEstadoCarta('descartado', $idServ, 1) ;
        return $estado;
    }
    public static function gana($idCartaUsuario, $idCartaServidor){
        $cnx = BD::conectar();
    
        // Obtener el atributo de la carta del servidor
        $sqlServidor = "SELECT atributo_id FROM carta WHERE id = :idCartaServidor";
        $stmtServ = $cnx->prepare($sqlServidor);
        $stmtServ->bindParam(':idCartaServidor', $idCartaServidor);
        $stmtServ->execute();
        $atributoServidor = $stmtServ->fetchColumn();
    
        if (!$atributoServidor) {
            return '';
        }
    
        // Buscar si el atributo de la carta del usuario gana al del servidor
        $sql = "SELECT gana_a.atributo_id2 
                FROM gana_a
                INNER JOIN atributo ON gana_a.atributo_id = atributo.id
                INNER JOIN carta ON atributo.id = carta.atributo_id
                WHERE carta.id = :idCartaUsuario";
    
        $stmt = $cnx->prepare($sql);
        $stmt->bindParam(':idCartaUsuario', $idCartaUsuario);
        $stmt->execute();
        $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
        foreach ($resultados as $fila) {
            if ((int)$fila['atributo_id2'] === (int)$atributoServidor) {
                return 'fuerte';
            }
        }
    
        return '';
    }
        
        public static function actualizarEstadoJugada($estado, $id_jugada){
            $cnx = BD::conectar();
            $sql = "UPDATE jugada SET el_usuario = :estado WHERE id = :id_jugada";
            $aux =$cnx->prepare($sql);
            $aux->bindParam(':id_jugada',$id_jugada);
            $aux->bindParam(':estado',$estado);
            return $aux->execute();
            
        }
    
        public static function esQuintaJugada($idPartida){
            $cnx = BD::conectar();
            $sql = "SELECT COUNT(*) as cantidad FROM jugada WHERE partida_id = :idPartida";
            $aux = $cnx->prepare($sql);
            $aux->bindParam(':idPartida',$idPartida);
            $aux->execute();
            $cant = $aux->fetch(\PDO::FETCH_ASSOC);
            return (isset($cant['cantidad']) && $cant['cantidad'] >= 5) ? true:false ;
        }
        public static function getPoderCarta($id){
            $sql = "SELECT ataque FROM carta WHERE id = :id";
            $cnx = BD::conectar();
            $cons = $cnx->prepare($sql);
            $cons->bindParam(':id',$id);
            if ($cons->execute()){
                $datos = $cons->fetch(\PDO::FETCH_ASSOC);
                return $datos['ataque'];
            }else return false;

        }
        public static function ganadorPartida($idPartida){
            $cnx = BD::conectar();
            $sql = "SELECT el_usuario, COUNT(*) as cantidad FROM jugada WHERE partida_id = :idPartida GROUP BY el_usuario";
            $stmt = $cnx->prepare($sql);
            $stmt->bindParam(':idPartida',$idPartida);
            $stmt->execute();
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $estadisticas = [
                'gano' => 0,
                'perdio' => 0,
                'empato' => 0
            ];
            foreach ($resultados as $fila) {
                $estadisticas[$fila['el_usuario']] = $fila['cantidad'];
            }
            if($estadisticas['gano'] >$estadisticas['perdio']){
                $estado = 'gano';
            }elseif($estadisticas['perdio'] >$estadisticas['gano']){
                $estado = 'perdio';
            } else {
                $estado = 'empato';
            }
            if (Juego::resultadoPartida($estado,$idPartida))
                return $estado;
            return false;
        }
        public static function resultadoPartida($estado, $idPartida){
            $cnx = BD::conectar();
            $sql = "UPDATE partida SET el_usuario = :estado WHERE id = :idPartida";
            $aux =$cnx->prepare($sql);
            $aux->bindParam(':idPartida',$idPartida);
            $aux->bindParam(':estado',$estado);
            return $aux->execute();
            
        }
        public static function actualizarEstadoPartida($estado, $idPartida){
            $cnx = BD::conectar();
            $sql = "UPDATE partida SET estado = :estado WHERE id = :id_partida";
            $aux =$cnx->prepare($sql);
            $aux->bindParam(':id_partida',$idPartida);
            $aux->bindParam(':estado',$estado);
            return $aux->execute();
            
        }
    
    public  static function obtenerCartasEnMano(int $usuarioId, int $partidaId): array  {
        try {
            $cnx = BD::conectar();
    
            $sql = "
            SELECT atributo.nombre AS atributo
            FROM mazo_carta
            JOIN carta ON mazo_carta.carta_id = carta.id
            JOIN atributo ON carta.atributo_id = atributo.id
            JOIN mazo ON mazo_carta.mazo_id = mazo.id
            JOIN partida ON partida.mazo_id = mazo.id
            WHERE partida.id = :partidaId
            AND mazo.usuario_id = :usuarioId
            AND mazo_carta.estado = 'en_mano'";
            
            $stmt = $cnx->prepare($sql);
    
            $stmt-> bindParam(':usuarioId', $usuarioId);
            $stmt-> bindParam(':partidaId', $partidaId);
    
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
        } catch (\PDOException $e) {
            return ["error" => "error: " . $e->getMessage()];
            }
        }
    
    public static function validarPartida(int $usuarioId, int $partidaId) {
        $cnx = BD::conectar();

        $sql = "SELECT usuario_id from partida where id =:partidaId";
        $stmt = $cnx ->prepare($sql);
        $stmt -> bindparam(':partidaId', $partidaId);
       if( $stmt ->execute())
        return $datos = $stmt->fetch(\PDO::FETCH_ASSOC);
        else 
            return false;
    }

    public static function mazoDisponible (int $usuarioId, int $mazoId):bool {
        $cnx = BD::conectar();

        $sql = "SELECT 1 FROM partida WHERE mazo_id = :mazoId AND usuario_id = :usuarioId AND estado = 'en_curso'";

        $stmt = $cnx ->prepare($sql);
        $stmt -> bindparam(':mazoId', $mazoId);
        $stmt -> bindparam(':usuarioId', $usuarioId);
        $stmt ->execute();
        return $stmt->fetch() === false;
    }

    public static function getEstadoPartida (int $partidaId):bool {
        $cnx = BD::conectar();

        $sql = "SELECT estado FROM partida WHERE partida.id =:partidaId";

        $stmt = $cnx ->prepare($sql);
        $stmt -> bindparam(':partidaId', $partidaId);
        $stmt ->execute();
        $datos = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($datos["estado"] === "finalizada")
            return true;
        return false; 
    }
}
?>