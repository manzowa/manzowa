import React, { useState, useEffect } from "react";
import { createRoot } from "react-dom/client";
import { useSchool } from "../hooks/useSchool";
import { useLinkActive } from "../hooks/useLinkActive";
import { ucfirst } from "../components/utils/helpers";
import {
  SchoolAddresse,
  SchoolSlider,
  SchoolDetail
} from "../components/school";

const App = () => {
  // Récupérer les données depuis l'élément DOM
  const schoolElement = document.getElementById("react-root");
  let schoolId = 0;
  if (schoolElement) {
    schoolId = schoolElement.dataset.schoolId || 0;
  }
  const { school, loading, error } = useSchool(parseInt(schoolId));
  const { isActive } = useLinkActive(school?.site);

  return (
    <>
      {school && (
        <div className="mo-content">
          <h2>École n°{school.id} ({ucfirst(school.nom)})</h2>
          <SchoolDetail school={school} />
          <SchoolAddresse adresses={school.adresses || []} />
          <SchoolSlider images={school.images || []} />
        </div>
      )}
    </>
  );
};

const container = document.getElementById("react-root");
const root = createRoot(container);
root.render(<App />);
