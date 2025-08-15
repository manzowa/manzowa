import { useEffect, useState } from "react";
import { fetchSchool } from "../service/api";

export function useSchool(id = 0) {
    const [school, setSchool] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        let isCancelled = false;
        async function getSchool() {
            setLoading(true);
            setError(null);
            try {
                const data = await fetchSchool(id);
                if (!isCancelled) {
                    setSchool(data);
                }
            } catch (err) {
                if (!isCancelled) {
                    setError(err);
                    setSchool(null);
                }
            } finally {
                if (!isCancelled) {
                    setLoading(false);
                }
            }
        }
        getSchool();
        return () => {
            isCancelled = true;
        };
    }, [id]);

    return { school, loading, error };
}