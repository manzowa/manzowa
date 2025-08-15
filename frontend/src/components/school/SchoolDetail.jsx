import React, { useState } from "react";
import { ucfirst } from "../utils/helpers";
import { useLinkActive } from "../../hooks/useLinkActive";

export const SchoolDetail = (props) => {
  const { school } = props;
  const { isActive } = useLinkActive(school?.site);
  console.log("SchoolDetail", school.maximage);
  return (
    <div className="mo-accordion">
      <input type="checkbox" id="mo-accordion-detail" />
      <h2>
        <label htmlFor="mo-accordion-detail">Information école</label>
      </h2>
      <div className="mo-accordion-content">
        {/* Affiche les détails de l'école */}
        <div className="mo-row mo-flex-column mo-mx-1 mo-my-1">
          {school?.nom && (
            <span>
              <b>Nom:</b> {ucfirst(school.nom)}
            </span>
          )}
          {school?.email && (
            <span>
              <b>Email:</b> {school.email}
            </span>
          )}
          {school?.telephone && (
            <span>
              <b>Téléphone:</b> {school.telephone}
            </span>
          )}
          {school?.type && (
            <span>
              <b>Type:</b> {ucfirst(school.type)}
            </span>
          )}
          {school?.maximage && school.maximage <= 5 && (
            <span>
              <b>Nombre d'images chargées:</b> {school.maximage}
            </span>
          )}
          {/* Vérifie que l'URL est valide avant d'afficher le lien */}
          {isActive && (
            <span>
              Site :{" "}
              <a href={school?.site} className="mo-btn mo-warning">
                allez sur le site
              </a>
            </span>
          )}
          <div className="mo-action-container mo-mt-1">
            {school?.maximage != 5 && (
              <a
                href={`/compte/ecoles/${school.id}/images`}
                className="mo-btn mo-success"
              >
                Ajouter une image
              </a>
            )}
            <a
              href={`/compte/ecoles/${school.id}/edit`}
              className="mo-btn mo-warning"
            >
              Modifier l'école
            </a>
          </div>
        </div>
      </div>
    </div>
  );
};
