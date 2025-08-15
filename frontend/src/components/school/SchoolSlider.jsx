import React, { useState } from "react";

const EmptyImage = () => {
  return (
    <div className="mo-accordion">
      <input type="checkbox" id="mo-accordion-slider" />
      <h2><label htmlFor="mo-accordion-slider">Images de l'école</label></h2>
      <div className="mo-accordion-content">
        <div className="mo-empty">Aucune image disponible</div>
      </div>
    </div>
  );
};

export const SchoolSlider = (props) => {
  const { images } = props;
  const [currentIndex, setCurrentIndex] = useState(0);

  if (!images || images.length === 0) {
    return <EmptyImage />;
  }
  // Fonction pour aller à l'image suivante
  const nextImage = () => {
    if (currentIndex < images.length - 1) {
      setCurrentIndex(currentIndex + 1);
    } else {
      setCurrentIndex(0); // Retourner à la première image
    }
  };

  // Fonction pour revenir à l'image précédente
  const prevImage = () => {
    if (currentIndex > 0) {
      setCurrentIndex(currentIndex - 1);
    } else {
      setCurrentIndex(images.length - 1); // Aller à la dernière image
    }
  };


  return (
    <div className="mo-accordion">
      <input type="checkbox" id="mo-accordion-slider" />
      <h2><label htmlFor="mo-accordion-slider">Images de l'école</label></h2>
      <div className="mo-accordion-content">
        <div className="school-slider">
          <button onClick={prevImage} className="slider-btn prev-button">
            &#8592;
          </button>
          <div className="image-container">
            <img
              src={images[currentIndex].url}
              alt={images[currentIndex].title}
            />
          </div>
          <button onClick={nextImage} className="slider-btn next-button">
            &#8594;
          </button>
        </div>
      </div>
    </div>
  );
};
