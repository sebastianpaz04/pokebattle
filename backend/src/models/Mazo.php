<?php
    namespace App\models;
    use App\models\BD;
    use Exception;
    class Mazo{
        public static function cantMazos($id){
            $sql = "SELECT COUNT(*) AS cantidad FROM mazo WHERE usuario_id = :id";
            $conn = BD::conectar();
            $cons = $conn -> prepare($sql);
            $cons ->bindParam(":id", $id);
            $cons -> execute();
            $datos = $cons -> fetch(\PDO::FETCH_ASSOC);
            return ($datos['cantidad'] < 3) ? true : false ;
        }
        public static function cartaValida($id){
            $cnx = BD::conectar();
            $sql = "SELECT id FROM carta WHERE id = :id";
            $aux =$cnx->prepare($sql);
            $aux ->bindParam(':id', $id);
            $aux -> execute();
            return ($aux->rowCount()>0)? true : false ;
        }
        public static function crearMazo($id,$nombre){
            $sql = 'INSERT INTO mazo (usuario_id,nombre) VALUES (:id,:nombre)';
            $conn = BD::conectar();
            $consulta = $conn -> prepare($sql);
            $consulta ->bindParam(':id', $id);
            $consulta->bindParam(':nombre',$nombre);
            if ($consulta->execute())
                return $conn->lastInsertId();
            return false;
        }
        public static function mazo_carta($idCarta,$idMazo,$estado){
            $sql = 'INSERT INTO mazo_carta (carta_id,mazo_id,estado) VALUES (:idC,:idMazo,:estado)';
            $conn = BD::conectar();
            $consulta = $conn -> prepare($sql);
            $consulta->bindParam('idC', $idCarta);
            $consulta->bindParam('idMazo', $idMazo);
            $consulta->bindParam('estado', $estado);
            return $consulta->execute();

        }
       public static function buscarCartas($atributo , $nombre ) {
            // agregar try y catch
                // me conecto a la base de datos
                $cnx = BD::conectar();
                
                // selecciono toda las cartas
                $sql = "SELECT 
                c.id, 
                c.nombre, 
                c.ataque, 
                c.ataque_nombre,
                c.atributo_id, 
                a.nombre AS nombre_atributo
                FROM 
                carta c
                JOIN 
                atributo a ON c.atributo_id = a.id";

                //arreglo de las condicion de filtro con el where
                $conditions = [];
                //arreglo con los parametros que se vincularan con las variables pasadas a la funcion, bindParamar con prepare
                $params = [];
            
                if ($atributo !== null) {
                    $conditions[] = "a.nombre = :atributo";
                    $params[':atributo'] = $atributo;
                }
            
                if ($nombre !== null) {
                    $conditions[] = "c.nombre LIKE :nombre";
                    $params[':nombre'] = "%$nombre%";
                }
            
                // si se cumple se lo agrego a sql
                if (!empty($conditions)) {
                    $sql .= " WHERE " . implode(" AND ", $conditions);
                }
            
                $stmt = $cnx->prepare($sql);
                $stmt->execute($params);
            
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

        public static function actualizarEstadoMazo($estado,$idMazo){
            $sql = "UPDATE mazo_carta SET estado = :estado WHERE mazo_id = :id";
            $cnx = BD::conectar();
            $consulta = $cnx->prepare($sql);
            $consulta->bindParam(":estado", $estado);
            $consulta->bindParam(":id", $idMazo);
            if (!$consulta->execute())
                throw new Exception('error en peticion sql actualizarEstadoMazo de Mazo');
        }
        public static function cambiarNombreMazo($mazoId, $nuevoNombre, $userId) {

            $cnx = BD::conectar();
            $sql = "UPDATE mazo SET nombre = :nombre WHERE id = :mazoId and usuario_id = :userId";
            $stmt = $cnx->prepare($sql);

            $stmt->bindParam(':nombre', $nuevoNombre);
            $stmt->bindParam(':mazoId', $mazoId);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            return $stmt->rowCount() > 0;

        }   
public static function obtenerMazoID($userId) {
    $cnx = BD::conectar();

    $sql = "
        SELECT 
            m.id AS mazo_id,
            m.nombre AS nombre_mazo,
            c.id AS carta_id,
            c.nombre AS nombre_carta,
            c.ataque AS ataque,
            c.ataque_nombre,
            a.nombre AS atributo
        FROM mazo m
        INNER JOIN mazo_carta mc ON m.id = mc.mazo_id
        INNER JOIN carta c ON mc.carta_id = c.id
        INNER JOIN atributo a ON c.atributo_id = a.id
        WHERE m.usuario_id = :id
        
        ORDER BY m.id, c.id 
    ";

    $stmt = $cnx->prepare($sql);
    $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);
    $stmt->execute();
    $filas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Agrupar cartas por mazo
    $mazos = [];

    foreach ($filas as $fila) {
        $mazoId = $fila['mazo_id'];

        if (!isset($mazos[$mazoId])) {
            $mazos[$mazoId] = [
                'id' => $mazoId,
                'nombre' => $fila['nombre_mazo'],
                'cartas' => []
            ];
        }

        $mazos[$mazoId]['cartas'][] = [
            'id' => $fila['carta_id'],
            'nombre' => $fila['nombre_carta'],
            'ataque' => $fila['ataque'],
            'ataque_nombre' => $fila['ataque_nombre'],
            'atributo' => $fila['atributo']
        ];
    }

    return array_values($mazos); // Devuelve un array limpio y ordenado
}


      
        public static function borrarMazo(int $mazoId, int $userId): bool {
            // Verificar participación en partidas
            $cnx = BD::conectar();
            $sql = "SELECT 1 FROM partida p JOIN mazo m ON p.mazo_id = m.id WHERE m.id = :mazoId AND m.usuario_id = :userId LIMIT 1";
            $stmt = $cnx->prepare($sql);
            $stmt->bindParam(':mazoId', $mazoId, \PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetch()) {
                throw new Exception("El mazo ya participó en una partida y no puede borrarse.");
            }
            $cnx = BD::conectar();
            $sql = "DELETE FROM mazo WHERE id = :mazoId AND usuario_id = :userId";
            $stmt = $cnx->prepare($sql);
            $stmt->bindParam(':mazoId', $mazoId);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        }
      
    }
    

?>