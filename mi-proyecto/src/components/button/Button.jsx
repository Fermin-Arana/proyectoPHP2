
import './Button.css'; 

const Button = ({
    onClick,
    children,
    type = 'button',
    className = '', 
    disabled = false
}) => {
    return (
        <button
            type={type}
            onClick={onClick}
            disabled={disabled}
            className={`tenis-button ${className}`} 
        >
            {children}
        </button>
    )
}

export default Button;