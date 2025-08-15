import React, { useState } from "react";
import { ucfirst} from "../utils/helpers";

export const SchoolAddresse = (props) => {
  const { adresses } = props;

  return (
    <div className="mo-accordion">
      <input type="checkbox" id="mo-accordion-addresses" />
      <h2><label htmlFor="mo-accordion-addresses">Adresse de l'école</label></h2>
        {adresses.map((adresse) => (
          <div key={adresse.id} className="mo-accordion-content">
            <div className="mo-row mo-flex-column mo-mx-1 mo-my-1">
              <span><b>Voie:</b> {ucfirst(adresse.voie)}</span>
              <span><b>Quartier:</b> {ucfirst(adresse.quartier)}</span>
              <span><b>Commune:</b> {ucfirst(adresse.commune)}</span>
              <span><b>District:</b> {ucfirst(adresse.district)}</span>
            </div>
          </div>
        ))}
    </div>
  );
};