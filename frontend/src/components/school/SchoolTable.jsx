import React from 'react';
import { SchoolLogo } from './SchoolLogo';
import { ucfirst } from '../utils/helpers';

export const SchoolTable = (props) => {
    const { schools, loading } = props;
    return (
        <>
            <table>
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Nom</th>
                        <th>Logo</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    {schools.length > 0 && schools.map((school) => (
                        <tr key={school.id}>
                            <td>{school.id}</td>
                            <td>{ucfirst(school.nom)}</td>
                            <td><SchoolLogo images={school.images} width={30} height={30}/></td>
                            <td>
                                <a href={`/compte/ecoles/${school.id}/voir`} className="mo-btn mo-primary mo-rounded">voir</a>
                                <a href={`/compte/ecoles/${school.id}/edit`} className="mo-btn mo-warning mo-rounded">edit</a>
                            </td>
                        </tr>
                    ))}
                    {!loading && schools.length === 0 && (
                        <tr>
                            <td colSpan="3">
                                Aucune école trouvée.
                            </td>
                        </tr>
                    )}
                </tbody>
            </table>
        </>
    );
};
