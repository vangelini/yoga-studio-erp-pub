import React, { useState, useEffect } from 'react';
import { useAppContext } from '../../context/AppContext';
import { Booking, User } from '../../types';
import Calendar from '../Calendar';
import Modal from '../Modal';

const TeacherDashboard: React.FC = () => {
    const { currentUser, teachers, bookings, updateTeacherProfile, clients } = useAppContext();
    const teacher = teachers.find(t => t.id === currentUser.id);
    
    const [isEditing, setIsEditing] = useState(false);
    const [editedBio, setEditedBio] = useState('');
    const [editedSpecs, setEditedSpecs] = useState('');
    const [editedPictureUrl, setEditedPictureUrl] = useState('');
    const [selectedClientInfo, setSelectedClientInfo] = useState<{ client: User; booking: Booking } | null>(null);

    // This effect will run whenever the `teacher` object changes.
    // It keeps the form state in sync with the latest teacher data.
    useEffect(() => {
        if (teacher) {
            setEditedBio(teacher.bio);
            setEditedSpecs(teacher.specializations.join(', '));
            setEditedPictureUrl(teacher.profilePictureUrl);
        }
    }, [teacher]);
    
    if (!teacher) {
        return <div>Loading teacher profile... If this persists, please contact an administrator.</div>;
    }
    
    const myBookings = bookings.filter(b => b.teacherId === teacher.id);
    
    const handleSave = async () => {
        const specializationsArray = editedSpecs.split(',').map(s => s.trim()).filter(s => s);
        await updateTeacherProfile(teacher.id, editedBio, specializationsArray, editedPictureUrl);
        setIsEditing(false);
    };

    const handleCancel = () => {
        // Reset state from the definitive teacher object
        setEditedBio(teacher.bio);
        setEditedSpecs(teacher.specializations.join(', '));
        setEditedPictureUrl(teacher.profilePictureUrl);
        setIsEditing(false);
    };

    const handleOpenClientDetails = (booking: Booking) => {
        const client = clients.find(c => c.id === booking.clientId);
        if (client) {
            setSelectedClientInfo({ client, booking });
        } else {
            alert('Client data not available at the moment.');
        }
    };

    const handleCloseClientModal = () => setSelectedClientInfo(null);

    return (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div className="lg:col-span-2">
                <h3 className="text-2xl font-semibold text-stone-800 mb-4">My Availability Calendar</h3>
                <p className="text-stone-600 mb-4">Click on a time slot to add or remove your availability for private lessons.</p>
                <div className="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-stone-200">
                    <Calendar teacher={teacher} viewMode="teacher" />
                </div>
            </div>
            <div className="space-y-8">
                 <div>
                    <h3 className="text-2xl font-semibold text-stone-800 mb-4">My Profile</h3>
                    <div className="bg-white p-6 rounded-lg shadow-sm border border-stone-200 space-y-4">
                        <div className="flex justify-center">
                            <img src={teacher.profilePictureUrl} alt={teacher.name} className="w-24 h-24 rounded-full object-cover ring-4 ring-teal-100" />
                        </div>
                        {!isEditing ? (
                            <>
                                <div>
                                    <label className="font-semibold text-stone-600">Biography</label>
                                    <p className="text-stone-800 mt-1">{teacher.bio}</p>
                                </div>
                                <div>
                                    <label className="font-semibold text-stone-600">Specializations</label>
                                    <div className="mt-2 flex flex-wrap gap-2">
                                        {teacher.specializations.map(spec => (
                                            <span key={spec} className="bg-teal-100 text-teal-800 text-sm font-medium px-3 py-1 rounded-full">{spec}</span>
                                        ))}
                                    </div>
                                </div>
                                <button onClick={() => setIsEditing(true)} className="w-full mt-2 bg-stone-200 text-stone-800 font-semibold py-2 px-4 rounded-lg hover:bg-stone-300 transition-colors">Edit Profile</button>
                            </>
                        ) : (
                            <>
                                <div>
                                    <label htmlFor="pictureUrl" className="font-semibold text-stone-600">Profile Picture URL</label>
                                    <input id="pictureUrl" type="text" value={editedPictureUrl} onChange={(e) => setEditedPictureUrl(e.target.value)} className="w-full mt-1 p-2 border rounded-md" />
                                </div>
                                <div>
                                    <label htmlFor="bio" className="font-semibold text-stone-600">Biography</label>
                                    <textarea id="bio" value={editedBio} onChange={(e) => setEditedBio(e.target.value)} className="w-full mt-1 p-2 border rounded-md h-24" />
                                </div>
                                <div>
                                    <label htmlFor="specializations" className="font-semibold text-stone-600">Specializations</label>
                                    <input id="specializations" type="text" value={editedSpecs} onChange={(e) => setEditedSpecs(e.target.value)} className="w-full mt-1 p-2 border rounded-md" />
                                    <p className="text-xs text-stone-500 mt-1">Enter specializations, separated by commas.</p>
                                </div>
                                <div className="flex gap-2 mt-2">
                                    <button onClick={handleSave} className="flex-1 bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-teal-700 transition-colors">Save Changes</button>
                                    <button onClick={handleCancel} className="flex-1 bg-stone-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-stone-600 transition-colors">Cancel</button>
                                </div>
                            </>
                        )}
                    </div>
                </div>

                <div>
                    <h3 className="text-2xl font-semibold text-stone-800 mb-4">Upcoming Lessons</h3>
                    <div className="bg-white p-4 rounded-lg shadow-sm border border-stone-200 space-y-3">
                        {myBookings.length > 0 ? (
                            myBookings.map(booking => (
                                <div key={booking.id} className="p-3 bg-teal-50 border-l-4 border-teal-500 rounded-r-md">
                                    <p className="font-semibold text-teal-800">{new Date(booking.slot.date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' })}</p>
                                    <p className="text-stone-600">
                                        {booking.slot.time} - with{' '}
                                        <button
                                            type="button"
                                            onClick={() => handleOpenClientDetails(booking)}
                                            className="text-teal-700 underline font-medium hover:text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 rounded"
                                        >
                                            {clients.find(client => client.id === booking.clientId)?.name || `Client #${booking.clientId}`}
                                        </button>
                                    </p>
                                </div>
                            ))
                        ) : (
                            <p className="text-stone-500 italic">No upcoming lessons booked.</p>
                        )}
                    </div>
                </div>
            </div>

            {selectedClientInfo && (() => {
                const { client, booking } = selectedClientInfo;
                const lessonDateTime = new Date(`${booking.slot.date}T${booking.slot.time}`);
                const formattedDate = lessonDateTime.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
                const sanitizedPhone = client.telephone ? client.telephone.replace(/\D/g, '') : '';
                // Prefill a friendly WhatsApp message for quick contact.
                const defaultMessage = encodeURIComponent(
                    `Ciao ${client.name}, sono ${teacher.name}. Ti contatto riguardo la lezione del ${formattedDate} alle ${booking.slot.time}.`
                );
                const whatsappUrl = sanitizedPhone ? `https://wa.me/${sanitizedPhone}?text=${defaultMessage}` : '';

                return (
                    <Modal title={`Client details: ${client.name}`} onClose={handleCloseClientModal}>
                        <div className="space-y-5">
                            <div>
                                <h4 className="text-lg font-semibold text-stone-800">Contact Information</h4>
                                <p className="text-stone-600 mt-1"><span className="font-medium text-stone-700">Email:</span> {client.email}</p>
                                <p className="text-stone-600 mt-1">
                                    <span className="font-medium text-stone-700">Phone:</span>{' '}
                                    {whatsappUrl ? (
                                        <a
                                            href={whatsappUrl}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-teal-700 underline hover:text-teal-900"
                                        >
                                            {client.telephone}
                                        </a>
                                    ) : (
                                        client.telephone || 'Not provided'
                                    )}
                                </p>
                                <p className="text-stone-600 mt-1"><span className="font-medium text-stone-700">Status:</span> {client.status}</p>
                            </div>

                            <div>
                                <h4 className="text-lg font-semibold text-stone-800">Upcoming Lesson</h4>
                                <p className="text-stone-600 mt-1">{formattedDate} at {booking.slot.time}</p>
                            </div>
                        </div>
                    </Modal>
                );
            })()}
        </div>
    );
};

export default TeacherDashboard;
