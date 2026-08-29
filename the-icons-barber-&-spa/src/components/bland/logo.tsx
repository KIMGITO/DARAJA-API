import React from 'react';
import { useApp } from '../../context/AppContext';

export const Logo: React.FC = () => {
  const { navigateTo } = useApp();

  return (
    <div
      className="cursor-pointer inline-block group"
      onClick={() => navigateTo('/')}
    >
      <div className="flex items-center justify-center sm:justify-start gap-1">
        <span className="font-script text-4xl sm:text-5xl text-primary tracking-wide transform -rotate-2 select-none group-hover:text-primary-hover group-hover:scale-105 transition-all duration-300 drop-shadow-[0_2px_10px_color-mix(in_srgb,var(--primary)_30%,transparent)]">
          The Icons
        </span>
      </div>
      <p className="text-[11px] sm:text-xs font-serif italic tracking-wider text-muted-foreground-light mt-0.5">
        Barber & Spa
      </p>
    </div>
  );
};

export default Logo;