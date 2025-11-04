import React, { createContext, useState, useContext, ReactNode, useCallback } from 'react';
import { Role, User, Course, Teacher, Booking, AvailabilitySlot, Subscription } from '../types';
import logger from '../services/logger';

const API_URL = '/api';

interface AppContextType {
    currentUser: User | null;
    loading: boolean;
    login: (email: string, password: string) => Promise<void>;
    logout: () => void;
    registerClient: (name: string, email: string, password: string) => Promise<boolean>;
    users: User[];
    courses: Course[];
    teachers: Teacher[];
    bookings: Booking[];
    subscriptions: Subscription[];
    clients: User[];
    updateUser: (userId: number, data: { role: Role; status: User['status'] }) => Promise<void>;
    resetUserPassword: (userId: number, newPassword: string) => Promise<void>;
    addUser: (userData: Omit<User, 'id'>) => Promise<void>;
    addCourse: (newCourseData: Omit<Course, 'id'>) => Promise<void>;
    updateCourse: (updatedCourse: Course) => Promise<void>;
    updateTeacher: (updatedTeacher: Teacher) => Promise<void>;
    setTeacherAvailability: (teacherId: number, availability: AvailabilitySlot[]) => Promise<void>;
    updateTeacherProfile: (teacherId: number, bio: string, specializations: string[], pictureUrl: string) => Promise<void>;
    bookLesson: (teacherId: number, slot: AvailabilitySlot) => Promise<boolean>;
    cancelBooking: (bookingId: number) => Promise<void>;
    subscribeToCourse: (options: { courseId: number; planType: 'monthly' | 'quarterly' | 'annual'; startOption: 'current_month' | 'next_month'; startDate?: string }) => Promise<void>;
}

const AppContext = createContext<AppContextType | undefined>(undefined);

async function fetchApi(path: string, options: RequestInit = {}) {
    const method = options.method || 'GET';
    logger.info(`API Request ===> ${method} ${path}`, options.body ? JSON.parse(options.body as string) : '');

    try {
        const response = await fetch(`${API_URL}${path}`, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers,
            },
        });
        const data = await response.json();
        if (!response.ok) {
            logger.error(`<=== API Error Response (${response.status}) for ${method} ${path}:`, data);
            throw new Error(data.message || 'An API error occurred');
        }
        logger.info(`<=== API Success Response for ${method} ${path}:`, data);
        return data;
    } catch (error) {
        logger.error(`API Fetch Failed for ${method} ${path}:`, error);
        throw error;
    }
}

export const AppProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
    const [currentUser, setCurrentUser] = useState<User | null>(null);
    const [loading, setLoading] = useState<boolean>(false);
    const [users, setUsers] = useState<User[]>([]);
    const [courses, setCourses] = useState<Course[]>([]);
    const [teachers, setTeachers] = useState<Teacher[]>([]);
    const [bookings, setBookings] = useState<Booking[]>([]);
    const [subscriptions, setSubscriptions] = useState<Subscription[]>([]);
    const [clients, setClients] = useState<User[]>([]);

    const handleFetchData = async (user: User) => {
        setLoading(true);
        try {
            const data = await fetchApi('/dashboard-data', {
                method: 'POST',
                body: JSON.stringify({ user }),
            });
            if (data.courses) setCourses(data.courses);
            if (data.teachers) setTeachers(data.teachers);
            if (data.users) setUsers(data.users);
            if (data.bookings) setBookings(data.bookings);
            if (data.subscriptions) setSubscriptions(data.subscriptions);
            if (data.clients) setClients(data.clients);
        } catch (error) {
            alert((error as Error).message);
        } finally {
            setLoading(false);
        }
    };

    const login = useCallback(async (email: string, password: string) => {
        setLoading(true);
        try {
            const { user } = await fetchApi('/auth/login', {
                method: 'POST',
                body: JSON.stringify({ email, password }),
            });
            setCurrentUser(user);
            await handleFetchData(user);
        } catch (error) {
            alert((error as Error).message);
        } finally {
            setLoading(false);
        }
    }, []);

    const logout = useCallback(() => {
        logger.info('User logged out.');
        setCurrentUser(null);
        setUsers([]);
        setCourses([]);
        setTeachers([]);
        setBookings([]);
        setSubscriptions([]);
        setClients([]);
    }, []);

    const registerClient = useCallback(async (name: string, email: string, password: string): Promise<boolean> => {
        setLoading(true);
        try {
            const { success } = await fetchApi('/users', {
                method: 'POST',
                body: JSON.stringify({ name, email, password, role: Role.Client }),
            });

            if (success) {
                alert('Registration successful! You can now log in.');
                return true;
            }

            return false;
        } catch (error) {
            alert((error as Error).message);
            return false;
        } finally {
            setLoading(false);
        }
    }, []);

    const updateUser = async (userId: number, data: { role: Role; status: User['status'] }) => {
        await fetchApi(`/users/${userId}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
        setUsers((prev) => prev.map((user) => (user.id === userId ? { ...user, ...data } : user)));
    };

    const resetUserPassword = async (userId: number, newPassword: string) => {
        await fetchApi(`/users/${userId}/password`, {
            method: 'PUT',
            body: JSON.stringify({ newPassword }),
        });
    };

    const addUser = async (userData: Omit<User, 'id'>) => {
        const { user } = await fetchApi('/users', {
            method: 'POST',
            body: JSON.stringify(userData),
        });
        setUsers((prev) => [...prev, user]);
    };

    const addCourse = async (newCourseData: Omit<Course, 'id'>) => {
        const { course } = await fetchApi('/courses', {
            method: 'POST',
            body: JSON.stringify(newCourseData),
        });
        setCourses((prev) => [...prev, course]);
    };

    const updateCourse = async (updatedCourse: Course) => {
        const { course } = await fetchApi(`/courses/${updatedCourse.id}`, {
            method: 'PUT',
            body: JSON.stringify(updatedCourse),
        });
        setCourses((prev) => prev.map((courseItem) => (courseItem.id === updatedCourse.id ? course : courseItem)));
    };

    const updateTeacher = async (updatedTeacher: Teacher) => {
        await fetchApi(`/teachers/${updatedTeacher.id}`, {
            method: 'PUT',
            body: JSON.stringify(updatedTeacher),
        });
        setTeachers((prev) => prev.map((teacher) => (teacher.id === updatedTeacher.id ? updatedTeacher : teacher)));
        setUsers((prev) => prev.map((user) => (user.id === updatedTeacher.id ? { ...user, name: updatedTeacher.name } : user)));
    };

    const setTeacherAvailability = async (teacherId: number, availability: AvailabilitySlot[]) => {
        const { availability: newAvailability } = await fetchApi(`/teachers/${teacherId}/availability`, {
            method: 'PUT',
            body: JSON.stringify({ availability }),
        });
        setTeachers((prev) => prev.map((teacher) => (teacher.id === teacherId ? { ...teacher, availability: newAvailability } : teacher)));
    };

    const updateTeacherProfile = async (teacherId: number, bio: string, specializations: string[], pictureUrl: string) => {
        const teacher = teachers.find((t) => t.id === teacherId);
        if (!teacher) return;

        await fetchApi(`/teachers/${teacherId}`, {
            method: 'PUT',
            body: JSON.stringify({ name: teacher.name, profilePictureUrl: pictureUrl, bio, specializations }),
        });

        setTeachers((prev) =>
            prev.map((t) => (t.id === teacherId ? { ...t, bio, specializations, profilePictureUrl: pictureUrl } : t)),
        );
    };

    const bookLesson = async (teacherId: number, slot: AvailabilitySlot): Promise<boolean> => {
        if (!currentUser) return false;
        setLoading(true);
        try {
            await fetchApi('/bookings', {
                method: 'POST',
                body: JSON.stringify({ clientId: currentUser.id, teacherId, slot }),
            });
            await handleFetchData(currentUser);
            return true;
        } catch (error) {
            alert((error as Error).message);
            return false;
        } finally {
            setLoading(false);
        }
    };

    const cancelBooking = async (bookingId: number) => {
        if (!currentUser) return;
        setLoading(true);
        try {
            await fetchApi(`/bookings/${bookingId}`, { method: 'DELETE' });
            await handleFetchData(currentUser);
        } catch (error) {
            alert((error as Error).message);
        } finally {
            setLoading(false);
        }
    };

    const subscribeToCourse = async ({ courseId, planType, startOption, startDate }: { courseId: number; planType: 'monthly' | 'quarterly' | 'annual'; startOption: 'current_month' | 'next_month'; startDate?: string }) => {
        if (!currentUser) return;
        setLoading(true);
        try {
            await fetchApi('/subscriptions', {
                method: 'POST',
                body: JSON.stringify({
                    clientId: currentUser.id,
                    courseId,
                    planType,
                    startOption,
                    startDate,
                }),
            });
            await handleFetchData(currentUser);
            alert('Successfully subscribed!');
        } catch (error) {
            alert((error as Error).message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <AppContext.Provider
            value={{
                currentUser,
                loading,
                login,
                logout,
                registerClient,
                users,
                courses,
                teachers,
                bookings,
                subscriptions,
                clients,
                updateUser,
                resetUserPassword,
                addUser,
                addCourse,
                updateCourse,
                updateTeacher,
                setTeacherAvailability,
                updateTeacherProfile,
                bookLesson,
                cancelBooking,
                subscribeToCourse,
            }}
        >
            {children}
        </AppContext.Provider>
    );
};

export const useAppContext = () => {
    const context = useContext(AppContext);
    if (context === undefined) {
        throw new Error('useAppContext must be used within an AppProvider');
    }
    return context;
};
