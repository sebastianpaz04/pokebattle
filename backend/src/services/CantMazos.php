<?php
    use App\models\BD;
    class CantMazos{
        public static function cantMazos($id){
            $sql = "SELECT COUNT(*) AS cantidad FROM mazo WHERE usuario_id = :id";
            $conn = BD::conectar();
            $cons = $conn -> prepare($sql);
            $cons ->bindParam(":id", $id);
            $cons -> execute();
            $datos = $cons -> fetch(PDO::FETCH_ASSOC);
            return (count($datos['cantidad']) < 3) ? true : false ;
        }
    }
?>