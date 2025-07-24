import logo2 from '../../assets/images/logo2.jpg';
import './HeaderComponent.css';
import NavComponent from '../NavComponent/NavComponent';
import { Link } from 'react-router-dom';

function HeaderComponent(){
    return(
        <header>
            <Link to="/" className="d-flex align-items-center text-decoration-none" >
            <img src={logo2} alt="logo" className='logo'/>
            <h3>Pokebattle</h3>
            </Link>
            <NavComponent />
        </header>
    );
}
export default HeaderComponent;