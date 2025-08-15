import React from 'react';
import { createRoot } from 'react-dom/client';
import { SchoolAutocomplete } from '../components/school';


const App = () => {
  return (
    <>
     <SchoolAutocomplete />
    </>
  );
};

const container = document.getElementById('react-root');
const root = createRoot(container);
root.render(<App />);