import { useEffect, useState, useCallback } from "react";
import { fetchSchools, fetchSchoolsByName } from "../service/api";

/**
 * Hook personnalisé pour charger les écoles
 * @param {string} name - Nom à rechercher (optionnel)
 * @param {number} limit - Nombre de résultats à récupérer
 * @param {number} offset - Page de pagination
 */
export function useSchoolsBy(name, limit = 5, offset = 1) {
  const [schools, setSchools] = useState([]);           // Liste des écoles
  const [loading, setLoading] = useState(true);         // Indicateur de chargement
  const [error, setError] = useState(null);             // Gestion d’erreur
  const [debouncedName, setDebouncedName] = useState(name);
  const [currentPage, setCurrentPage] = useState(offset); // Page courante
  const [totalPages, setTotalPages] = useState(0);      // Nombre total de pages

  // Debounce le nom pour éviter trop d'appels API
  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedName(name);
    }, 500);

    return () => clearTimeout(timer);
  }, [name]);

  const getSchools = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);

      let data = [];
      if (debouncedName?.trim()) {
        // Recherche par nom
        data = await fetchSchoolsByName(debouncedName, limit);
      } else {
        // Recherche par pagination
        data = await fetchSchools(currentPage, limit);
      }
      //console.log("Données récupérées:", data.totalPages || 1); // Vérifier ce qui est récupéré
      setSchools(Array.isArray(data) ? data : []);
      setTotalPages(400 || 1); // Assurez-vous que l'API renvoie le nombre total de pages
    } catch (err) {
      console.error("Erreur lors du chargement des écoles:", err);
      setError(err);
      setSchools([]);
    } finally {
      setLoading(false);
    }
  }, [debouncedName, limit, currentPage]);

  // Charger les écoles à chaque fois que la page ou les critères changent
  useEffect(() => {
    getSchools();
  }, [getSchools]);

  // Fonction pour changer de page
  const changePage = (newPage) => {
    if (newPage > 0 && newPage <= totalPages) {
      setCurrentPage(newPage);
    }
  };

  return {
    schools,
    loading,
    error,
    currentPage,
    totalPages,
    changePage, // Permet de changer de page depuis le composant appelant
  };
}
