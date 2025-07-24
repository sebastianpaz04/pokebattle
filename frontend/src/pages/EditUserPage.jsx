import React, { useEffect, useState } from 'react';
import api from '../api/axiosConfig'; // Cliente Axios con configuración predefinida
import { validarNombre, validarContraseña } from "../utils/Validaciones"; // Funciones de validación externas
import { useNavigate } from 'react-router-dom'; // Hook para redirección
import { toast } from 'react-toastify'; // Notificaciones visuales
import { jwtDecode } from 'jwt-decode'; // Librería para decodificar el JWT
import './EditUserPage.css'; // Estilos específicos de esta página

function EditUserPage() {
  // Estados para los campos del formulario
  const [nombre, setNombre] = useState('');
  const [contraseña, setContraseña] = useState('');
  const [contraseña2, setContraseña2] = useState('');
  const navigate = useNavigate(); // Hook de navegación

  // Se obtiene el token del usuario desde localStorage
  const token = localStorage.getItem('token');

  let idUsuario = null; // ID del usuario autenticado

  try {
    // Decodifica el token JWT para obtener el ID del usuario
    const payload = jwtDecode(token);
    idUsuario = payload?.data?.id;
  } catch (error) {
    // Si el token está mal formado o expirado, se notifica el error
    console.error('Error al decodificar el token:', error);
    toast.error('Token inválido o expirado.');
  }

  // Cargar los datos del usuario cuando se monta el componente
  useEffect(() => {
    const cargarDatos = async () => {
      if (!idUsuario || !token) return; // Si falta información, no continúa

      try {
        // Petición GET para obtener los datos actuales del usuario
        const res = await api.get(`/usuarios/${idUsuario}`, {
          headers: { Authorization: `Bearer ${token}` } // Se envía el token por encabezado
        });

        const data = res.data;
        const pwd = data.password || ''; // Por si el campo viene vacío

        console.log("🟢 Datos del usuario:", data);

        // Se cargan los datos obtenidos en el formulario
        setNombre(data.nombre);
        setContraseña(pwd);
        setContraseña2(pwd);
      } catch (error) {
        console.error("Error al cargar los datos del usuario:", error);
        toast.error('No se pudieron cargar los datos del usuario');
      }
    };

    cargarDatos(); // Se ejecuta al montar el componente
  }, [idUsuario, token]);

  // Función que se ejecuta al enviar el formulario
  const enviarDatos = async (e) => {
    e.preventDefault(); // Previene recarga automática del formulario

    if (!idUsuario || !token) {
      toast.error('No se pudo obtener el usuario autenticado.');
      return;
    }

    // Validación de los campos usando funciones externas
    const errorNombre = validarNombre(nombre);
    const errorContraseña = validarContraseña(contraseña);

    if (errorNombre || errorContraseña) {
      toast.error(errorNombre || errorContraseña);
      return;
    }

    // Validación extra: confirmación de contraseña
    if (contraseña !== contraseña2) {
      toast.error('Las contraseñas no coinciden.');
      return;
    }

    try {
      // Enviamos la actualización al backend
      await api.put(
        `/usuarios/${idUsuario}`,
        { nombre, contraseña },
        {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${token}`
          }
        }
      );

      toast.success('Actualización exitosa'); // Feedback positivo
      navigate('/'); // Redireccionamos al inicio
      window.location.reload(); // Forzamos recarga completa de la app
    } catch (error) {
      console.error(error);
      toast.error('Error al actualizar usuario');
    }
  };

  return (
    <div className='contenedor'>
      <h3>Editar Usuario</h3>
      <form onSubmit={enviarDatos}>
        {/* Campo para editar el nombre */}
        <input
          type="text"
          value={nombre}
          onChange={e => setNombre(e.target.value)}
          placeholder="Nuevo nombre"
          required
          className="input-box"
        />
        {/* Campo para editar la contraseña */}
        <input
          type="password"
          value={contraseña}
          onChange={e => setContraseña(e.target.value)}
          placeholder="Nueva contraseña"
          required
          className="input-box"
        />
        {/* Confirmar la nueva contraseña */}
        <input
          type="password"
          value={contraseña2}
          onChange={e => setContraseña2(e.target.value)}
          placeholder="Confirmar nueva contraseña"
          required
          className="input-box"
        />
        <button type="submit">Actualizar</button>
      </form>
    </div>
  );
}

export default EditUserPage;
