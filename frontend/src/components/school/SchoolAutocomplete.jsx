import React, { useState } from "react";
import { useSchoolsBy } from "../../hooks/useSchoolsBy";
import { SchoolTable } from "./SchoolTable";

export const SchoolAutocomplete = () => {
  const [input, setInput] = useState("");
  const { schools, loading, error, currentPage, totalPages, changePage } =
    useSchoolsBy(input, 5, 1);

  const handleChange = (e) => {
    setInput(e.target.value);
  };

  return (
    <div className="mo-content">
      <h2> Gestion des écoles </h2>
      
      <div className="mo-control-inline">
        <input
          type="text"
          value={input}
          onChange={handleChange}
          placeholder="Rechercher une école"
        />
        <button type="button" className="mo-btn mo-danger mo-rounded">
          vider
        </button>
        <a
          href="/compte/ecoles/ajouter"
          className="mo-btn mo-success mo-rounded"
        >
          ajouter
        </a>
      </div>
      {loading && <div>Chargement...</div>}
      {error && <div>Erreur: {error.message}</div>}
      <SchoolTable schools={schools} />
      {/* Pagination */}
      <div className="pagination">
        <button
          className="mo-btn mo-primary"
          onClick={() => changePage(currentPage - 1)}
          disabled={currentPage === 1}
        >
          Précédent
        </button>
        {/* <span>{currentPage} sur {totalPages}</span> */}
        <button
          className="mo-btn mo-primary"
          onClick={() => changePage(currentPage + 1)}
          disabled={currentPage === totalPages}
        >
          Suivant
        </button>
      </div>
    </div>
  );
};
