import React, {useState, useMemo} from 'react';
import { absoluteUrl } from '../utils/helpers';

export const SchoolLogo = (props) => {
    const { images, width, height } = props;
    const logo= useMemo(() => {
        return images.find((image) =>
        image.filename?.toLowerCase().includes("logo")
        );
    }, [images]);
    const url = absoluteUrl('/images/none.png');
    const source = logo?.url ?? url;
    const title =logo?.title ?? "logo none";
    return (
        <>
            <img src={source} alt={title} width={width} height={height} />
        </>
    );
}