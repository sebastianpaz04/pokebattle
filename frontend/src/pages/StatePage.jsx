import React, { useEffect, useState } from 'react';
import './StatePage.css';
import api from '../api/axiosConfig'; // Importa la instancia de Axios configurada

function StatePage() {
  // Estado para guardar las estadísticas de los jugadores
  const [estadisticas, setEstadisticas] = useState([]);

  // Estado para definir el orden: false = descendente (mejor rendimiento primero)
  const [ordenAscendente, setOrdenAscendente] = useState(false);

  // useEffect se ejecuta una sola vez al montar el componente
  useEffect(() => {
    // Función asincrónica para obtener los datos del backend
    const fetchDatos = async () => {
      try {
        // Se hace una petición GET a /estadisticas
        const response = await api.get('/estadisticas');

        // Se convierte el objeto en un array de entradas: [usuario_id, stats]
        const entries = Object.entries(response.data).map(([usuario_id, stats]) => {
          // Calculamos el total de partidas jugadas
          const total = stats.ganadas + stats.empatadas + stats.perdidas;

          // Calculamos el promedio de partidas ganadas sobre el total
          const promedio = total > 0 ? (stats.ganadas / total) : 0;

          // Devolvemos una tupla con el id y las estadísticas incluyendo el promedio
          return [usuario_id, { ...stats, promedio }];
        });

        // Guardamos las estadísticas en el estado
        setEstadisticas(entries);
      } catch (error) {
        // Si ocurre un error, lo mostramos por consola
        console.error("No se pudo traer los datos", error);
      }
    };

    // Llamamos a la función para cargar los datos
    fetchDatos();
  }, []);

  // Ordenamos las estadísticas en función del estado ordenAscendente
  const estadisticasOrdenadas = [...estadisticas].sort((a, b) => {
    return ordenAscendente
      ? a[1].promedio - b[1].promedio  // Si es ascendente, muestra primero los peores promedios
      : b[1].promedio - a[1].promedio; // Si es descendente, muestra primero los mejores promedios
  });

  return (
    <div className='tabla'>
      <h2>Ranking de jugadores</h2>

      {/* Selector para cambiar el orden del ranking */}
      <div className="filtros">
        <label>Ordenar por: </label>
        <select onChange={(e) => setOrdenAscendente(e.target.value === 'asc')}>
          <option value="desc">Mejor rendimiento</option>
          <option value="asc">Peor rendimiento</option>
        </select>
      </div>

      {/* Tabla con los datos de los jugadores */}
      <table>
        <thead>
          <tr>
            <th>Posición</th>
            <th>Usuario</th>
            <th>Ganadas</th>
            <th>Empatadas</th>
            <th>Perdidas</th>
            <th>Promedio</th>
          </tr>
        </thead>
        <tbody>
          {/* Recorremos las estadísticas ordenadas y generamos una fila por jugador */}
          {estadisticasOrdenadas.map(([usuario_id, stats], index) => (
            <tr key={usuario_id}>
              {/* Mostramos 🥇 para el primer lugar */}
              <td className='Pos'>{index === 0 ? '🥇' : index + 1}</td>
              <td>{usuario_id}</td>
              <td>{stats.ganadas}</td>
              <td>{stats.empatadas}</td>
              <td>{stats.perdidas}</td>
              {/* Mostramos el promedio como porcentaje con 1 decimal */}
              <td>{(stats.promedio * 100).toFixed(1)}%</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default StatePage;
