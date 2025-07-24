// Importamos React y algunos hooks necesarios
import React, { useEffect, useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
// Importamos la configuración de axios para conectarnos al backend
import api from '../../api/axiosConfig';
// Importamos estilos para esta página
import './jugar.css';

function JugarPage() {
  // Estados para controlar la lógica del juego
  const [idPartida, setIdPartida] = useState(null); // ID de la partida creada
  const [cartaJugadaUsuario, setCartaJugadaUsuario] = useState(null); // Última carta jugada por el usuario
  const [cartaJugadaServidor, setCartaJugadaServidor] = useState(''); // Última carta jugada por el servidor
  const [resultado, setResultado] = useState(null); // Resultado de la jugada actual
  const [cartasJugadasUsuario, setCartasJugadasUsuario] = useState([]); // Índices de las cartas jugadas por el usuario
  const [cartasJugadasServidor, setCartasJugadasServidor] = useState([]); // Atributos de las cartas jugadas por el servidor
  const [esperandoPartida, setEsperandoPartida] = useState(true); // Controla si se espera la creación de la partida

  // Navegación y localización para obtener datos del estado anterior
  const navigate = useNavigate();
  const location = useLocation();
  const { idMazo, cartas } = location.state || {}; // Recuperamos el mazo y sus cartas desde la página anterior

  // Al montar el componente, se crea una partida si hay datos válidos
  useEffect(() => {
    if (!idMazo || !cartas) {
      navigate('/MisMazos'); // Si no hay datos, redirige al usuario
      return;
    }

    const crearPartida = async () => {
      try {
        const response = await api.post(
          '/partidas',
          { idMazo },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('token')}`
            }
          }
        );
        setIdPartida(response.data.id_partida); // Guardamos el ID de la nueva partida
        setEsperandoPartida(false); // Ya no esperamos
      } catch (error) {
        if (error.response && error.response.data) {
          console.log("Error backend:", error.response.data);
        } else {
          console.log("Error inesperado:", error.message);
        }
      }
    };

    crearPartida();
  }, [idMazo, cartas, navigate]);

  // Función que maneja una jugada del usuario
  const Jugar = async (idCarta, cartaUsuario, indexUsuario) => {
    try {
      const response = await api.post(
        '/jugadas',
        { idCarta, idPartida },
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        }
      );

      // Extraemos los datos de la respuesta del servidor
      const cartaServidor = response.data['carta de servidor'];
      const poderU = response.data['poder carta usuario'];
      const poderS = response.data['poder carta servidor'];

      // Guardamos las cartas jugadas
      setCartaJugadaUsuario(cartaUsuario);
      setCartaJugadaServidor(cartaServidor);
      setCartasJugadasUsuario((prev) => [...prev, indexUsuario]);
      setCartasJugadasServidor((prev) => [...prev, cartaServidor]);

      // Evaluamos el resultado de la jugada
      if (poderU > poderS) {
        setResultado("Ganó el usuario");
      } else if (poderU < poderS) {
        setResultado("Ganó el servidor");
      } else {
        setResultado("Empate");
      }

      // Si ya hay un ganador, mostramos alerta y redirigimos
      if (response.data['el usuario']) {
        setTimeout(() => {
          alert(`¡El usuario ${response.data['el usuario']}!`);
          localStorage.removeItem("partida_en_curso");
          setTimeout(() => {
            navigate('/MisMazos');
          }, 1000);
        }, 1000);
      }
    } catch (error) {
      console.error("Error al jugar:", error);
    }
  };

  return (
    <div>
      {/* Sección superior: cartas del servidor */}
      <ul className="mazoServidor">
        {['fuego', 'fuego', 'agua', 'piedra', 'piedra'].map((atributo, index) => {
          const jugada = cartasJugadasServidor.includes(atributo);
          return (
            <li key={index} className={`cartaS ${jugada ? 'jugada' : ''}`}>
              {atributo}
            </li>
          );
        })}
      </ul>

      {/* Zona central que muestra el duelo actual */}
      {cartaJugadaUsuario && cartaJugadaServidor && (
        <div className="zona-centro">
          <div className="carta-jugada">
            <p><strong>{cartaJugadaUsuario.nombre}</strong></p>
            <p>Carta del usuario</p>
          </div>
          <div className="versus">VS</div>
          <div className="carta-jugada">
            <p><strong>{cartaJugadaServidor}</strong></p>
            <p>Carta del servidor</p>
          </div>
          <div className="resultado">
            <p>{resultado}</p>
          </div>
        </div>
      )}

      {/* Sección inferior: cartas del usuario */}
      <ul className="lista">
        {cartas.map((carta, index) => {
          const yaJugado = cartasJugadasUsuario.includes(index);
          return (
            <li
              key={index}
              className={`carta ${yaJugado ? 'jugada' : ''}`}
              onDoubleClick={() => {
                if (!yaJugado && !esperandoPartida) {
                  Jugar(carta.id, carta, index); // Se juega la carta al hacer doble clic
                }
              }}
            >
              <p><strong>Nombre:</strong> {carta.nombre}</p>
              <p><strong>Atributo:</strong> {carta.atributo}</p>
              <p><strong>Ataque:</strong> {carta.ataque}</p>
            </li>
          );
        })}
      </ul>
    </div>
  );
}

export default JugarPage;
