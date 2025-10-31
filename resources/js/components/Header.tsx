import React from 'react';
import { useAppContext } from '../context/AppContext';

const UserIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2 text-teal-600" viewBox="0 0 20 20" fill="currentColor">
        <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
    </svg>
);

const Header: React.FC = () => {
    const { currentUser, logout } = useAppContext();

    return (
        <header className="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-50">
            <div className="container mx-auto px-4 py-3 flex justify-between items-center">
                <div className="flex items-center space-x-2">
                    <img src="/images/yoga-logo.jpg" alt="Shanti Sadhana Logo" className="rounded-full" />
                    <h1 className="text-2xl font-bold text-teal-800 tracking-tight">Shanti Sadhana</h1>
                </div>
                {currentUser && (
                    <div className="flex items-center gap-4">
                        <div className="flex items-center">
                            <UserIcon />
                            <span className="text-stone-700 font-medium">{currentUser.name}</span>
                        </div>
                        <button
                            onClick={logout}
                            className="bg-stone-200 text-stone-800 font-semibold py-2 px-4 rounded-lg hover:bg-stone-300 transition-colors"
                        >
                            Logout
                        </button>
                    </div>
                )}
            </div>
        </header>
    );
};

export default Header;
