import React from 'react';

interface ModalProps {
    title: string;
    onClose: () => void;
    children: React.ReactNode;
}

const Modal: React.FC<ModalProps> = ({ title, onClose, children }) => (
    <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center p-4">
        <div className="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
            <div className="flex justify-between items-center p-4 border-b">
                <h2 className="text-xl font-semibold text-stone-800">{title}</h2>
                <button onClick={onClose} className="text-stone-500 hover:text-stone-800 text-2xl">
                    &times;
                </button>
            </div>
            <div className="p-6 overflow-y-auto">{children}</div>
        </div>
    </div>
);

export default Modal;
