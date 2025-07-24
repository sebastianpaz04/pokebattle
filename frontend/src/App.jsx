import LoginPage from './pages/LoginPage';
import RegistroPage from './pages/RegistroPage';
import StatePage from './pages/StatePage';
import HeaderComponent from './components/HeaderComponent/HeaderComponent';
import FooterComponent from './components/footer/FooterComponent';
import AltaMazoPage from './pages/AltaMazoPage';
import JugarPage from './pages/jugar/JugarPage';
import 'bootstrap/dist/css/bootstrap.min.css';
import './App.css'
import EditUserPage from './pages/EditUserPage';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import MisMazos from './pages/MisMazos';
import { ToastContainer} from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
function App() {
  return (
    <Router>
       <div className="app-container">
     <HeaderComponent /> 

      <main className="main-content">
        <Routes>
          <Route path='/JugarPage' element = {<JugarPage />} />
          <Route path='/EditUserPage' element = {<EditUserPage />} />
          <Route path="/AltaMazoPage" element={<AltaMazoPage />} /> {/* <-- agregar esta línea */}
          <Route path="/" element={<StatePage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/registro" element={<RegistroPage />} />
          <Route path="/estadisticas" element={<StatePage />} />
          <Route path='/MisMazos' element={<MisMazos />}/>
        </Routes>
      </main>
     <FooterComponent />
     <ToastContainer position = "bottom-right" autoClose={30000} hideProgressBar style ={{zIndex :99999}} className="z-3 position-fixed"/>
      </div>
    </Router>
  );
}

export default App;
