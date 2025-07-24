<?php

namespace App\services;
use App\models\BD;
class JugadaServidor{
    static function jugadaServidor(){
        $cnx = BD::conectar();
        $sql = "SELECT mazo_carta.carta_id FROM mazo INNER JOIN  mazo_carta ON mazo.id = mazo_carta.mazo_id WHERE mazo.usuario_id = 1 AND mazo_carta.estado = 'en_mano'";
        $consulta = $cnx->prepare($sql);
        $consulta->execute();
        $ids = $consulta->fetchAll(\PDO::FETCH_ASSOC);
        $indice =array_rand($ids);
        if (empty($ids)) {
            throw new \Exception("No hay cartas en mano para el servidor.");
        }
        return $ids[$indice]['carta_id'];
    }
}
?>