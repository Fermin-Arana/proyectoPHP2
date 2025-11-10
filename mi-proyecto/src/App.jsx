//import './App.css'
import HeaderComponent from './components/general/HeaderComponent.jsx';
import FooterComponent from './components/general/FooterComponent.jsx';
import { AuthProvider } from './context/AuthContext';

function App() {

  return (
    <>
      <AuthProvider>
        <HeaderComponent />
        <FooterComponent />
      </AuthProvider>
    </>
  )
}

export default App
