import React, { useState } from 'react';
import { Eye, EyeOff } from 'lucide-react';

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  variant?: 'default' | 'error' | 'success';
  icon?: React.ReactNode;
  iconPosition?: 'left' | 'right';
  showPasswordToggle?: boolean;
}

export const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ variant = 'default', className = '', disabled, icon, iconPosition = 'left', showPasswordToggle, type, ...props }, ref) => {
    const [showPassword, setShowPassword] = useState(false);
    const isPassword = type === 'password';
    const effectiveType = isPassword && showPassword ? 'text' : type;

    const variantClass = variant === 'error' 
      ? 'input-error' 
      : variant === 'success' 
      ? 'input-success' 
      : 'input-default';

    const disabledClass = disabled ? 'input-disabled' : '';

    const paddingClass = icon
      ? iconPosition === 'left'
        ? 'pl-9'
        : 'pr-9'
      : '';

    const togglePadding = showPasswordToggle && isPassword ? 'pr-10' : '';

    return (
      <div className="relative w-full">
        {icon && iconPosition === 'left' && (
          <span className="absolute left-3 top-1/2 -translate-y-1/2 flex items-center text-muted-foreground pointer-events-none">
            {icon}
          </span>
        )}
        <input
          ref={ref}
          disabled={disabled}
          type={effectiveType}
          className={`input-base ${variantClass} ${disabledClass} w-full ${paddingClass} ${togglePadding} ${className}`.trim()}
          {...props}
        />
        {icon && iconPosition === 'right' && !(showPasswordToggle && isPassword) && (
          <span className="absolute right-3 top-1/2 -translate-y-1/2 flex items-center text-muted-foreground pointer-events-none">
            {icon}
          </span>
        )}
        {showPasswordToggle && isPassword && (
          <button
            type="button"
            tabIndex={-1}
            onClick={() => setShowPassword(prev => !prev)}
            className="absolute right-3 top-1/2 -translate-y-1/2 flex items-center text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
            aria-label={showPassword ? 'Hide password' : 'Show password'}
          >
            {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
          </button>
        )}
      </div>
    );
  }
);

Input.displayName = 'Input';
