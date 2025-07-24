import React, { createContext, useContext, useState, useEffect } from 'react';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [usuario, setUsuario] = useState(null);
  const [token, setToken] = useState(null);

  useEffect(() => {
    // Al montar, recuperar de localStorage
    const storedToken = localStorage.getItem('token');
    const storedUsuario = localStorage.getItem('usuario');

    if (storedToken && storedUsuario) {
      setToken(storedToken);
      setUsuario(JSON.parse(storedUsuario));
    }
  }, []);

  // Cuando cambia el token o usuario, lo guardamos
  useEffect(() => {
    if (token) localStorage.setItem('token', token);
    if (usuario) localStorage.setItem('usuario', JSON.stringify(usuario));
  }, [token, usuario]);

  return (
    <AuthContext.Provider value={{ usuario, setUsuario, token, setToken }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
