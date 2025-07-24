import Button from 'react-bootstrap/Button';
import Modal from 'react-bootstrap/Modal';
import React, { useState } from "react";
import api from '../../api/axiosConfig';
import './ModalEditar.css';

function ModalEditarMazos({ show, handleClose, idMazo }) {
  const [nombreMazo, setNombre] = useState('');

  const actualizarMazo = async (e) => {
    e.preventDefault(); // evitar que recargue la página

    try {
      const response = await api.put(`/mazos/${idMazo}`,
        { nombre: nombreMazo },
        {
          headers: {
            Authorization: `${localStorage.getItem('token')}`
          }
        }
      );

      // Podés cerrar el modal o mostrar un mensaje de éxito si querés
      handleClose();
      window.location.reload();
      alert('!!!! mazo editado !!!!');
    } catch (error) {
      console.error("Error al actualizar el mazo:", error);
    }
  };

  return (
    <Modal show={show} onHide={handleClose}>
      <Modal.Header closeButton>
        <Modal.Title>Editar Mazo</Modal.Title>
      </Modal.Header>
      <Modal.Body>
        <form className='FormularioEditar' onSubmit={actualizarMazo}>
          <label htmlFor="">Nuevo nombre del mazo:</label>
          <input
            type="text"
            value={nombreMazo}
            onChange={(e) => setNombre(e.target.value)}
          />
          <button type="submit">Cambiar nombre</button>
        </form>
      </Modal.Body>
      <Modal.Footer>
        <Button variant="secondary" onClick={handleClose}>
          Cerrar
        </Button>
      </Modal.Footer>
    </Modal>
  );
}

export default ModalEditarMazos;
