import React, { useState } from "react";
import api from '../api/axiosConfig'; // Importa la configuración de Axios para llamadas HTTP
import { useNavigate } from 'react-router-dom'; // Permite redirigir al usuario a otra ruta
import { toast } from 'react-toastify'; // Muestra notificaciones tipo "toast"

// Función para validar los campos del formulario
function validarDatos({ usuario, nombre, contraseña }) {
  const errores = [];

  // Verifica que los campos no estén vacíos
  if (!usuario.trim()) errores.push("usuario");
  if (!nombre.trim()) errores.push("nombre");
  if (!contraseña.trim()) errores.push("contraseña");

  // Si hay campos vacíos, devuelve un mensaje de error
  if (errores.length > 0) {
    return `Faltan completar los campos: ${errores.join(", ")}`;
  }

  // Verifica longitud mínima y máxima del campo "usuario"
  if (usuario.length < 6)
    return 'El campo "usuario" debe tener más de 6 caracteres';
  if (usuario.length > 20)
    return 'El campo "usuario" debe tener menos de 20 caracteres';

  // Verifica que el usuario tenga solo letras y números
  if (!/^[a-zA-Z0-9]+$/.test(usuario))
    return 'El campo "usuario" solo puede contener letras y números (sin espacios ni símbolos)';

  // Verifica que la contraseña tenga una mayúscula, un número y un carácter especial
  const regexSegura = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).+$/;
  if (!regexSegura.test(contraseña))
    return 'La contraseña debe tener: número, carácter especial, mayúscula';

  // Si pasa todas las validaciones, no devuelve ningún mensaje de error
  return "";
}

function RegistroPage() {
  // Estados para los campos del formulario
  const [usuario, setUsuario] = useState('');
  const [nombre, setNombre] = useState('');
  const [contraseña, setContraseña] = useState('');
  const [Msj, setMsj] = useState(''); // Estado para mostrar mensajes al usuario

  const navigate = useNavigate(); // Hook para redireccionar al usuario

  // Función que se ejecuta al enviar el formulario
  const enviarDatos = async (e) => {
    e.preventDefault(); // Evita el comportamiento por defecto del formulario

    // Se validan los campos
    const mensajeValidacion = validarDatos({ usuario, nombre, contraseña });

    if (mensajeValidacion) {
      // Si hay errores de validación, se muestra el mensaje
      setMsj(mensajeValidacion);
    } else {
      try {
        // Si todo está bien, se envían los datos al backend (ruta /registro)
        const response = await api.post('/registro', { usuario, nombre, contraseña });

        // Se muestra el mensaje del backend
        setMsj(response.data.mensaje);

        // Redirige al usuario a la pantalla de login
        navigate('/login');

        // Recarga la página para que se actualice completamente
        window.location.reload();

        // Muestra una notificación de éxito
        toast.success('REGISTRO COMPLETADO');
      } catch (error) {
        // Si hay un error del backend (por ejemplo, usuario ya registrado), se muestra
        console.log(error);
        setMsj(error.response.data.error);
      }
    }
  };

  return (
    // Contenedor centrado vertical y horizontalmente
    <div className="container vh-100 d-flex justify-content-center align-items-center" style={{ minHeight: "calc(100vh - 100px)" }}>
      <div className="col-md-5">
        {/* Formulario con borde, sombra y fondo blanco */}
        <form onSubmit={enviarDatos} className="border p-4 rounded shadow bg-white">
          <h3 className="mb-4 text-center">Registrarse</h3>

          {/* Campo de entrada: Usuario */}
          <div className="mb-3">
            <label className="form-label">Usuario</label>
            <input
              type="text"
              className="form-control"
              value={usuario}
              onChange={(e) => setUsuario(e.target.value)} // Actualiza el estado
            />
          </div>

          {/* Campo de entrada: Nombre */}
          <div className="mb-3">
            <label className="form-label">Nombre</label>
            <input
              type="text"
              className="form-control"
              value={nombre}
              onChange={(e) => setNombre(e.target.value)} // Actualiza el estado
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
            />
          </div>

          {/* Botón para enviar el formulario */}
          <button type="submit" className="btn btn-primary w-100">
            Registrarse
          </button>

          {/* Mensaje de error o éxito */}
          {Msj && (
            <div className="alert alert-info mt-3" role="alert">
              {Msj}
            </div>
          )}
        </form>
      </div>
    </div>
  );
}

export default RegistroPage;
