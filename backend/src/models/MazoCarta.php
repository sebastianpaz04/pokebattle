<?php
    namespace App\models;
    use App\models\BD;
    use Exception;
    use PDO;
    class MazoCarta{
        public static function getDatos( $idMazo){
            $sql ="SELECT carta.nombre FROM carta INNER JOIN mazo_carta ON carta.id = mazo_carta.carta_id WHERE mazo_carta.mazo_id = :idMazo";
            $cnx = BD::conectar();
            $consulta = $cnx->prepare($sql);
            $consulta->bindParam(":idMazo", $idMazo);
            if ($consulta->execute())   
            return $consulta->fetchAll(\PDO::FETCH_ASSOC);
            else throw new Exception('error en getDatos de MazoCarta');
            }
            public function actualizarEstado(){
        }
        public static function getDatosCarta($idCarta){
            $sql ="SELECT carta.nombre FROM carta INNER JOIN mazo_carta ON carta.id = mazo_carta.carta_id WHERE mazo_carta.carta_id = :idCarta";
            $cnx = BD::conectar();
            $consulta = $cnx->prepare($sql);
            $consulta->bindParam(':idCarta',$idCarta);
            if($consulta->execute()){
                $datos = $consulta->fetch(PDO::FETCH_ASSOC);
                if ($datos)
                    return $datos['nombre'];
               else throw new Exception('no se encontraron datos con ese id de carta');
            }
            else
                throw new Exception('error al traer datos de la carta getDatosCarta');  
        }
  
        public static function idMazo(int $idPartida){
            $sql = "SELECT mazo_id FROM partida WHERE id = :idP";
            $cnx = BD::conectar();
            $consulta = $cnx->prepare($sql);
            $consulta->bindParam(':idP',$idPartida);
            if ($consulta->execute()){
               $datos = $consulta->fetch(PDO::FETCH_ASSOC);
                return $datos['mazo_id'] ?? null;
            }else{
                throw new Exception('error en idMazo de partida');
            }

        }
    }

        
    
?>