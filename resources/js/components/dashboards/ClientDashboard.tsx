import React, { useEffect, useState } from 'react';
import { useAppContext } from '../../context/AppContext';
import { getPoseOfTheDay } from '../../services/geminiService';
import { Pose, Teacher, Course, Booking } from '../../types';
import Calendar from '../Calendar';
import Modal from '../Modal';

const PoseOfTheDay: React.FC = () => {
  const [pose, setPose] = useState<Pose | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchPose = async () => {
      setLoading(true);
      const poseJsonString = await getPoseOfTheDay();
      try {
        const parsedPose = JSON.parse(poseJsonString);
        setPose(parsedPose);
      } catch (e) {
         console.error("Failed to parse pose JSON", e);
         setPose({name: "Error", instructions: "Could not load pose.", benefits: "Please try again."});
      }
      setLoading(false);
    };
    fetchPose();
  }, []);

  return (
    <div className="bg-gradient-to-br from-teal-50 to-emerald-50 p-6 rounded-lg shadow-sm border border-stone-200">
      <h4 className="text-xl font-semibold text-teal-800 mb-3">🧘 Pose of the Day</h4>
      {loading ? (
        <div className="animate-pulse space-y-2">
          <div className="h-6 bg-stone-200 rounded w-1/3"></div>
          <div className="h-4 bg-stone-200 rounded w-full"></div>
          <div className="h-4 bg-stone-200 rounded w-2/3"></div>
        </div>
      ) : pose && (
        <div>
          <h5 className="font-bold text-lg text-stone-700">{pose.name}</h5>
          <p className="text-stone-600 mt-2 text-sm"><strong>Instructions:</strong> {pose.instructions}</p>
          <p className="text-stone-600 mt-1 text-sm"><strong>Benefits:</strong> {pose.benefits}</p>
        </div>
      )}
    </div>
  );
};


const ClientDashboard: React.FC = () => {
    const { courses, teachers, currentUser, bookings, cancelBooking, subscriptions, subscribeToCourse, toggleAutoRenew, loading } = useAppContext();
    const [selectedTeacher, setSelectedTeacher] = useState<Teacher | null>(null);
    const [viewingCourse, setViewingCourse] = useState<Course | null>(null);
    const today = new Date();
    const todayIso = today.toISOString().slice(0, 10);
    const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    const endOfMonthIso = endOfMonth.toISOString().slice(0, 10);
    const nextMonthStart = new Date(today.getFullYear(), today.getMonth() + 1, 1);
    const nextMonthStartIso = nextMonthStart.toISOString().slice(0, 10);
    const nextMonthLabel = nextMonthStart.toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
    const [subscriptionStartOption, setSubscriptionStartOption] = useState<'current_month' | 'next_month'>('current_month');
    const [subscriptionStartDate, setSubscriptionStartDate] = useState<string>(todayIso);
    const [subscriptionPreview, setSubscriptionPreview] = useState<number>(0);

    const calculateSubscriptionAmount = (price: number, option: 'current_month' | 'next_month', startDate: string, planType: 'monthly' | 'quarterly' | 'annual' = 'monthly'): number => {
        const basePrice = Number(price) || 0;
        if (option === 'next_month' || planType !== 'monthly') {
            return Number(basePrice.toFixed(2));
        }

        const start = startDate ? new Date(`${startDate}T00:00:00`) : new Date(`${todayIso}T00:00:00`);
        const totalDays = new Date(start.getFullYear(), start.getMonth() + 1, 0).getDate();
        const startDay = start.getDate();
        const remainingDays = Math.max(totalDays - startDay + 1, 0);
        const ratio = totalDays > 0 ? remainingDays / totalDays : 1;
        const prorated = Number((basePrice * ratio).toFixed(2));

        if (basePrice > 0 && prorated < 0.01) {
            return 0.01;
        }

        return prorated;
    };

    const handleStartOptionChange = (option: 'current_month' | 'next_month') => {
        setSubscriptionStartOption(option);
        if (option === 'current_month') {
            setSubscriptionStartDate(todayIso);
        } else {
            setSubscriptionStartDate(nextMonthStartIso);
        }
    };

    useEffect(() => {
        if (viewingCourse) {
            handleStartOptionChange('current_month');
        }
    }, [viewingCourse]);

    useEffect(() => {
        if (viewingCourse) {
            const plan = viewingCourse.availablePlans?.[0] ?? null;
            setSubscriptionPreview(
                calculateSubscriptionAmount(
                    Number(plan?.amount ?? viewingCourse.price ?? 0),
                    subscriptionStartOption,
                    subscriptionStartDate,
                    plan?.type ?? 'monthly'
                )
            );
        }
    }, [subscriptionStartOption, subscriptionStartDate, viewingCourse]);

    const handleSubscriptionSubmit = async () => {
        if (!viewingCourse) return;
        const startOption = subscriptionStartOption;
        const plan = viewingCourse.availablePlans?.[0];
        const planType = plan?.type ?? 'monthly';
        const startDatePayload = startOption === 'current_month' ? subscriptionStartDate : undefined;
        try {
            await subscribeToCourse({
                courseId: viewingCourse.id,
                planType,
                startOption,
                startDate: startDatePayload,
            });
            setViewingCourse(null);
        } catch (error) {
            console.error(error);
        }
    };

    const myBookings = bookings
        .filter(b => b.clientId === currentUser.id)
        .sort((a, b) => new Date(`${a.slot.date}T${a.slot.time}`).getTime() - new Date(`${b.slot.date}T${b.slot.time}`).getTime());

    const mySubscriptions = subscriptions.filter(sub => sub.clientId === currentUser.id);

    const upcomingLessons = myBookings.filter(b => new Date(`${b.slot.date}T${b.slot.time}`) > new Date());

    const handleCancelBooking = (booking: Booking) => {
        const teacher = teachers.find(t => t.id === booking.teacherId);
        const lessonDateTime = new Date(`${booking.slot.date}T${booking.slot.time}`);
        const dateString = lessonDateTime.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
        const timeString = booking.slot.time;

        if (window.confirm(`Are you sure you want to cancel your lesson with ${teacher?.name} on ${dateString} at ${timeString}? This action cannot be undone.`)) {
            cancelBooking(booking.id);
        }
    };
    
    const handleBookingSuccess = () => {
        setSelectedTeacher(null); // Close the modal
        alert('Lesson booked successfully!');
    };

    const isCourseSubscribed = (courseId: number) => mySubscriptions.some(s => s.courseId === courseId);


    return (
        <div className="space-y-12">
            <PoseOfTheDay />

            <div>
                <h3 className="text-2xl font-semibold text-stone-800 mb-4">My Subscribed Courses</h3>
                <div className="bg-white p-4 rounded-lg shadow-sm border border-stone-200 space-y-4">
                    {mySubscriptions.length > 0 ? (
                        mySubscriptions.map(sub => {
                            const course = courses.find(c => c.id === sub.courseId);
                            if (!course) return null;
                            const teacher = teachers.find(t => t.id === course.teacherId);
                            const startLabel = sub.startDateDisplay || sub.startDate;
                            return (
                                <div key={sub.id} className="p-4 rounded-lg bg-stone-50 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                                    <div>
                                        <h4 className="font-bold text-teal-700">{course.title}</h4>
                                        <p className="text-sm text-stone-500">with {teacher?.name}</p>
                                        <p className="text-sm text-stone-600 mt-1">{course.schedule.map(s => `${s.day} @ ${s.time}`).join(', ')}</p>
                                        {startLabel && (
                                            <p className="text-xs text-stone-500 mt-1">Start date: {startLabel}</p>
                                        )}
                                    </div>
                                    <div className="flex items-center gap-4 sm:gap-6">
                                        <span className="font-semibold text-lg text-stone-800">
                                            €{(course.availablePlans?.[0]?.amount ?? course.price ?? 0).toFixed(2)}
                                        </span>
                                        <label htmlFor={`auto-renew-${sub.id}`} className="flex items-center cursor-pointer">
                                            <span className="mr-3 text-sm text-stone-600">Auto-Renew</span>
                                            <div className="relative">
                                                <input 
                                                    type="checkbox" 
                                                    id={`auto-renew-${sub.id}`} 
                                                    className="sr-only" 
                                                    checked={sub.autoRenew}
                                                    onChange={() => toggleAutoRenew(sub.id)}
                                                />
                                                <div className={`block w-10 h-6 rounded-full ${sub.autoRenew ? 'bg-teal-500' : 'bg-stone-300'}`}></div>
                                                <div className={`dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform ${sub.autoRenew ? 'transform translate-x-4' : ''}`}></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            );
                        })
                    ) : (
                        <p className="text-stone-500 italic p-4 text-center">You are not subscribed to any courses.</p>
                    )}
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div className="lg:col-span-1">
                    <h3 className="text-2xl font-semibold text-stone-800 mb-4">🔔 Next Lessons</h3>
                    <div className="bg-white p-6 rounded-lg shadow-sm border border-stone-200 space-y-4">
                        {upcomingLessons.length > 0 ? (
                            upcomingLessons.slice(0, 3).map(booking => {
                                const teacher = teachers.find(t => t.id === booking.teacherId);
                                return (
                                    <div key={booking.id} className="p-3 bg-teal-50 border-l-4 border-teal-500 rounded-r-md">
                                        <p className="font-semibold text-teal-800">
                                            {new Date(booking.slot.date).toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' })} at {booking.slot.time}
                                        </p>
                                        <p className="text-stone-600 text-sm">with {teacher?.name}</p>
                                    </div>
                                );
                            })
                        ) : (
                            <p className="text-stone-500 italic">You have no upcoming lessons.</p>
                        )}
                    </div>
                </div>

                <div className="lg:col-span-2">
                    <h3 className="text-2xl font-semibold text-stone-800 mb-4">My Booked Lessons</h3>
                    <div className="bg-white p-4 rounded-lg shadow-sm border border-stone-200 space-y-3 max-h-96 overflow-y-auto">
                        {myBookings.length > 0 ? (
                            myBookings.map(booking => {
                                const teacher = teachers.find(t => t.id === booking.teacherId);
                                const lessonDateTime = new Date(`${booking.slot.date}T${booking.slot.time}`);
                                const hoursUntilLesson = (lessonDateTime.getTime() - new Date().getTime()) / (1000 * 60 * 60);
                                const canCancel = hoursUntilLesson > 24;
                                const isPast = hoursUntilLesson < 0;

                                return (
                                    <div key={booking.id} className={`p-4 rounded-lg flex justify-between items-center ${isPast ? 'bg-stone-100 opacity-60' : 'bg-stone-50'}`}>
                                        <div>
                                            <p className={`font-bold ${isPast ? 'text-stone-600' : 'text-stone-800'}`}>
                                                Lesson with {teacher?.name}
                                            </p>
                                            <p className="text-sm text-stone-500">
                                                {lessonDateTime.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' })} @ {booking.slot.time}
                                            </p>
                                        </div>
                                        <div>
                                            {isPast ? (
                                                <span className="text-xs font-bold text-stone-500 uppercase">Completed</span>
                                            ) : (
                                                canCancel ? (
                                                    <button
                                                        onClick={() => handleCancelBooking(booking)}
                                                        className="bg-red-500 text-white font-semibold py-1 px-3 text-sm rounded-lg hover:bg-red-600 transition-colors"
                                                    >
                                                        Cancel
                                                    </button>
                                                ) : (
                                                    <button
                                                        className="bg-stone-300 text-stone-600 font-semibold py-1 px-3 text-sm rounded-lg cursor-not-allowed"
                                                        title="Cannot cancel within 24 hours of the lesson."
                                                        disabled
                                                    >
                                                        Cancel
                                                    </button>
                                                )
                                            )}
                                        </div>
                                    </div>
                                );
                            })
                        ) : (
                            <p className="text-stone-500 italic p-4">You have not booked any private lessons yet.</p>
                        )}
                    </div>
                </div>
            </div>
            
            <div>
                <h3 className="text-2xl font-semibold text-stone-800 mb-4">Available Monthly Courses</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {courses.map(course => (
                        <button key={course.id} onClick={() => setViewingCourse(course)} className="bg-white p-6 rounded-lg shadow-sm border border-stone-200 flex flex-col text-left hover:shadow-md hover:border-teal-200 transition-all duration-300">
                            <div>
                                <h4 className="font-bold text-xl text-teal-700">{course.title}</h4>
                                <p className="text-stone-500 text-sm mb-2">with {teachers.find(t => t.id === course.teacherId)?.name}</p>
                                <p className="text-stone-600 flex-grow mb-4 text-sm">{course.description}</p>
                                {(course.startDateDisplay || course.startDate || course.endDateDisplay || course.endDate) && (
                                    <p className="text-xs text-stone-500 mb-3">
                                        Periodo: {(course.startDateDisplay || course.startDate || '—')} → {(course.endDateDisplay || course.endDate || '—')}
                                    </p>
                                )}
                            </div>
                            
                             {course.gallery && course.gallery.length > 0 && (
                                <div className="mb-4">
                                    <div className="grid grid-cols-3 gap-2 mt-2">
                                        {course.gallery.slice(0, 3).map((url, index) => (
                                            <img key={index} src={url} alt={`Course gallery image ${index + 1}`} className="w-full h-20 object-cover rounded-md" />
                                        ))}
                                    </div>
                                </div>
                            )}
                            
                            <div className="mt-auto">
                                <p className="text-sm text-teal-800 font-medium mb-4">{course.schedule.map(s => `${s.day} @ ${s.time}`).join(', ')}</p>
                                <div className="flex justify-between items-center">
                                        <span className="font-semibold text-lg text-stone-800">
                                            €{(course.availablePlans?.[0]?.amount ?? course.price ?? 0).toFixed(2)}
                                        </span>
                                    <span className="font-semibold py-2 px-4 rounded-lg transition-colors bg-teal-600 text-white">
                                        View Details
                                    </span>
                                </div>
                            </div>
                        </button>
                    ))}
                </div>
            </div>

            <div>
                <h3 className="text-2xl font-semibold text-stone-800 mb-4">Our Teachers &amp; Private Lessons</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {teachers.map(teacher => (
                        <div key={teacher.id} className="bg-white p-6 rounded-lg shadow-sm border border-stone-200 flex flex-col sm:flex-row items-start gap-4">
                            <img src={teacher.profilePictureUrl} alt={teacher.name} className="w-20 h-20 rounded-full object-cover flex-shrink-0"/>
                            <div className="flex-grow">
                                <h4 className="font-bold text-xl text-stone-800">{teacher.name}</h4>
                                <p className="text-stone-600 text-sm mt-1">{teacher.bio}</p>
                                <div className="mt-2 flex flex-wrap gap-2">
                                    {teacher.specializations.map(spec => (
                                        <span key={spec} className="bg-teal-100 text-teal-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{spec}</span>
                                    ))}
                                </div>
                            </div>
                            <button onClick={() => setSelectedTeacher(teacher)} className="bg-emerald-500 mt-2 sm:mt-0 text-white font-semibold py-2 px-4 rounded-lg hover:bg-emerald-600 transition-colors whitespace-nowrap self-start sm:self-center">View Calendar</button>
                        </div>
                    ))}
                </div>
            </div>

            {selectedTeacher && (
                <Modal title={`Book a lesson with ${selectedTeacher.name}`} onClose={() => setSelectedTeacher(null)}>
                   <Calendar teacher={selectedTeacher} viewMode="client" onBookingSuccess={handleBookingSuccess} />
                </Modal>
            )}

            {viewingCourse && (() => {
                const teacher = teachers.find(t => t.id === viewingCourse.teacherId);
                const isSubscribed = isCourseSubscribed(viewingCourse.id);
                const isCurrentOption = subscriptionStartOption === 'current_month';
                const startDateValid = !isCurrentOption || (
                    subscriptionStartDate >= todayIso && subscriptionStartDate <= endOfMonthIso
                );
                const subscribeButtonDisabled = isSubscribed || !startDateValid || loading;
                const previewLabel = `$${subscriptionPreview.toFixed(2)}`;

                return (
                    <Modal title={viewingCourse.title} onClose={() => setViewingCourse(null)}>
                        <div className="space-y-6">
                            {viewingCourse.gallery && viewingCourse.gallery.length > 0 && (
                                <div>
                                    <img src={viewingCourse.gallery[0]} alt={`${viewingCourse.title} main image`} className="w-full h-64 object-cover rounded-lg mb-2" />
                                    <div className="grid grid-cols-5 gap-2">
                                        {viewingCourse.gallery.map((url, index) => (
                                            <img key={index} src={url} alt={`Course gallery image ${index + 1}`} className="w-full h-24 object-cover rounded-md" />
                                        ))}
                                    </div>
                                </div>
                            )}

                            <div>
                                <h4 className="font-semibold text-lg text-stone-700">Course Description</h4>
                                <p className="text-stone-600 mt-1">{viewingCourse.description}</p>
                            </div>
                            {(viewingCourse.startDateDisplay || viewingCourse.startDate || viewingCourse.endDateDisplay || viewingCourse.endDate) && (
                                <div>
                                    <h4 className="font-semibold text-lg text-stone-700">Periodo</h4>
                                    <p className="text-stone-600 mt-1">
                                        {viewingCourse.startDateDisplay || viewingCourse.startDate || '—'} → {viewingCourse.endDateDisplay || viewingCourse.endDate || '—'}
                                    </p>
                                </div>
                            )}
                            <div>
                                <h4 className="font-semibold text-lg text-stone-700">Speciality Focus</h4>
                                <p className="text-stone-600 mt-1">{viewingCourse.specialityDescription}</p>
                            </div>

                            <div className="bg-stone-50 border border-stone-200 rounded-lg p-4 space-y-3">
                                <h4 className="font-semibold text-stone-700">Scegli l'inizio della tua iscrizione</h4>
                                <p className="text-xs text-stone-500">Se inizi questo mese l'importo viene calcolato in base ai giorni rimanenti.</p>
                                <div className="space-y-3">
                                    <label className="flex items-start gap-3 text-sm text-stone-600">
                                        <input
                                            type="radio"
                                            name="subscription-start-option"
                                            value="current_month"
                                            checked={subscriptionStartOption === 'current_month'}
                                            onChange={() => handleStartOptionChange('current_month')}
                                            className="mt-1 h-4 w-4 text-teal-600 border-stone-300 focus:ring-teal-500"
                                        />
                                        <span>
                                            <span className="font-semibold text-stone-700">Inizia questo mese</span>
                                            <span className="block text-xs text-stone-500">Costo proporzionale ai giorni rimanenti nel mese.</span>
                                        </span>
                                    </label>
                                    {subscriptionStartOption === 'current_month' && (
                                        <div className="ml-7 space-y-2">
                                            <div className="flex items-center gap-2">
                                                <input
                                                    type="date"
                                                    className="border border-stone-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                                                    value={subscriptionStartDate}
                                                    min={todayIso}
                                                    max={endOfMonthIso}
                                                    onChange={(e) => setSubscriptionStartDate(e.target.value)}
                                                />
                                                <span className="text-xs text-stone-500">Disponibile fino al {endOfMonth.toLocaleDateString('it-IT')}</span>
                                            </div>
                                            {!startDateValid && (
                                                <p className="text-xs text-rose-500">Seleziona una data valida nel mese corrente.</p>
                                            )}
                                        </div>
                                    )}
                                    <label className="flex items-start gap-3 text-sm text-stone-600">
                                        <input
                                            type="radio"
                                            name="subscription-start-option"
                                            value="next_month"
                                            checked={subscriptionStartOption === 'next_month'}
                                            onChange={() => handleStartOptionChange('next_month')}
                                            className="mt-1 h-4 w-4 text-teal-600 border-stone-300 focus:ring-teal-500"
                                        />
                                        <span>
                                            <span className="font-semibold text-stone-700">Inizia dal prossimo mese</span>
                                            <span className="block text-xs text-stone-500">La tua prima lezione inizierà il {nextMonthLabel}. Importo intero.</span>
                                        </span>
                                    </label>
                                </div>
                                <div className="rounded-lg bg-white border border-stone-200 px-4 py-3 text-sm flex items-center justify-between">
                                    <span className="text-stone-600">Importo dovuto al momento dell'iscrizione</span>
                                    <span className="font-semibold text-teal-700">{previewLabel}</span>
                                </div>
                            </div>

                            {teacher && (
                                <div className="p-4 bg-stone-50 rounded-lg border">
                                    <h4 className="font-semibold text-lg text-stone-700 mb-2">Your Instructor</h4>
                                    <div className="flex items-start gap-4">
                                        <img src={teacher.profilePictureUrl} alt={teacher.name} className="w-16 h-16 rounded-full object-cover flex-shrink-0" />
                                        <div>
                                            <h5 className="font-bold text-stone-800">{teacher.name}</h5>
                                            <p className="text-stone-600 text-sm mt-1">{teacher.bio}</p>
                                        </div>
                                    </div>
                                </div>
                            )}

                            <div className="border-t pt-4">
                                <p className="text-lg text-teal-800 font-medium mb-4"><strong>Schedule:</strong> {viewingCourse.schedule.map(s => `${s.day} @ ${s.time}`).join(', ')}</p>
                                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-stone-100 p-4 rounded-lg">
                                    <span className="font-bold text-2xl text-stone-800">
                                        ${viewingCourse.price}<span className="text-base font-normal text-stone-600">/month</span>
                                    </span>
                                    <button
                                        onClick={handleSubscriptionSubmit}
                                        disabled={subscribeButtonDisabled}
                                        className={`font-semibold py-3 px-6 rounded-lg text-lg transition-colors ${
                                            subscribeButtonDisabled
                                                ? 'bg-stone-300 text-stone-500 cursor-not-allowed'
                                                : 'bg-teal-600 text-white hover:bg-teal-700'
                                        }`}
                                    >
                                        {isSubscribed ? 'Already subscribed' : (loading ? 'Processing…' : `Conferma iscrizione (${previewLabel})`)}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Modal>
                );
            })()}

        </div>
    );
};

export default ClientDashboard;
