import React from 'react';

const Navbar = () => {
  return (
    <nav className="bg-white shadow-sm">
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between h-16">
          <div className="flex items-center space-x-2">
            <div className="logo-icon flex items-center justify-center">
              <span className="text-white font-bold text-lg">B</span>
            </div>
            <span className="text-xl font-bold text-dark">Biletly</span>
          </div>
          
          <div className="hidden md:flex items-center space-x-8">
            <a href="#" className="text-dark hover:text-primary font-medium">Seferler</a>
            <a href="#" className="text-dark hover:text-primary font-medium">Kampanyalar</a>
          </div>
          
          <button className="bg-primary hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-full transition duration-300">
            Giriş / Kayıt
          </button>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;