const Button = ({
    onClick,
    children,
    type='button',
    className='',
    disabled=false
}) => {
    return (
        <button
        type={type}
        onClick={onClick}
        className={className}
        disabled={disabled}>
            {children}
        </button>
    )
}

export default Button