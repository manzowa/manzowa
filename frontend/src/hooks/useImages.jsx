import { useEffect, useState } from "react";
import { fetchImages} from "@/services/api";


export function useImages(id) {
    const [images, setImages] = useState([]);
    const [ImageHasloading, setLoading] = useState(true);

    useEffect(() => {
        async function getImages() {
            setLoading(true);

            const data = await fetchImages(id);
            setImages(data);
            setLoading(false);
        }
        getImages();
    }, [id]);
    return { images, ImageHasloading };
};
