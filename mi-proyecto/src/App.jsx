//import './App.css'
import HeaderComponent from './components/general/HeaderComponent.jsx';
import FooterComponent from './components/general/FooterComponent.jsx';
import Home from './components/general/Home.jsx';
import CourtPage from './components/courts/CourtPage.jsx';
import RegisterPage from './components/auth/RegisterPage.jsx';
import LoginPage from './components/auth/LoginPage.jsx';

import { AuthProvider } from './context/AuthContext';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';

function App() {

  return (
    <Router>
      <AuthProvider>
        <HeaderComponent />
        <FooterComponent />
        <Routes>
          <Route path="/" element={<Home/>} />
          <Route path="/courts" element={<CourtPage/>} />
          <Route path="/register" element={<RegisterPage/>} />
          <Route path="/login" element={<LoginPage/>} />
        </Routes>
      </AuthProvider>
    </Router>
  )
}

export default App
