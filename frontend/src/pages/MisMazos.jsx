import React, { useEffect, useState } from "react";
import { Link, useNavigate, useLocation } from 'react-router-dom';

import api from "../api/axiosConfig"; // Importa la configuración de Axios para hacer peticiones al backend
import './MisMazos.css'; // Estilos específicos para esta vista
import ModalMazos from "../components/modal/ModalMazos"; // Modal para ver las cartas del mazo
import ModalEditarMazos from "../components/modal/modalEditarMazo"; // Modal para editar el mazo

// Función que decodifica el JWT para obtener el payload (información del usuario)
function obtenerPayloadDelToken(token) {
  if (!token) return null;

  const partes = token.split(".");
  if (partes.length !== 3) return null;

  try {
    // Decodifica la parte intermedia del token (payload)
    const payloadBase64 = partes[1].replace(/-/g, "+").replace(/_/g, "/");
    const payloadJson = decodeURIComponent(
      atob(payloadBase64)
        .split("")
        .map((c) => `%${("00" + c.charCodeAt(0).toString(16)).slice(-2)}`)
        .join("")
    );
    return JSON.parse(payloadJson);
  } catch (e) {
    console.error("Error decodificando el token:", e);
    return null;
  }
}

function MisMazos() {
  const [mazos, setMazos] = useState([]); // Lista de mazos del usuario
  const [showC, setShowC] = useState(false); // Controla si se muestra el modal de cartas
  const [mostrarCartas, setCartas] = useState([]); // Cartas del mazo seleccionado para ver
  const [showE, setShowE] = useState(false); // Controla si se muestra el modal de edición
  const [mazoAEditar, setMazoAEditar] = useState(null); // ID del mazo que se va a editar

  const navigate = useNavigate(); // Para redirigir al usuario
  const location = useLocation(); // Para detectar si el usuario volvió a esta página

  // Cierra el modal para ver cartas
  const handleCloseVerCartas = () => setShowC(false);

  // Abre el modal para ver cartas de un mazo específico
  const handleShowVerCartas = (cartas) => {
    setCartas(cartas);
    setShowC(true);
  };

  // Cierra el modal para editar mazo
  const handleCloseEditar = () => setShowE(false);

  // Abre el modal para editar un mazo específico
  const handleShowEditar = (id) => {
    setMazoAEditar(id);
    setShowE(true);
  };

  // Función para obtener los mazos del usuario
  const traerDatosMazos = async () => {
    const tokenLocal = localStorage.getItem("token");
    if (!tokenLocal) {
      console.error("No hay token en el localStorage");
      return;
    }

    const payload = obtenerPayloadDelToken(tokenLocal);
    const id = payload?.data?.id;

    if (!id) {
      console.error("ID no encontrado en el token");
      return;
    }

    try {
      // Petición al backend para traer los mazos del usuario
      const response = await api.get(`usuarios/${id}/mazos`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${tokenLocal}` // Enviamos el token por encabezado
        }
      });
      setMazos(response.data.mazos); // Guardamos los mazos obtenidos
      console.log(response.data.mazos);
    } catch (error) {
      console.error("Error al traer mazos:", error.response?.data || error.message);
    }
  };

  // Se ejecuta cuando el componente se monta o cuando la ruta cambia
  useEffect(() => {
    traerDatosMazos();
  }, [location.pathname]);

  // Función para eliminar un mazo
  const eliminarMazo = async (id) => {
    const token = localStorage.getItem("token");
    if (!token) {
      alert("Token no encontrado");
      return;
    }

    const confirmar = window.confirm("¿Estás seguro de que deseas eliminar este mazo?");
    if (!confirmar) return;

    try {
      // Petición DELETE al backend para eliminar el mazo
      const response = await api.delete(`/mazos/${id}`, {
        headers: {
          Authorization: `Bearer ${token}`
        }
      });
      alert(response.data.msj || "Mazo eliminado correctamente");

      // Eliminamos el mazo del estado local para actualizar la vista
      setMazos(prev => prev.filter(m => m.id !== id));
    } catch (error) {
      console.error("Error al eliminar mazo:", error.response?.data || error.message);
      const mensaje = error.response?.data?.msj || error.response?.data?.error || error.message;
      alert(mensaje);
    }
  };

  // Función para jugar una partida con un mazo
  const jugarConMazo = (mazo) => {
    // Guardamos en localStorage la info del mazo con el que se va a jugar
    localStorage.setItem("partida_en_curso", JSON.stringify({
      id: mazo.id,
      nombre: mazo.nombre
    }));

    // Navegamos a la página de juego pasando el mazo como estado
    navigate('/JugarPage', {
      state: {
        idMazo: mazo.id,
        cartas: mazo.cartas
      }
    });
  };

  // Verificamos si hay una partida guardada en curso
  const partidaEnCurso = JSON.parse(localStorage.getItem("partida_en_curso"));

  return (
    <>
      <div className="contenedor">
        <h2>MAZOS DISPONIBLES</h2>

        {/* Lista de mazos del usuario */}
        <ul>
          {mazos.map((mazo, index) => (
            <li key={index} className="mazo-item">
              <span>{mazo.nombre}</span>
              <div className="botones">
                {/* Ver cartas del mazo */}
                <button onClick={() => handleShowVerCartas(mazo.cartas)}>Ver</button>

                {/* Editar mazo */}
                <button onClick={() => handleShowEditar(mazo.id)}>Editar</button>

                {/* Jugar con este mazo (deshabilitado si ya está en uso) */}
                <button
                  onClick={() => jugarConMazo(mazo)}
                  disabled={partidaEnCurso && partidaEnCurso.id === mazo.id}
                >
                  {partidaEnCurso && partidaEnCurso.id === mazo.id
                    ? "Partida en curso"
                    : "Jugar con este mazo"}
                </button>

                {/* Eliminar mazo */}
                <button onClick={() => eliminarMazo(mazo.id)}>Eliminar</button>
              </div>
            </li>
          ))}
        </ul>

        {/* Link para crear un nuevo mazo */}
        <Link to='/AltaMazoPage' className="link_1">Crear Mazo</Link>
      </div>

      {/* Modal para ver las cartas de un mazo */}
      <ModalMazos show={showC} handleClose={handleCloseVerCartas} cartas={mostrarCartas} />

      {/* Modal para editar un mazo */}
      <ModalEditarMazos show={showE} handleClose={handleCloseEditar} idMazo={mazoAEditar} />
    </>
  );
}

export default MisMazos;
