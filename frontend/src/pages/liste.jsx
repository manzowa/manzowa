import React from 'react';
import { createRoot } from 'react-dom/client';
import { SchoolAutocomplete } from '../components/school';
import { useSchoolsSearch } from '../hooks/useSchoolsSearch';


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