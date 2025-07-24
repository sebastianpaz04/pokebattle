// Importamos React y sus hooks useState y useEffect
import React, { useEffect, useState } from 'react';
// Función utilitaria que agrega imágenes a las cartas
import { agregarImagenes } from '../utils/AgregarImagenCarta';
// Axios configurado para hacer peticiones a la API
import api from '../api/axiosConfig';
// Estilos específicos para esta página
import './AltaMazo.css';

function AltaMazoPage() {
  // Estados del formulario y lógica del componente
  const [nombreMazo, setNombreMazo] = useState(''); // Nombre que el usuario le da al mazo
  const [cartas, setCartas] = useState([]); // Cartas disponibles para seleccionar
  const [mazo, setMazo] = useState([]); // Cartas seleccionadas por el usuario para el mazo
  const [atributos, setAtributos] = useState([]); // Atributos únicos que se usarán como filtros
  const [filtroNombre, setFiltroNombre] = useState(''); // Filtro de búsqueda por nombre de carta
  const [filtroAtributo, setFiltroAtributo] = useState(''); // Filtro por atributo de la carta
  const [mensaje, setMensaje] = useState(''); // Mensaje de alerta o error

  // Se obtiene el token JWT del usuario desde localStorage
  const token = localStorage.getItem('token');

  // Al montar el componente se cargan los atributos disponibles de las cartas
  useEffect(() => {
    const cargarAtributos = async () => {
      try {
        const response = await api.get('/cartas');
        const dato = response.data;
        const nombresAtributos = dato.map(c => c.nombre_atributo);
        const atributosUnicos = Array.from(new Set(nombresAtributos)); // Quitamos duplicados
        setAtributos(atributosUnicos);
      } catch (error) {
        console.error("Error al cargar atributos:", error);
      }
    };
    cargarAtributos();
  }, []);

  // Cada vez que se modifica algún filtro se vuelve a cargar la lista de cartas filtradas
  useEffect(() => {
    const cartasFiltradas = async () => {
      try {
        const params = {};
        if (filtroNombre) params.nombre = filtroNombre;
        if (filtroAtributo) params.atributo = filtroAtributo;

        const response = await api.get('/cartas', { params });
        const cartasConImagen = agregarImagenes(response.data); // Agregamos imágenes a cada carta
        setCartas(cartasConImagen);
      } catch (error) {
        console.error("No se pudo traer los datos", error);
      }
    };
    cartasFiltradas();
  }, [filtroAtributo, filtroNombre]);

  // Agrega una carta al mazo si no se supera el límite de 5 y no está repetida
  const agregarAlMazo = (cartaSeleccionada) => {
    if (mazo.length >= 5) {
      alert("¡No puedes agregar más de 5 cartas al mazo!");
      return;
    }
    if (!mazo.some(carta => carta.id === cartaSeleccionada.id)) {
      setMazo(prevMazo => [...prevMazo, cartaSeleccionada]);
    }
  };

  // Envía los datos del nuevo mazo al backend
  const crearMazo = async () => {
    if (!nombreMazo) {
      alert("Por favor, ingresa un nombre para el mazo.");
      return;
    }
    if (mazo.length < 5) {
      alert("Debes agregar al menos 5 cartas");
      return;
    }

    // Se arma el payload con el nombre del mazo y los IDs de las cartas seleccionadas
    const payload = { nombre: nombreMazo };
    mazo.forEach((carta, index) => {
      payload[`id${index + 1}`] = String(carta.id);
    });

    try {
      await api.post('/mazos', payload, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      alert("¡Mazo creado exitosamente!");
      setMazo([]);
      setNombreMazo('');
      setMensaje('');
    } catch (error) {
      console.error("Error al crear el mazo:", error);
      const mensajeError = error.response?.data?.message ||
        error.response?.data?.error ||
        "Ocurrió un error al crear el mazo.";
      alert(mensajeError);
    }
  };

  return (
    <div className="container mt-5">
      {/* Filtros de búsqueda de cartas */}
      <div className="row mb-4 align-items-center">
        <div className="col-md-4">
          <input
            type="text"
            className="form-control"
            value={filtroNombre}
            onChange={(e) => setFiltroNombre(e.target.value)}
            placeholder="Filtrar por nombre"
          />
        </div>

        <div className="col-md-4">
          <select
            className="form-select"
            value={filtroAtributo}
            onChange={(e) => setFiltroAtributo(e.target.value)}
          >
            <option value="">Todos los atributos</option>
            {atributos.map((attr, i) => (
              <option key={i} value={attr}>{attr}</option>
            ))}
          </select>
        </div>

        <div className="col-md-4">
          <button
            className="btn btn-outline-secondary w-100"
            onClick={() => {
              setFiltroNombre('');
              setFiltroAtributo('');
            }}
          >
            Limpiar filtros
          </button>
        </div>
      </div>

      {/* Sección de cartas disponibles */}
      <div className="row">
        <div className="col-md-8">
          <h2>Cartas disponibles</h2>
          <div className="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
            {cartas.map((carta) => (
              <div className="col" key={carta.id}>
                <div className="card h-100 text-center">
                  {/* Imagen de la carta */}
                  {carta.imagen && (
                    <img
                      src={carta.imagen}
                      className="card-img-top mx-auto"
                      alt={carta.nombre}
                      style={{
                        width: '80px',
                        height: 'auto',
                        objectFit: 'contain',
                        marginTop: '10px'
                      }}
                    />
                  )}
                  <div className="card-body p-2">
                    <h6 className="card-title mb-1">{carta.nombre}</h6>
                    <p className="card-text mb-1" style={{ fontSize: '0.85rem' }}>
                      Ataque: {carta.ataque}
                    </p>
                    <p className="card-text mb-2" style={{ fontSize: '0.85rem' }}>
                      Atributo: {carta.nombre_atributo}
                    </p>
                    <button
                      className="btn btn-sm btn-primary"
                      onClick={() => agregarAlMazo(carta)}
                    >
                      Agregar
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Sección de mazo creado */}
        <div className="col-md-4">
          <div className="sticky-top" style={{ top: '100px' }}>
            <div className="card">
              <div className="card-header">
                Mazo seleccionado ({mazo.length}/5)
              </div>
              <div className="card-body">
                {/* Vista previa de cartas seleccionadas */}
                <div className="d-flex flex-wrap justify-content-center mb-3">
                  {mazo.map((carta) => (
                    <div key={carta.id} className="m-1">
                      {carta.imagen && (
                        <img
                          src={carta.imagen}
                          alt={carta.nombre}
                          className="img-thumbnail"
                          style={{ width: '80px' }}
                        />
                      )}
                    </div>
                  ))}
                </div>

                {mensaje && (
                  <div className="alert alert-warning text-center p-2">
                    {mensaje}
                  </div>
                )}

                {/* Input para nombre del mazo */}
                <div className="mb-2">
                  <label className="form-label">Nombre del mazo</label>
                  <input
                    type="text"
                    className="form-control"
                    value={nombreMazo}
                    onChange={(e) => setNombreMazo(e.target.value)}
                  />
                </div>

                {/* Botón para crear mazo */}
                <button className="btn btn-success w-100" onClick={crearMazo}>
                  Crear mazo
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default AltaMazoPage;
