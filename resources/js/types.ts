export enum Role {
    Admin = 'Admin',
    Teacher = 'Teacher',
    Client = 'Client',
}

export interface User {
    id: number;
    name: string;
    email: string;
    password: string;
    role: Role;
    status: 'active' | 'pending' | 'disabled';
    telephone?: string;
}

export type DayOfWeek = 'Sunday' | 'Monday' | 'Tuesday' | 'Wednesday' | 'Thursday' | 'Friday' | 'Saturday';

export interface ScheduleSlot {
    day: DayOfWeek;
    time: string;
}

export interface Course {
    id: number;
    title: string;
    description: string;
    teacherId: number;
    schedule: ScheduleSlot[];
    price: number;
    specialityDescription: string;
    gallery: string[];
}

export interface Teacher {
    id: number;
    name: string;
    profilePictureUrl: string;
    bio: string;
    specializations: string[];
    availability: AvailabilitySlot[];
}

export interface AvailabilitySlot {
    date: string;
    time: string;
    isBooked: boolean;
    bookedBy?: number;
    bookedByName?: string;
}

export interface Booking {
    id: number;
    clientId: number;
    teacherId: number;
    slot: AvailabilitySlot;
}

export interface Pose {
    name: string;
    instructions: string;
    benefits: string;
}

export interface Subscription {
    id: number;
    clientId: number;
    courseId: number;
    autoRenew: boolean;
}
