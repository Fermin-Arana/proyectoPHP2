import logo from '../../assets/iconopagina.svg';

const HeaderComponent = () => {
    return(
        <>
            <div className="header">
                <a href="/" className="header-link">
                    <img src={logo} alt="Logo" className="header-logo" />
                    <h1>Tenis-Plus</h1>
                </a>
            </div>
        </>
    )
}

export default HeaderComponent;