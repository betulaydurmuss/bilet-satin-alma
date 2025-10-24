import React, { useState } from 'react';

const SearchSection = ({ onSearch }) => {
  const [formData, setFormData] = useState({
    kalkis: '',
    varis: '',
    tarih: '',
    yolcu: '1'
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSearch(formData);
  };

  const departureCities = [
    "İstanbul (Esenler)",
    "Ankara (AŞTİ)",
    "İzmir (Eşrefpaşa)",
    "Antalya",
    "Bursa",
    "Adana"
  ];

  const arrivalCities = [
    "Ankara (AŞTİ)",
    "İstanbul (Esenler)",
    "İzmir (Eşrefpaşa)",
    "Antalya",
    "Bursa",
    "Adana"
  ];

  const passengerOptions = [
    { value: '1', label: '1 Yolcu' },
    { value: '2', label: '2 Yolcu' },
    { value: '3', label: '3 Yolcu' },
    { value: '4', label: '4 Yolcu' }
  ];

  return (
    <div className="bg-white rounded-xl shadow-md p-6 mb-8">
      <form onSubmit={handleSubmit}>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Kalkış</label>
            <select
              name="kalkis"
              value={formData.kalkis}
              onChange={handleChange}
              className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
            >
              <option value="">Kalkış noktası seçin</option>
              {departureCities.map((city, index) => (
                <option key={index} value={city}>{city}</option>
              ))}
            </select>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Varış</label>
            <select
              name="varis"
              value={formData.varis}
              onChange={handleChange}
              className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
            >
              <option value="">Varış noktası seçin</option>
              {arrivalCities.map((city, index) => (
                <option key={index} value={city}>{city}</option>
              ))}
            </select>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Tarih</label>
            <div className="relative">
              <input
                type="date"
                name="tarih"
                value={formData.tarih}
                onChange={handleChange}
                className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
              />
              <div className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg className="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clipRule="evenodd" />
                </svg>
              </div>
            </div>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Yolcu</label>
            <select
              name="yolcu"
              value={formData.yolcu}
              onChange={handleChange}
              className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
            >
              {passengerOptions.map((option, index) => (
                <option key={index} value={option.value}>{option.label}</option>
              ))}
            </select>
          </div>
        </div>
        
        <div className="flex justify-end">
          <button
            type="submit"
            className="bg-primary hover:bg-orange-600 text-white font-semibold py-3 px-8 rounded-lg transition duration-300 flex items-center"
          >
            <svg className="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clipRule="evenodd" />
            </svg>
            Sefer Ara
          </button>
        </div>
      </form>
    </div>
  );
};

export default SearchSection;