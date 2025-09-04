import React, { useState } from "react";

export const SchoolSchedule = (props) => {
    const { schedules } = props;
    return (
        <div className="mo-accordion">
            <input type="checkbox" id="mo-accordion-schedule" />
            <h2>
                <label htmlFor="mo-accordion-schedule">Horaire scolaire</label>
            </h2>
           <div className="mo-accordion-content">
               {/* Render the schedule here */}
               <div className="mo-row mo-flex-column mo-mx-1 mo-my-1">
                   <div className="mo-col mo-pl-3">
                          {/* Example static schedule, replace with dynamic rendering */}
                        {schedules.filter(schedule => {
                            const jour = schedule.jour.toLowerCase();
                            return jour !== 'dimanche';
                        }).map((schedule, index) => (
                            <div key={index}>
                                <b>{schedule.jour}: </b>
                                <span>{schedule.debut} - {schedule.fin}</span><br/>
                            </div>
                        ))}
                        <b>Dimanche: </b><span>fermé</span><br/>
                   </div>
               </div>
           </div>
        </div>
    );
};
