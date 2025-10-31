import React, { useState } from 'react';
import { useAppContext } from '../context/AppContext';
import logger from '../services/logger';

const Auth: React.FC = () => {
    const [isLoginView, setIsLoginView] = useState(true);
    const { login, registerClient, loading } = useAppContext();

    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');

    const handleLogin = async (event: React.FormEvent) => {
        event.preventDefault();
        logger.info('Login form submitted for:', email);
        await login(email, password);
    };

    const handleRegister = async (event: React.FormEvent) => {
        event.preventDefault();
        logger.info('Registration form submitted with:', { name, email });
        const success = await registerClient(name, email, password);
        if (success) {
            setIsLoginView(true);
            setName('');
            setEmail('');
            setPassword('');
        }
    };

    return (
        <div className="flex items-center justify-center min-h-screen bg-stone-50">
            <div className="w-full max-w-md mx-auto">
                <div className="text-center mb-8">
                    <img src="/images/yoga-logo-big.jpg" alt="Shanti Sadhana Logo" className="rounded-full mx-auto mb-4" />
                    <h1 className="text-4xl font-bold text-teal-800 tracking-tight">Shanti Sadhana</h1>
                    <p className="text-stone-500 mt-2">Find your inner peace.</p>
                </div>

                <div className="bg-white rounded-xl shadow-lg border border-stone-200">
                    <div className="flex border-b border-stone-200">
                        <button
                            onClick={() => setIsLoginView(true)}
                            className={`flex-1 py-3 font-semibold text-center transition-colors ${
                                isLoginView ? 'text-teal-600 border-b-2 border-teal-600' : 'text-stone-500 hover:bg-stone-50'
                            }`}
                        >
                            Sign In
                        </button>
                        <button
                            onClick={() => setIsLoginView(false)}
                            className={`flex-1 py-3 font-semibold text-center transition-colors ${
                                !isLoginView ? 'text-teal-600 border-b-2 border-teal-600' : 'text-stone-500 hover:bg-stone-50'
                            }`}
                        >
                            Register
                        </button>
                    </div>

                    <div className="p-8">
                        {isLoginView ? (
                            <form onSubmit={handleLogin} className="space-y-6">
                                <h2 className="text-2xl font-semibold text-stone-700 text-center">Welcome Back</h2>
                                <div>
                                    <label className="text-sm font-medium text-stone-600">Email</label>
                                    <input
                                        type="email"
                                        value={email}
                                        onChange={(event) => setEmail(event.target.value)}
                                        className="w-full mt-1 p-3 border rounded-md focus:ring-teal-500 focus:border-teal-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-stone-600">Password</label>
                                    <input
                                        type="password"
                                        value={password}
                                        onChange={(event) => setPassword(event.target.value)}
                                        className="w-full mt-1 p-3 border rounded-md focus:ring-teal-500 focus:border-teal-500"
                                        required
                                    />
                                </div>
                                <button
                                    type="submit"
                                    disabled={loading}
                                    className="w-full bg-teal-600 text-white font-semibold py-3 rounded-md hover:bg-teal-700 transition-colors disabled:bg-teal-400"
                                >
                                    {loading ? 'Signing In...' : 'Sign In'}
                                </button>
                            </form>
                        ) : (
                            <form onSubmit={handleRegister} className="space-y-6">
                                <h2 className="text-2xl font-semibold text-stone-700 text-center">Create Your Account</h2>
                                <div>
                                    <label className="text-sm font-medium text-stone-600">Full Name</label>
                                    <input
                                        type="text"
                                        value={name}
                                        onChange={(event) => setName(event.target.value)}
                                        className="w-full mt-1 p-3 border rounded-md focus:ring-teal-500 focus:border-teal-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-stone-600">Email</label>
                                    <input
                                        type="email"
                                        value={email}
                                        onChange={(event) => setEmail(event.target.value)}
                                        className="w-full mt-1 p-3 border rounded-md focus:ring-teal-500 focus:border-teal-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-stone-600">Password</label>
                                    <input
                                        type="password"
                                        value={password}
                                        onChange={(event) => setPassword(event.target.value)}
                                        className="w-full mt-1 p-3 border rounded-md focus:ring-teal-500 focus:border-teal-500"
                                        required
                                    />
                                </div>
                                <button
                                    type="submit"
                                    disabled={loading}
                                    className="w-full bg-teal-600 text-white font-semibold py-3 rounded-md hover:bg-teal-700 transition-colors disabled:bg-teal-400"
                                >
                                    {loading ? 'Registering...' : 'Register'}
                                </button>
                                <p className="text-xs text-center text-stone-500">Upon registration, an administrator will approve your account.</p>
                            </form>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Auth;
