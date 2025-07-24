<?php
namespace App\models;
    use App\models\BD;
    use Exception;
class Estadisticas {
    public static function estadisticas() {
        $cnx = BD::conectar();

        $sql = "
           SELECT 
    u.usuario AS usuario,
    SUM(CASE WHEN p.el_usuario = 'gano' THEN 1 ELSE 0 END) AS ganadas,
    SUM(CASE WHEN p.el_usuario = 'empato' THEN 1 ELSE 0 END) AS empatadas,
    SUM(CASE WHEN p.el_usuario = 'perdio' THEN 1 ELSE 0 END) AS perdidas
    FROM partida p
    JOIN usuario u ON u.id = p.usuario_id
    WHERE p.estado = 'finalizada'
    GROUP BY u.usuario
            ";

        $stmt = $cnx->prepare($sql);
        $stmt->execute();

        $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $response = [];
foreach ($resultados as $fila) {
    $response[$fila['usuario']] = [
        'ganadas' => (int)$fila['ganadas'],
        'empatadas' => (int)$fila['empatadas'],
        'perdidas' => (int)$fila['perdidas']
    ];
}

        return $response;
    }
}

?>