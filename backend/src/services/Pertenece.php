<?php
    namespace App\services;
    use App\models\BD;
    class Pertenece{
        public static function pertenece(int $idMazo,int $idUser){
            $cnx = BD::conectar();
            $sql ="SELECT COUNT(*) FROM mazo WHERE id = :idMazo AND usuario_id = :idUser";
            $cons = $cnx->prepare($sql);
            $cons->bindParam(":idMazo",$idMazo);
            $cons->bindParam(":idUser", $idUser);
            $cons->execute();
            return ($cons->fetchColumn()>0)? true: false;
        }
    }


?>