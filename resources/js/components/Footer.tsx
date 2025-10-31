import React from 'react';

const Footer: React.FC = () => (
    <footer className="bg-white mt-12 py-6 border-t border-stone-200">
        <div className="container mx-auto px-4 text-center text-stone-500">
            <p>&copy; {new Date().getFullYear()} Shanti Sadhana Yoga Center. All Rights Reserved.</p>
            <p className="text-sm mt-1">Find your inner peace, one breath at a time.</p>
        </div>
    </footer>
);

export default Footer;
