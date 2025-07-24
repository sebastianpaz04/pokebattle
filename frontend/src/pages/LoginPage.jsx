import React, { useState } from 'react';
import api from '../api/axiosConfig'; // Instancia de Axios para peticiones al backend
import { useNavigate } from "react-router-dom"; // Hook para redirigir a otra ruta

function LoginPage() {
  // Estados para los inputs del formulario
  const [usuario, setUsuario] = useState('');
  const [contraseña, setContraseña] = useState('');
  const [mensaje, setMensaje] = useState(''); // Mensaje de error o aviso al usuario

  const navigate = useNavigate(); // Hook para navegar a otra página

  // Función que se ejecuta al enviar el formulario
  const enviarDatos = async (e) => {
    e.preventDefault(); // Evita el comportamiento por defecto del formulario

    try {
      // Petición POST al backend con el usuario y la contraseña
      const respuesta = await api.post('/login', {
        usuario,
        contraseña,
      });

      // Si la autenticación es exitosa, guardamos el token en localStorage
      localStorage.setItem('token', respuesta.data.token); // Importante: primero guardar token

      // Disparamos un evento global para que otros componentes sepan que el token cambió
      window.dispatchEvent(new Event("tokenUpdated"));

      // Luego redirigimos al usuario a la página principal
      navigate('/');
    } catch (error) {
      // Si ocurre un error, lo mostramos en la consola
      console.log(error);

      // Si el backend responde con un mensaje de error, lo mostramos
      if (error.response && error.response.data) {
        setMensaje(error.response.data.error);
      } else {
        // Si es un error de red o de conexión
        setMensaje('Error en la conexión');
      }
    }
  };

  return (
    // Contenedor centrado vertical y horizontalmente
    <div className="container vh-100 d-flex justify-content-center align-items-center">
      <div className="col-md-5">
        {/* Formulario de login con estilos de Bootstrap */}
        <form
          onSubmit={enviarDatos}
          className="border p-4 rounded shadow bg-white"
        >
          <h3 className="mb-4 text-center">Iniciar sesión</h3>

          {/* Campo de entrada: Usuario */}
          <div className="mb-3">
            <label className="form-label">Usuario</label>
            <input
              type="text"
              className="form-control"
              value={usuario}
              onChange={(e) => setUsuario(e.target.value)} // Actualiza el estado
              required // Valida que el campo no esté vacío
            />
          </div>

          {/* Campo de entrada: Contraseña */}
          <div className="mb-3">
            <label className="form-label">Contraseña</label>
            <input
              type="password"
              className="form-control"
              value={contraseña}
              onChange={(e) => setContraseña(e.target.value)} // Actualiza el estado
              required // Valida que el campo no esté vacío
            />
          </div>

          {/* Botón para enviar el formulario */}
          <button type="submit" className="btn btn-primary w-100">
            Iniciar sesión
          </button>

          {/* Muestra mensaje si hay error */}
          {mensaje && (
            <div className="alert alert-info mt-3" role="alert">
              {mensaje}
            </div>
          )}
        </form>
      </div>
    </div>
  );
}

export default LoginPage;
