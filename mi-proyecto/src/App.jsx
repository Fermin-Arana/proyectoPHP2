import './App.css'
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
import CreateBookingPage from './components/bookings/CreateBookingPage.jsx'
import UpdateBookingPage from './components/bookings/UpdateBookingPage.jsx'
import DeleteBookingPage from './components/bookings/DeleteBookingPage.jsx'
import DeleteUserPage from './components/user/DeleteUserPage.jsx'
import InfoUserPage from './components/user/InfoUserPage.jsx'
import { AuthProvider } from './context/AuthContext';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';

function App() {

  return (
    <Router>
      <AuthProvider>
        <div className="app-container"></div>
          <HeaderComponent />
          <NavBarComponent />
          <div className="main-content">
            <Routes>
              <Route path="/" element={<Home/>} />
              <Route path="/courts" element={<CourtPage/>} />
              <Route path="/register" element={<RegisterPage/>} />
              <Route path="/login" element={<LoginPage/>} />
              <Route path="/userlist" element={<UserList/>} />
              <Route path="/edituser/:id" element= {<EditUserPage/>} />
              <Route path="/editpassword" element= {<EditPasswordPage/>} />
              <Route path="logout" element = {<LogoutPage/>}/>
              <Route path="/update-court/:id" element = {< UpdateCourtPage />} />
              <Route path="/delete-court/:id" element = {< DeleteCourtPage />} />
              <Route path="/create-booking" element = {< CreateBookingPage />} />
              <Route path="/update-booking/:id" element = {< UpdateBookingPage />} />
              <Route path="/delete-booking/:id" element = {< DeleteBookingPage />} />
              <Route path="/delete-user/:id" element = {< DeleteUserPage />} />
              <Route path="/info-user/:id" element = {< InfoUserPage />} />
            </Routes>
          </div>
          <FooterComponent />
      </AuthProvider>
    </Router>
  )
}

export default App
