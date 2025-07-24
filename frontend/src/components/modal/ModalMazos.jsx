import Button from 'react-bootstrap/Button';
import Modal from 'react-bootstrap/Modal';

function ModalMazos({ show, handleClose, cartas }) {
  return (
    <Modal show={show} onHide={handleClose}>
      <Modal.Header closeButton>
        <Modal.Title>Detalles del Mazo</Modal.Title>
      </Modal.Header>
      <Modal.Body>
        <ul>
          {cartas && cartas.length > 0 ? (
            cartas.map((carta, index) => (
              <li key={index}>
                <strong>{carta.nombre}</strong> — Ataque: {carta.ataque} ({carta.ataque_nombre}), Atributo: {carta.atributo}
              </li>
            ))
          ) : (
            <li>No hay cartas en este mazo.</li>
          )}
        </ul>
      </Modal.Body>
      <Modal.Footer>
        <Button variant="secondary" onClick={handleClose}>
          Cerrar
        </Button>
      </Modal.Footer>
    </Modal>
  );
}

export default ModalMazos;
