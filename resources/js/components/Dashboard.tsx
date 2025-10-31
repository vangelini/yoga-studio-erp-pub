import React from 'react';
import { useAppContext } from '../context/AppContext';
import { Role } from '../types';
import AdminDashboard from './dashboards/AdminDashboard';
import TeacherDashboard from './dashboards/TeacherDashboard';
import ClientDashboard from './dashboards/ClientDashboard';

const Dashboard: React.FC = () => {
    const { currentUser } = useAppContext();

    if (!currentUser) {
        return null;
    }

    const renderDashboard = () => {
        switch (currentUser.role) {
            case Role.Admin:
                return <AdminDashboard />;
            case Role.Teacher:
                return <TeacherDashboard />;
            case Role.Client:
                return <ClientDashboard />;
            default:
                return <div>Please select a user role.</div>;
        }
    };

    return (
        <div>
            <div className="mb-8 p-6 bg-white rounded-xl shadow-sm border border-stone-200">
                <h2 className="text-3xl font-light text-stone-700">
                    Welcome, <span className="font-semibold text-teal-700">{currentUser.name.split(' ')[0]}!</span>
                </h2>
                <p className="text-stone-500 mt-1">
                    You are logged in as a <span className="font-medium">{currentUser.role}</span>.
                </p>
            </div>
            {renderDashboard()}
        </div>
    );
};

export default Dashboard;
