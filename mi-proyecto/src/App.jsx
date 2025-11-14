//import './App.css'
import HeaderComponent from './components/general/HeaderComponent.jsx';
import FooterComponent from './components/general/FooterComponent.jsx';
import NavBarComponent from './components/general/NavBarComponent.jsx';
import Home from './components/general/Home.jsx';
import CourtPage from './components/courts/CourtPage.jsx';
import RegisterPage from './components/auth/RegisterPage.jsx';
import LoginPage from './components/auth/LoginPage.jsx';
import UserList from './components/user/UserListPage.jsx'
import EditUserPage from './components/user/EditUserPage.jsx'
import LogoutPage from './components/auth/LogoutPage.jsx'
import UpdateCourtPage from './components/courts/UpdateCourtPage.jsx'
import DeleteCourtPage from './components/courts/DeleteCourtPage.jsx'
import EditPasswordPage from './components/user/EditPasswordPage.jsx'
import { AuthProvider } from './context/AuthContext';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';

function App() {

  return (
    <Router>
      <AuthProvider>
        <HeaderComponent />
        <FooterComponent />
        <NavBarComponent />
        <Routes>
          <Route path="/" element={<Home/>} />
          <Route path="/courts" element={<CourtPage/>} />
          <Route path="/register" element={<RegisterPage/>} />
          <Route path="/login" element={<LoginPage/>} />
          <Route path="/userlist" element={<UserList/>} />
          <Route path="/edituser" element= {<EditUserPage/>} />
          <Route path="/editpassword" element= {<EditPasswordPage/>} />
          <Route path="logout" element = {<LogoutPage/>}/>
          <Route path="/update-court/:id" element = {< UpdateCourtPage />} />
          <Route path="/delete-court/:id" element = {< DeleteCourtPage />} />
        </Routes>
      </AuthProvider>
    </Router>
  )
}

export default App
