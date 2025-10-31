
import React, { useState, useEffect } from 'react';
import { useAppContext } from '../../context/AppContext';
// FIX: Removed unused import for generateCourseDescription
// import { generateCourseDescription } from '../../services/geminiService';
// FIX: Removed 'Omit' from import as it is a built-in TypeScript utility type and does not need to be imported.
import { Course, DayOfWeek, Role, User, Teacher } from '../../types';
import Modal from '../Modal';

const CourseForm: React.FC<{ initialData: Course | Omit<Course, 'id'>, onSave: (course: Course | Omit<Course, 'id'>) => void, onCancel: () => void }> = ({ initialData, onSave, onCancel }) => {
    const { teachers } = useAppContext();
    const [formData, setFormData] = useState(initialData);
    const daysOfWeek: DayOfWeek[] = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    useEffect(() => {
        setFormData(initialData);
    }, [initialData]);

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: name === 'teacherId' || name === 'price' ? Number(value) : value }));
    };

    const handleGalleryChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
        const urls = e.target.value.split(',').map(url => url.trim()).filter(url => url);
        setFormData(prev => ({ ...prev, gallery: urls }));
    };

    const handleScheduleDayChange = (day: DayOfWeek, checked: boolean) => {
        let newSchedule = [...formData.schedule];
        if (checked) {
            if (!newSchedule.find(s => s.day === day)) {
                newSchedule.push({ day, time: '09:00' });
            }
        } else {
            newSchedule = newSchedule.filter(s => s.day !== day);
        }
        setFormData(prev => ({ ...prev, schedule: newSchedule }));
    };

    const handleScheduleTimeChange = (day: DayOfWeek, time: string) => {
        const newSchedule = formData.schedule.map(s => s.day === day ? { ...s, time } : s);
        setFormData(prev => ({ ...prev, schedule: newSchedule }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSave(formData);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div>
                <label className="font-semibold text-stone-600">Title</label>
                <input type="text" name="title" value={formData.title} onChange={handleInputChange} className="w-full mt-1 p-2 border rounded-md" required />
            </div>
            <div>
                <label className="font-semibold text-stone-600">Description</label>
                <textarea name="description" value={formData.description} onChange={handleInputChange} className="w-full mt-1 p-2 border rounded-md h-24" required />
            </div>
            <div>
                <label className="font-semibold text-stone-600">Speciality Description</label>
                <textarea name="specialityDescription" value={formData.specialityDescription} onChange={handleInputChange} className="w-full mt-1 p-2 border rounded-md h-20" required />
            </div>
            <div>
                <label className="font-semibold text-stone-600">Gallery Image URLs (comma-separated)</label>
                <textarea name="gallery" value={formData.gallery.join(', ')} onChange={handleGalleryChange} className="w-full mt-1 p-2 border rounded-md h-20" />
            </div>
            <div className="grid grid-cols-2 gap-4">
                <div>
                    <label className="font-semibold text-stone-600">Teacher</label>
                    <select name="teacherId" value={formData.teacherId} onChange={handleInputChange} className="w-full mt-1 p-2 border rounded-md bg-white" required>
                        {teachers.map(teacher => <option key={teacher.id} value={teacher.id}>{teacher.name}</option>)}
                    </select>
                </div>
                <div>
                    <label className="font-semibold text-stone-600">Price ($)</label>
                    <input type="number" name="price" value={formData.price} onChange={handleInputChange} className="w-full mt-1 p-2 border rounded-md" required />
                </div>
            </div>
            <div>
                <label className="font-semibold text-stone-600">Weekly Schedule</label>
                <div className="mt-2 p-3 border rounded-md space-y-2 bg-stone-50">
                    {daysOfWeek.map(day => {
                        const slot = formData.schedule.find(s => s.day === day);
                        return (
                            <div key={day} className="flex items-center gap-4">
                                <input type="checkbox" id={`day-${day}`} checked={!!slot} onChange={e => handleScheduleDayChange(day, e.target.checked)} className="h-5 w-5 rounded text-teal-600 focus:ring-teal-500" />
                                <label htmlFor={`day-${day}`} className="w-24">{day}</label>
                                {slot && (
                                    <input type="time" value={slot.time} onChange={e => handleScheduleTimeChange(day, e.target.value)} className="p-1 border rounded-md" />
                                )}
                            </div>
                        )
                    })}
                </div>
            </div>
            <div className="flex justify-end gap-2 pt-4">
                <button type="button" onClick={onCancel} className="bg-stone-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-stone-600 transition-colors">Cancel</button>
                <button type="submit" className="bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-teal-700 transition-colors">Save Changes</button>
            </div>
        </form>
    );
};

const AddUserForm: React.FC<{ onSave: (userData: Omit<User, 'id'>) => void, onCancel: () => void }> = ({ onSave, onCancel }) => {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [role, setRole] = useState<Role>(Role.Client);
    
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSave({ name, email, password, role, status: 'active' });
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <input type="text" placeholder="Full Name" value={name} onChange={e => setName(e.target.value)} className="w-full p-2 border rounded-md" required />
            <input type="email" placeholder="Email Address" value={email} onChange={e => setEmail(e.target.value)} className="w-full p-2 border rounded-md" required />
            <input type="password" placeholder="Password" value={password} onChange={e => setPassword(e.target.value)} className="w-full p-2 border rounded-md" required />
            <select value={role} onChange={e => setRole(e.target.value as Role)} className="w-full p-2 border rounded-md bg-white">
                {Object.values(Role).map(r => <option key={r} value={r}>{r}</option>)}
            </select>
            <div className="flex justify-end gap-2 pt-4">
                <button type="button" onClick={onCancel} className="bg-stone-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-stone-600 transition-colors">Cancel</button>
                <button type="submit" className="bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-teal-700 transition-colors">Add User</button>
            </div>
        </form>
    );
};

const EditTeacherForm: React.FC<{ teacher: Teacher, onSave: (teacher: Teacher) => void, onCancel: () => void }> = ({ teacher, onSave, onCancel }) => {
    const [formData, setFormData] = useState(teacher);

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

     const handleSpecializationsChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const specs = e.target.value.split(',').map(s => s.trim());
        setFormData(prev => ({ ...prev, specializations: specs }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSave(formData);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <input type="text" name="name" placeholder="Name" value={formData.name} onChange={handleInputChange} className="w-full p-2 border rounded-md" required />
            <input type="text" name="profilePictureUrl" placeholder="Profile Picture URL" value={formData.profilePictureUrl} onChange={handleInputChange} className="w-full p-2 border rounded-md" required />
            <textarea name="bio" placeholder="Biography" value={formData.bio} onChange={handleInputChange} className="w-full p-2 border rounded-md h-24" required />
            <input type="text" name="specializations" placeholder="Specializations (comma-separated)" value={formData.specializations.join(', ')} onChange={handleSpecializationsChange} className="w-full p-2 border rounded-md" required />
             <div className="flex justify-end gap-2 pt-4">
                <button type="button" onClick={onCancel} className="bg-stone-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-stone-600 transition-colors">Cancel</button>
                <button type="submit" className="bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-teal-700 transition-colors">Save Changes</button>
            </div>
        </form>
    )
};


const AdminDashboard: React.FC = () => {
  const { courses, teachers, users, updateCourse, updateUser, resetUserPassword, addUser, updateTeacher, addCourse } = useAppContext();
  const [editingCourse, setEditingCourse] = useState<Course | null>(null);
  const [isAddingUser, setIsAddingUser] = useState(false);
  const [editingTeacher, setEditingTeacher] = useState<Teacher | null>(null);
  const [isAddingCourse, setIsAddingCourse] = useState(false);

    const statusColors: Record<User['status'], string> = {
        active: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        disabled: 'bg-red-100 text-red-800',
    };
    
    const roleColors: Record<Role, string> = {
        Admin: 'bg-sky-100 text-sky-800',
        Teacher: 'bg-purple-100 text-purple-800',
        Client: 'bg-stone-100 text-stone-800',
    }

  const handleResetPassword = (userId: number) => {
      const newPassword = prompt("Enter new password for the user:");
      if (newPassword) {
          resetUserPassword(userId, newPassword);
      }
  };

  const handleAddUser = async (userData: Omit<User, 'id'>) => {
      await addUser(userData);
      setIsAddingUser(false);
  }
  
  const handleAddCourse = async (courseData: Omit<Course, 'id'>) => {
      await addCourse(courseData);
      setIsAddingCourse(false);
  }
  
  const emptyCourse: Omit<Course, 'id'> = {
    title: '',
    description: '',
    teacherId: teachers.length > 0 ? teachers[0].id : 0,
    schedule: [],
    price: 99,
    specialityDescription: '',
    gallery: []
  };

  return (
    <div className="space-y-12">
        <div className="grid grid-cols-1 xl:grid-cols-2 gap-8">
            {/* User Management */}
            <div>
                <div className="flex justify-between items-center mb-4">
                    <h3 className="text-2xl font-semibold text-stone-800">User Management</h3>
                    <button onClick={() => setIsAddingUser(true)} className="bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-teal-700 transition-colors">Add User</button>
                </div>
                <div className="bg-white p-4 rounded-lg shadow-sm border border-stone-200 space-y-3 max-h-96 overflow-y-auto">
                    {users.map(user => (
                        <div key={user.id} className="p-3 bg-stone-50 rounded-md grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                            <div className="md:col-span-2">
                                <p className="font-bold text-stone-800">{user.name}</p>
                                <p className="text-sm text-stone-500">{user.email}</p>
                            </div>
                            <div className="flex gap-2">
                                <select 
                                    value={user.role} 
                                    onChange={(e) => updateUser(user.id, { role: e.target.value as Role, status: user.status })}
                                    className={`text-xs font-medium w-full px-2.5 py-1 rounded-full border-none appearance-none text-center ${roleColors[user.role]}`}
                                >
                                    {Object.values(Role).map(r => <option key={r} value={r}>{r}</option>)}
                                </select>
                                <select 
                                    value={user.status} 
                                    onChange={(e) => updateUser(user.id, { role: user.role, status: e.target.value as User['status'] })}
                                    className={`text-xs font-medium w-full px-2.5 py-1 rounded-full border-none appearance-none text-center ${statusColors[user.status]}`}
                                >
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="disabled">Disabled</option>
                                </select>
                            </div>
                            <div className="text-right">
                                <button onClick={() => handleResetPassword(user.id)} className="text-xs bg-stone-200 text-stone-700 font-semibold py-1 px-2 rounded hover:bg-stone-300">Reset Pass</button>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Teacher Profile Management */}
            <div>
                 <div className="flex justify-between items-center mb-4">
                    <h3 className="text-2xl font-semibold text-stone-800">Teacher Profiles</h3>
                </div>
                <div className="bg-white p-4 rounded-lg shadow-sm border border-stone-200 space-y-3 max-h-96 overflow-y-auto">
                    {teachers.map(teacher => (
                        <div key={teacher.id} className="p-3 bg-stone-50 rounded-md flex justify-between items-center">
                            <div className="flex items-center gap-3">
                                <img src={teacher.profilePictureUrl} alt={teacher.name} className="w-10 h-10 rounded-full object-cover"/>
                                <div>
                                    <p className="font-bold text-stone-800">{teacher.name}</p>
                                    <p className="text-sm text-stone-500">{teacher.specializations.slice(0,2).join(', ')}</p>
                                </div>
                            </div>
                            <button onClick={() => setEditingTeacher(teacher)} className="bg-stone-200 text-stone-800 font-semibold py-2 px-4 rounded-lg hover:bg-stone-300 transition-colors">Edit</button>
                        </div>
                    ))}
                </div>
            </div>
        </div>

        <div>
            <div className="flex justify-between items-center mb-4">
                <h3 className="text-2xl font-semibold text-stone-800">Manage Courses</h3>
                <button 
                    onClick={() => setIsAddingCourse(true)} 
                    disabled={teachers.length === 0}
                    title={teachers.length === 0 ? "You must add at least one teacher before creating a course." : "Add a new course"}
                    className="bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-teal-700 transition-colors disabled:bg-stone-400 disabled:cursor-not-allowed">
                    Add Course
                </button>
            </div>
            <div className="space-y-4">
            {courses.map(course => (
                <div key={course.id} className="bg-white p-4 rounded-lg shadow-sm border border-stone-200 flex justify-between items-center">
                    <div>
                        <h4 className="font-bold text-teal-700">{course.title}</h4>
                        <p className="text-sm text-stone-500">{teachers.find(t => t.id === course.teacherId)?.name}</p>
                    </div>
                    <button onClick={() => setEditingCourse(course)} className="bg-stone-200 text-stone-800 font-semibold py-2 px-4 rounded-lg hover:bg-stone-300 transition-colors">Edit</button>
                </div>
            ))}
            </div>
        </div>

        {editingCourse && (
            <Modal title={`Edit: ${editingCourse.title}`} onClose={() => setEditingCourse(null)}>
                <CourseForm initialData={editingCourse} onSave={async (c) => { await updateCourse(c as Course); setEditingCourse(null); }} onCancel={() => setEditingCourse(null)} />
            </Modal>
        )}
        {isAddingCourse && (
             <Modal title="Add New Course" onClose={() => setIsAddingCourse(false)}>
                <CourseForm initialData={emptyCourse} onSave={handleAddCourse} onCancel={() => setIsAddingCourse(false)} />
            </Modal>
        )}
        {isAddingUser && (
            <Modal title="Add New User" onClose={() => setIsAddingUser(false)}>
                <AddUserForm onSave={handleAddUser} onCancel={() => setIsAddingUser(false)} />
            </Modal>
        )}
        {editingTeacher && (
             <Modal title={`Edit Profile: ${editingTeacher.name}`} onClose={() => setEditingTeacher(null)}>
                <EditTeacherForm teacher={editingTeacher} onSave={async (t) => { await updateTeacher(t); setEditingTeacher(null); }} onCancel={() => setEditingTeacher(null)} />
            </Modal>
        )}
    </div>
  );
};

export default AdminDashboard;