import React, { useState } from "react";
import { useSchoolsBy } from "../../hooks/useSchoolsBy";
import { useSchoolsSearch } from "../../hooks/useSchoolsSearch";
import { SchoolTable } from "./SchoolTable";

export const SchoolAutocomplete = () => {

  const {
    schools,
    loading,
    error,
    pagination,
    currentPage,
    goToPage,
    searchByName,
    searchByType,
  } = useSchoolsSearch();

  const handleSearchChange = (e) => {
    const value = e.target.value;
    searchByName(value);
  };
  const handleTypeChange = (e) => {
    const value = e.target.value;
    searchByType(value);
  };
  const handleNextPage = () => {
    goToPage(currentPage + 1);
  };
  const handlePreviousPage = () => {
    goToPage(currentPage - 1);
  };

  return (
    <div className="mo-content">
      <h2> Gestion des écoles </h2>
      
      <div className="mo-control-inline">
        <input
          type="text" onChange={handleSearchChange}
          placeholder="Rechercher une école"
        />
         {/* <select onChange={handleTypeChange} className="mo-btn mo-rounded"> */}
        <select  className="mo-btn mo-rounded mo-hidden">
          <option value="">Tous les types</option>
          <option value="public">Public</option>
          <option value="private">Privé</option>
        </select>
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
          disabled={!pagination.hasPreviousPage} 
          onClick={handlePreviousPage}
        >Précédent</button>
        {/* <span>{currentPage} sur {totalPages}</span> */}
        <button
          className="mo-btn mo-primary"
          disabled={!pagination.hasNextPage} 
          onClick={handleNextPage}
        >Suivant</button>
      </div>
    </div>
  );
};
