import React from 'react';
import { AppProvider, useAppContext } from './context/AppContext';
import Header from './components/Header';
import Dashboard from './components/Dashboard';
import Footer from './components/Footer';
import Auth from './components/Auth';

const LoadingSpinner: React.FC = () => (
    <div className="fixed inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-[999]">
        <div className="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-teal-600"></div>
    </div>
);

const AppContent: React.FC = () => {
    const { currentUser, loading } = useAppContext();

    if (!currentUser) {
        return (
            <>
                {loading && <LoadingSpinner />}
                <Auth />
            </>
        );
    }

    return (
        <div className="flex flex-col min-h-screen bg-stone-50 font-sans">
            {loading && <LoadingSpinner />}
            <Header />
            <main className="flex-grow container mx-auto px-4 py-8">
                <Dashboard />
            </main>
            <Footer />
        </div>
    );
};

const App: React.FC = () => (
    <AppProvider>
        <AppContent />
    </AppProvider>
);

export default App;
