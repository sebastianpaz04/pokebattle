<?php
namespace App\models;

use PDOException;
class BD{
    public static function conectar(){
        try{
            $conn = new \PDO("mysql:dbname=pokemones;host:localhost","root","");
            $conn->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION); 
        }catch(\PDOException $e){
            echo "Error en la conexion: " . $e->getMessage();
        }
        return $conn;
    }
}
?>