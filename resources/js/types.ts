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

export interface CoursePlan {
    type: 'monthly' | 'quarterly' | 'annual';
    label: string;
    amount: number;
    amount_formatted?: string;
    months: number;
}

export interface Course {
    id: number;
    title: string;
    description: string;
    teacherId: number;
    teacherName?: string;
    schedule: ScheduleSlot[];
    price: number;
    monthlyPrice?: number | null;
    quarterlyPrice?: number | null;
    annualPrice?: number | null;
    specialityDescription: string;
    gallery: string[];
    startDate: string;
    endDate: string;
    startDateDisplay?: string;
    endDateDisplay?: string;
    availablePlans?: CoursePlan[];
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
    autoRenew?: boolean;
    status?: 'active' | 'cancelled';
    cancelledAt?: string;
    cancelledAtDisplay?: string;
    startDate?: string;
    startDateDisplay?: string;
    endDate?: string;
    endDateDisplay?: string;
    planType?: CoursePlan['type'];
    planLabel?: string;
    planAmount?: number;
    course?: Pick<Course, 'id' | 'title' | 'price' | 'monthlyPrice' | 'quarterlyPrice' | 'annualPrice'>;
}
