import { useEffect, useState } from "react";
import { isLinkActive } from "../components/utils/helpers";

export function useLinkActive(url = "") {
    const [isActive, setIsActive] = useState(false);
    useEffect(() => {
        const checkLink = async () => {
            if (url) {
                const isActive = await isLinkActive(url);
                setIsActive(isActive);
            }
        };
        checkLink();
    }, [url]);
    return { isActive };
}