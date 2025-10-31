
import React, { useState, useMemo, useEffect } from 'react';
import { Teacher, AvailabilitySlot, User } from '../types';
import { useAppContext } from '../context/AppContext';
import Modal from './Modal';

interface CalendarProps {
  teacher: Teacher;
  viewMode: 'teacher' | 'client';
  onBookingSuccess?: () => void;
}

const TeacherCalendarLegend: React.FC = () => (
    <div className="mt-4 flex flex-wrap justify-center items-center gap-x-6 gap-y-2 text-sm text-stone-600 border-t pt-4">
        <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded bg-stone-100 border"></div>
            <span>Unavailable</span>
        </div>
        <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded bg-blue-500"></div>
            <span>Available (Saved)</span>
        </div>
        <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded bg-green-500"></div>
            <span>Pending Add</span>
        </div>
        <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded bg-rose-200 border"></div>
            <span>Pending Remove</span>
        </div>
        <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded bg-red-600"></div>
            <span>Booked</span>
        </div>
    </div>
);

const ClientCalendarLegend: React.FC = () => (
    <div className="mt-4 flex flex-wrap justify-center items-center gap-x-6 gap-y-2 text-sm text-stone-600 border-t pt-4">
        <div className="flex items-center gap-2" title="Slots available for booking">
            <div className="w-4 h-4 rounded bg-emerald-200 border"></div>
            <span>Available</span>
        </div>
        <div className="flex items-center gap-2" title="The slot you have currently selected">
            <div className="w-4 h-4 rounded bg-blue-500 ring-2 ring-blue-300"></div>
            <span>Selected</span>
        </div>
        <div className="flex items-center gap-2" title="A lesson you have already booked">
            <div className="w-4 h-4 rounded bg-teal-500"></div>
            <span>My Lesson</span>
        </div>
        <div className="flex items-center gap-2" title="A slot that has been booked by someone else">
            <div className="w-4 h-4 rounded bg-red-200 border"></div>
            <span>Booked</span>
        </div>
         <div className="flex items-center gap-2" title="You cannot book another lesson on a day you already have one scheduled">
            <div className="w-4 h-4 rounded bg-emerald-200 border opacity-50"></div>
            <span>Day Unavailable</span>
        </div>
    </div>
);


const Calendar: React.FC<CalendarProps> = ({ teacher, viewMode, onBookingSuccess }) => {
    const { setTeacherAvailability, bookLesson, currentUser, bookings, clients } = useAppContext();
    const [currentDate, setCurrentDate] = useState(new Date());

    // Teacher state
    const [pendingAvailability, setPendingAvailability] = useState<AvailabilitySlot[]>(teacher.availability);
    const [viewingClientProfile, setViewingClientProfile] = useState<User | null>(null);
    
    // Client state
    const [selectedSlot, setSelectedSlot] = useState<AvailabilitySlot | null>(null);

    // Helper function to format date to 'YYYY-MM-DD' without timezone issues.
    const toYyyyMmDd = (d: Date): string => {
        const year = d.getFullYear();
        const month = (d.getMonth() + 1).toString().padStart(2, '0');
        const day = d.getDate().toString().padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    // Sync pending state with prop changes to avoid stale state.
    useEffect(() => {
        setPendingAvailability(teacher.availability);
    }, [teacher.availability]);

    const isDirty = useMemo(() => {
        const originalSlots = new Set(teacher.availability.filter(s => !s.isBooked).map(s => `${s.date}-${s.time}`));
        const pendingSlots = new Set(pendingAvailability.filter(s => !s.isBooked).map(s => `${s.date}-${s.time}`));

        if (originalSlots.size !== pendingSlots.size) return true;
        for (const slot of pendingSlots) {
            if (!originalSlots.has(slot)) return true;
        }
        return false;
    }, [teacher.availability, pendingAvailability]);

    const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const timeSlots = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];

    const startOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
    const endOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
    const startDate = new Date(startOfMonth);
    startDate.setDate(startDate.getDate() - startOfMonth.getDay());

    const dates = [];
    let date = new Date(startDate);
    while (date <= endOfMonth || dates.length % 7 !== 0) {
        dates.push(new Date(date));
        date.setDate(date.getDate() + 1);
        if (dates.length > 42) break; // Safety break
    }

    const handleTeacherSlotClick = (date: Date, time: string) => {
        const dateString = toYyyyMmDd(date);
        setPendingAvailability(prev => {
            const existingSlotIndex = prev.findIndex(s => s.date === dateString && s.time === time);
            if (existingSlotIndex > -1) {
                // Check if the slot is booked before allowing removal
                if (prev[existingSlotIndex].isBooked) {
                    alert("You cannot remove an availability slot that has already been booked by a client.");
                    return prev;
                }
                return prev.filter((_, index) => index !== existingSlotIndex);
            } else {
                const newSlot: AvailabilitySlot = { date: dateString, time, isBooked: false };
                return [...prev, newSlot];
            }
        });
    };
    
    const handleClientSlotClick = (date: Date, time: string) => {
        const dateString = toYyyyMmDd(date);
        const clickedSlot = { date: dateString, time, isBooked: false };
        
        if (selectedSlot && selectedSlot.date === dateString && selectedSlot.time === time) {
            setSelectedSlot(null); // Deselect if clicked again
        } else {
            setSelectedSlot(clickedSlot);
        }
    };

    const handleConfirmTeacherChanges = async () => {
        await setTeacherAvailability(teacher.id, pendingAvailability);
        alert('Availability updated!');
    };

    const handleResetTeacherChanges = () => {
        setPendingAvailability(teacher.availability);
    };

    const handleViewClientProfile = (clientId?: number) => {
        if (!clientId) return;
        const client = clients.find(c => c.id === clientId);
        if (client) {
            setViewingClientProfile(client);
        } else {
            alert("Could not find client details. Data may be out of sync.");
        }
    };

    const handleConfirmBooking = async () => {
        if (!selectedSlot) return;
        if (window.confirm(`Book a lesson with ${teacher.name} on ${new Date(selectedSlot.date).toLocaleDateString()} at ${selectedSlot.time}?`)) {
            const success = await bookLesson(teacher.id, selectedSlot);
            if (success) {
                setSelectedSlot(null); // Clear selection on successful booking
                onBookingSuccess?.();
            }
        }
    };
    
    const changeMonth = (offset: number) => {
        setCurrentDate(prev => new Date(prev.getFullYear(), prev.getMonth() + offset, 1));
    };

    const clientHasBookingOnDate = (date: Date) => {
        const dateString = toYyyyMmDd(date);
        return bookings.some(b => b.clientId === currentUser.id && b.teacherId === teacher.id && b.slot.date === dateString);
    }

    return (
        <div>
            <div className="flex justify-between items-center mb-4">
                <button onClick={() => changeMonth(-1)} className="px-3 py-1 bg-stone-200 rounded-md hover:bg-stone-300">&lt;</button>
                <h3 className="text-xl font-semibold">{currentDate.toLocaleString('default', { month: 'long', year: 'numeric' })}</h3>
                <button onClick={() => changeMonth(1)} className="px-3 py-1 bg-stone-200 rounded-md hover:bg-stone-300">&gt;</button>
            </div>
            <div className="grid grid-cols-7 gap-1 text-center font-semibold text-stone-600">
                {daysOfWeek.map(day => <div key={day}>{day}</div>)}
            </div>
            <div className="grid grid-cols-7 gap-1 mt-2">
                {dates.map((d, i) => (
                    <div key={i} className={`p-2 border rounded-md min-h-[120px] ${d.getMonth() !== currentDate.getMonth() ? 'bg-stone-50 text-stone-400' : 'bg-white'}`}>
                        <div className={`font-bold ${toYyyyMmDd(new Date()) === toYyyyMmDd(d) ? 'text-teal-600' : ''}`}>{d.getDate()}</div>
                        <div className="space-y-1 mt-1 text-xs">
                           {timeSlots.map(time => {
                               const dateString = toYyyyMmDd(d);
                               
                               if (viewMode === 'teacher') {
                                   const originalSlot = teacher.availability.find(s => s.date === dateString && s.time === time);
                                   const pendingSlot = pendingAvailability.find(s => s.date === dateString && s.time === time);

                                   // State 1: Booked
                                   if (originalSlot?.isBooked) {
                                       return (
                                           <button
                                              key={time}
                                              onClick={() => handleViewClientProfile(originalSlot.bookedBy)}
                                              className="w-full text-center rounded p-1 bg-red-600 text-white cursor-pointer hover:bg-red-700 text-xs leading-tight font-semibold"
                                              title={`Booked by ${originalSlot.bookedByName}. Click to view profile.`}
                                            >
                                              {originalSlot.bookedByName?.split(' ')[0]}
                                           </button>
                                       );
                                   }
                                   
                                   let buttonClass: string;
                                   if (pendingSlot && !originalSlot) {
                                       // Pending Add: User has selected a new available slot.
                                       buttonClass = 'bg-green-500 text-white';
                                   } else if (!pendingSlot && originalSlot && !originalSlot.isBooked) {
                                       // Pending Remove: User has deselected a previously saved slot.
                                       buttonClass = 'bg-rose-200 text-rose-800 border border-rose-300';
                                   } else if (pendingSlot && originalSlot && !originalSlot.isBooked) {
                                       // Available (Saved): The slot is saved and still selected.
                                       buttonClass = 'bg-blue-500 text-white';
                                   } else {
                                       // Unavailable: The default state for an unbooked, unselected slot.
                                       buttonClass = 'bg-stone-100 text-stone-500 hover:bg-stone-200';
                                   }


                                   return (
                                       <button 
                                           key={time} 
                                           onClick={() => handleTeacherSlotClick(d, time)} 
                                           className={`w-full text-center rounded p-1 transition-colors text-xs leading-tight ${buttonClass}`}>
                                           {time}
                                       </button>
                                   );
                               }
                               
                               if (viewMode === 'client') {
                                    const slotInTeacherAvailability = teacher.availability.find(s => s.date === dateString && s.time === time);
                                    if(slotInTeacherAvailability) {
                                        if (slotInTeacherAvailability.isBooked) {
                                            const isMyBooking = slotInTeacherAvailability.bookedBy === currentUser.id;
                                            return <div key={time} className={`w-full text-center rounded p-1 text-xs leading-tight ${isMyBooking ? 'bg-teal-500 text-white font-semibold' : 'bg-red-200 text-red-700 cursor-not-allowed'}`}>{isMyBooking ? 'My lesson' : 'Booked'}</div>;
                                        }
                                        const isSelected = selectedSlot?.date === dateString && selectedSlot?.time === time;
                                        const hasBookingOnThisDay = clientHasBookingOnDate(d);
                                        const canSelect = !hasBookingOnThisDay || isSelected;

                                        return (
                                            <button
                                                key={time}
                                                onClick={() => handleClientSlotClick(d, time)}
                                                disabled={!canSelect}
                                                className={`w-full text-center rounded p-1 transition-all duration-200 text-xs leading-tight ${
                                                    isSelected
                                                        ? 'bg-blue-500 text-white font-bold ring-2 ring-offset-1 ring-blue-500'
                                                        : 'bg-emerald-200 text-emerald-800 hover:bg-emerald-300'
                                                } ${
                                                    !canSelect
                                                        ? 'opacity-50 cursor-not-allowed'
                                                        : ''
                                                }`}
                                                title={!canSelect && !isSelected ? "You already have a lesson scheduled on this day." : `Book at ${time}`}
                                            >
                                                {time}
                                            </button>
                                        );
                                    }
                                    return <div key={time} className="w-full text-center rounded p-1 bg-stone-100 text-stone-400 cursor-not-allowed opacity-50 text-xs leading-tight">{time}</div>;
                               }
                               return null;
                           })}
                        </div>
                    </div>
                ))}
            </div>

            {viewMode === 'teacher' && <TeacherCalendarLegend />}
            {viewMode === 'client' && <ClientCalendarLegend />}
            
            {viewMode === 'teacher' && isDirty && (
                <div className="mt-4 flex justify-end gap-2 p-4 bg-stone-100 rounded-md">
                    <button onClick={handleResetTeacherChanges} className="bg-stone-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-stone-600 transition-colors">Reset</button>
                    <button onClick={handleConfirmTeacherChanges} className="bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-teal-700 transition-colors">Confirm Changes</button>
                </div>
            )}

            {viewMode === 'client' && (
                <div className="mt-4 flex justify-end gap-2 p-4 bg-stone-100 rounded-md">
                     <button onClick={handleConfirmBooking} disabled={!selectedSlot} className="bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-teal-700 transition-colors disabled:bg-stone-400 disabled:cursor-not-allowed">
                        {selectedSlot ? `Book Lesson for ${selectedSlot.time}` : 'Select a time to book'}
                    </button>
                </div>
            )}

            {viewingClientProfile && (
                <Modal title={`Client Profile: ${viewingClientProfile.name}`} onClose={() => setViewingClientProfile(null)}>
                    <div className="space-y-4 text-stone-700">
                        <div className="flex items-center gap-4">
                            <img src={`https://i.pravatar.cc/150?u=${viewingClientProfile.email}`} alt={viewingClientProfile.name} className="w-20 h-20 rounded-full object-cover"/>
                            <div>
                                <p className="text-xl font-bold">{viewingClientProfile.name}</p>
                                <p className="text-stone-500">{viewingClientProfile.email}</p>
                            </div>
                        </div>
                        {viewingClientProfile.telephone && (
                            <div className="pt-4 border-t">
                                <p className="font-semibold mb-2">Contact Information</p>
                                <a 
                                    href={`https://wa.me/${viewingClientProfile.telephone.replace(/\D/g, '')}`} 
                                    target="_blank" 
                                    rel="noopener noreferrer"
                                    className="mt-2 inline-flex items-center gap-2 bg-green-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-600 transition-colors"
                                >
                                    <svg fill="currentColor" viewBox="0 0 24 24" className="w-5 h-5"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.894 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.433-9.89-9.889-9.89-5.452 0-9.887 4.428-9.889 9.891-.001 2.235.666 4.363 1.842 6.066l-1.29 4.721 4.773-1.241z m7.82-5.982c-.378-.19-.888-.38-1.034-.44-.146-.06-.267-.09-.423.18-.195.33-.63.75-.772.89-.142.14-.282.16-.52.06-.237-.09-.994-.37-1.896-1.16-.712-.6-1.197-1.34-1.34-1.59-.143-.25-.06-.39.04-.5.09-.09.2-.24.3-.33.09-.09.12-.17.18-.28.06-.11.03-.21-.02-.32-.05-.11-.47-1.13-.65-1.54-.18-.41-.36-.35-.5-.35-.14 0-.3 0-.46.01-.16.01-.42.06-.64.31-.22.25-.86.85-.86 2.06 0 1.21.88 2.39 1 2.56.12.17 1.73 2.66 4.2 3.72.59.25 1.05.4 1.41.52.59.19 1.13.16 1.56.09.48-.07 1.41-.57 1.61-1.12.2-.55.2-1.02.14-1.12-.06-.1-.22-.16-.47-.25z" /></svg>
                                    Chat on WhatsApp ({viewingClientProfile.telephone})
                                </a>
                            </div>
                        )}
                    </div>
                </Modal>
            )}
        </div>
    );
};

export default Calendar;
