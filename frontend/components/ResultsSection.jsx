import React from 'react';

const ResultsSection = ({ results, isLoading }) => {
  if (isLoading) {
    return (
      <div className="bg-white rounded-xl shadow-md p-6">
        <h2 className="text-2xl font-bold text-dark mb-6">Seferler</h2>
        <div className="flex justify-center items-center h-32">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>
      </div>
    );
  }

  if (results.length === 0) {
    return (
      <div className="bg-white rounded-xl shadow-md p-6">
        <h2 className="text-2xl font-bold text-dark mb-6">Seferler</h2>
        <div className="text-center py-12">
          <svg className="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <h3 className="mt-2 text-lg font-medium text-gray-900">Sefer bulunamadı</h3>
          <p className="mt-1 text-gray-500">Lütfen farklı tarih veya rota seçin</p>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-xl shadow-md p-6">
      <h2 className="text-2xl font-bold text-dark mb-6">Seferler</h2>
      <div className="space-y-4">
        {results.map((result) => (
          <div key={result.id} className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-300">
            <div className="flex flex-col md:flex-row md:items-center justify-between">
              <div className="flex-1">
                <div className="flex items-center mb-2">
                  <div className="text-2xl font-bold text-dark mr-4">{result.departureTime}</div>
                  <div className="text-sm text-gray-500">→</div>
                  <div className="text-lg text-gray-500 ml-4">{result.arrivalTime}</div>
                </div>
                
                <div className="font-bold text-dark mb-1">{result.route}</div>
                <div className="text-sm text-gray-600">{result.company} • {result.details}</div>
              </div>
              
              <div className="mt-4 md:mt-0 md:text-right">
                <div className="text-2xl font-bold text-primary mb-2">{result.price}</div>
                {result.hasSeatSelection ? (
                  <button className="bg-primary hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-300">
                    Koltuk Seç
                  </button>
                ) : (
                  <button className="bg-gray-200 text-gray-500 font-semibold py-2 px-4 rounded-lg cursor-not-allowed" disabled>
                    Satıldı
                  </button>
                )}
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default ResultsSection;