import React, { useState } from 'react';
import Navbar from './Navbar';
import SearchSection from './SearchSection';
import ResultsSection from './ResultsSection';

const App = () => {
  const [searchResults, setSearchResults] = useState([]);
  const [isLoading, setIsLoading] = useState(false);

  const handleSearch = (searchData) => {
    setIsLoading(true);
    setTimeout(() => {
      const mockResults = [
        {
          id: 1,
          departureTime: "09:00",
          arrivalTime: "16:30",
          route: "İstanbul (Esenler) – Ankara (AŞTİ)",
          company: "Metro Turizm",
          details: "WiFi, TV, 7.5 saat",
          price: "650 TL",
          hasSeatSelection: true
        },
        {
          id: 2,
          departureTime: "12:30",
          arrivalTime: "20:00",
          route: "İstanbul (Esenler) – Ankara (AŞTİ)",
          company: "Kamil Koç",
          details: "WiFi, TV, 7.5 saat",
          price: "700 TL",
          hasSeatSelection: false
        },
        {
          id: 3,
          departureTime: "15:00",
          arrivalTime: "22:30",
          route: "İstanbul (Esenler) – Ankara (AŞTİ)",
          company: "Ulusoy",
          details: "WiFi, TV, 7.5 saat",
          price: "600 TL",
          hasSeatSelection: true
        }
      ];
      setSearchResults(mockResults);
      setIsLoading(false);
    }, 1000);
  };

  return (
    <div className="min-h-screen bg-light">
      <Navbar />
      <main className="container mx-auto px-4 py-8">
        <SearchSection onSearch={handleSearch} />
        <ResultsSection results={searchResults} isLoading={isLoading} />
      </main>
    </div>
  );
};

export default App;