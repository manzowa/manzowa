import React, { useEffect, useState, useCallback } from "react";
import { fetchSchoolSearch } from "../service/api";

/**
 * Hook personnalisé pour charger les écoles
 *
 * @param {number} limit - Nombre de résultats à récupérer
 * @param {number} offset - Page de pagination
 * @param {string} nom - Nom à rechercher (optionnel)
 * @param {string} type - Type à rechercher (optionnel)
 */
// Hook de recherche des écoles avec debounce et pagination
export function useSchoolsSearch(
  initialLimit = 1, initialOffset = 5, 
  initialNom = '', initialType = ''
) {
  const [limit, setLimit] = useState(initialLimit);

  const [offset, setOffset] = useState(initialOffset);
  const [nom, setNom] = useState(initialNom);
  const [type, setType] = useState(initialType);

  const [debouncedLimit, setDebouncedLimit] = useState(limit);
  const [debouncedOffset, setDebouncedOffset] = useState(offset);
  const [debouncedNom, setDebouncedNom] = useState(nom);
  const [debouncedType, setDebouncedType] = useState(type);

  const [schools, setSchools] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [pagination, setPagination] = useState({
    rowsReturned: 0,
    totalRows: 0,
    totalPages: 0,
    hasNextPage: false,
    hasPreviousPage: false,
  });

  // Debounce: limit (nombre de résultats)
  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedLimit(limit);
    }, 300);
    return () => clearTimeout(handler);
  }, [limit]);

  // Debounce: offset (pagination)
  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedOffset(offset);
    }, 300);
    return () => clearTimeout(handler);
  }, [offset]);

  // Debounce: nom (recherche texte)
  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedNom(nom);
      setLimit(1); // Reset page on new filter
      setOffset(5); // Reset page on new filter
    }, 400);
    return () => clearTimeout(handler);
  }, [nom]);

  // Debounce: type (filtrage)
  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedType(type);
      setLimit(1); // Reset page on new filter
      setOffset(5); // Reset page on new filter
    }, 400);
    return () => clearTimeout(handler);
  }, [type]);

  // Requête API quand les valeurs debounced changent
  useEffect(() => {
    const loadSchools = async () => {
      setLoading(true);
      setError(null);

      try {
        const data = await fetchSchoolSearch(
          limit, debouncedOffset, debouncedNom, debouncedType
        );

        setSchools(data.data.schools || []);
        setPagination({
          rowsReturned: data.data.rows_returned,
          totalRows: data.data.total_rows,
          totalPages: data.data.total_pages,
          hasNextPage: data.data.has_next_page,
          hasPreviousPage: data.data.has_privious_page,
        });
      } catch (err) {
        setError(err.message || 'Une erreur est survenue');
      } finally {
        setLoading(false);
      }
    };

    loadSchools();
  }, [debouncedLimit, debouncedOffset, debouncedNom, debouncedType]);

  // Fonctions utilisables dans le composant
  const goToPage = (page) => setLimit(page);
  const searchByName = (value) => setNom(value);
  const searchByType = (value) => setType(value);

  return {
    schools,
    loading,
    error,
    pagination,
    currentPage: limit,
    goToPage,
    searchByName,
    searchByType,
  };
}