import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../../api/axiosConfig';
import './Nav.css';
import { toast } from 'react-toastify';

// Función para decodificar el payload de un JWT (sin librerías externas)
function obtenerPayloadDelToken(token) {
  const partes = token.split('.');
  if (partes.length !== 3) return null; // Token inválido si no tiene 3 partes

  try {
    // Decodificamos la parte payload, que viene en base64url, a JSON
    const payloadBase64 = partes[1].replace(/-/g, '+').replace(/_/g, '/');
    const payloadJson = decodeURIComponent(
      atob(payloadBase64)
        .split('')
        .map(c => `%${('00' + c.charCodeAt(0).toString(16)).slice(-2)}`)
        .join('')
    );
    return JSON.parse(payloadJson); // Retornamos el payload decodificado como objeto
  } catch (e) {
    console.error('Error decodificando el token:', e);
    return null;
  }
}

// Función para determinar si el token expiró basado en su campo exp (timestamp en segundos)
function tokenExpirado(payload) {
  if (!payload?.exp) return true; // Si no tiene exp, consideramos expirado
  const ahora = Math.floor(Date.now() / 1000); // Tiempo actual en segundos
  return ahora >= payload.exp; // Retorna true si ya expiró
}

// Función para obtener el nombre del usuario desde el backend dado su id y token
async function obtenerNombreUsuario(id, token) {
  try {
    const datos = await api.get(`/usuarios/${id}`, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    return datos.data.nombre; // Retorna el nombre de usuario
  } catch (error) {
    console.log(error);
    return null; // En caso de error, retorna null
  }
}

// Componente que muestra los links según si el usuario está logueado o no
function UsuarioLogueado() {
  // Estado para almacenar el nombre del usuario y el token
  const [nombre, setNombre] = useState(null);
  const [token, setToken] = useState(localStorage.getItem('token'));
  const navigate = useNavigate();

  // Función para cerrar sesión: limpia token, nombre y notifica a otras pestañas
  const cerrarSesion = () => {
    localStorage.removeItem('token');
    setToken(null);
    setNombre(null);
    window.dispatchEvent(new Event('tokenUpdated')); // Evento custom para sincronizar otras pestañas
    toast.success('Sesión cerrada correctamente');
    navigate('/'); // Redirige a home
  };

  // useEffect para cargar el nombre del usuario cada vez que cambia el token
  useEffect(() => {
    const cargarNombre = async () => {
      if (token) {
        const payload = obtenerPayloadDelToken(token);
        console.log("Payload decodificado:", payload);

        if (!tokenExpirado(payload)) {
          // Si el token es válido, obtenemos el nombre del usuario desde el backend
          const nombreUsuario = await obtenerNombreUsuario(payload.data.id, token);
          setNombre(nombreUsuario);
        } else {
          // Si el token expiró, cerramos sesión automáticamente
          console.warn("Token expirado, cerrando sesión...");
          cerrarSesion();
        }
      } else {
        setNombre(null); // Si no hay token, aseguramos limpiar el nombre
      }
    };

    cargarNombre();

    // Escuchamos cambios en el localStorage y eventos personalizados para actualizar el token localmente
    const onStorageChange = () => {
      const nuevoToken = localStorage.getItem('token');
      setToken(nuevoToken);
    };

    window.addEventListener('storage', onStorageChange);
    window.addEventListener('tokenUpdated', onStorageChange);

    // Limpiamos los listeners al desmontar el componente
    return () => {
      window.removeEventListener('storage', onStorageChange);
      window.removeEventListener('tokenUpdated', onStorageChange);
    };
  }, [token]);

  // Renderizado condicional:
  // Si no hay token o nombre, mostramos links para login y registro
  if (!token || !nombre) {
    return (
      <>
        <Link to="/login">Iniciar sesión</Link>
        <Link to="/registro">Registrarse</Link>
      </>
    );
  }

  // Si hay usuario logueado, mostramos saludo, links y opción para cerrar sesión
  return (
    <>
      <h4>Hola {nombre}</h4>
      <Link to="/MisMazos">Mis mazos</Link>
      <Link to="/EditUserPage">Editar usuario</Link>
      <a
        href="#"
        onClick={(e) => {
          e.preventDefault();
          cerrarSesion();
        }}
      >
        Cerrar sesión
      </a>
    </>
  );
}

// Componente principal de navegación que incluye el componente UsuarioLogueado y un link extra
function NavComponent() {
  return (
    <nav>
      <UsuarioLogueado />
      <Link to="/estadisticas">Estadísticas</Link>
    </nav>
  );
}

export default NavComponent;
