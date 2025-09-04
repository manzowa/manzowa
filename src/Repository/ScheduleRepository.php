<?php

namespace App\Repository;

use App\Model\Schedule;

class ScheduleRepository extends Repository
{
    public function retrieve(
        ?int $id = null, 
        ?int $schoolid = null
    ): array {
        // Implementation for retrieving schedules
        $schedules = [];
        $command = 'SELECT * FROM `horaires` h ';
        $command .= 'INNER JOIN ecoles e on h.ecoleid = e.id ';

        if (!is_null($id) && !is_null($schoolid)) {
            $command .= 'WHERE h.ecoleid = :ecoleid ';
            $command .= 'AND h.id = :id ';
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->bindParam(':ecoleid', $schoolid, \PDO::PARAM_INT);

        }
        if (!is_null($id) && is_null($schoolid)) {
            $command .= 'WHERE h.id = :id ';
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT);
        }

        if (is_null($id) && !is_null($schoolid)) {
            $command .= 'WHERE h.ecoleid = :schoolid ';
            $this->prepare($command)
                ->bindParam(':schoolid', $schoolid, \PDO::PARAM_INT);
        }

        $scheduleRows = $this->executeQuery()
            ->getResults();
        if (is_array($scheduleRows) && count($scheduleRows) > 0) {
            foreach ($scheduleRows as $scheduleRow) {
                $schedule = Schedule::fromState($scheduleRow);
                $schedules[] = $schedule->toArray();
            }
        }
        return $schedules;
    }

    public function add(Schedule $schedule): void
    {
        // Implementation for adding a schedule
        $jour = $schedule->getJour();
        $debut = $schedule->getDebut();
        $fin = $schedule->getFin();
        $ecoleid = $schedule->getEcoleId();
        $command = 'INSERT INTO horaires (jour, debut, fin, ecoleid) ';
        $command .= 'VALUES (:jour, :debut, :fin, :ecoleid)';
        $this->prepare($command)
            ->bindParam(':jour', $jour, \PDO::PARAM_STR)
            ->bindParam(':debut', $debut, \PDO::PARAM_STR)
            ->bindParam(':fin', $fin, \PDO::PARAM_STR)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT)
            ->execute();
    }

    public function update(Schedule $schedule): self
    {
        // Implementation for finding a schedule by ID
        $jour = $schedule->getJour();
        $debut = $schedule->getDebut();
        $fin = $schedule->getFin();
        $ecoleid = $schedule->getEcoleId();
        $id = $schedule->getId();

        $command = 'UPDATE horaires SET jour = :jour, debut = :debut, ';
        $command .= 'fin = :fin, ecoleid = :ecoleid WHERE id = :id';
        $this->prepare($command)
            ->bindParam(':jour', $jour, \PDO::PARAM_STR)
            ->bindParam(':debut', $debut, \PDO::PARAM_STR)
            ->bindParam(':fin', $fin, \PDO::PARAM_STR)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT)
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->execute();

        return $this; // Placeholder return
    }


    public function updateByDay(Schedule $schedule): self
    {
        // Implementation for updating a schedule
        $jour = $schedule->getJour();
        $debut = $schedule->getDebut();
        $fin = $schedule->getFin();
        $ecoleid = $schedule->getEcoleId();
        $id = $schedule->getId();

        $command = 'UPDATE horaires SET debut = :debut, fin = :fin WHERE jour = :jour ';
        $command .= 'AND ecoleid = :ecoleid And id = :id';
        $this->prepare($command)
            ->bindParam(':debut', $debut, \PDO::PARAM_STR)
            ->bindParam(':fin', $fin, \PDO::PARAM_STR)
            ->bindParam(':jour', $jour, \PDO::PARAM_STR)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT)
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->execute();

        return $this;
    }

    public function remove(int $id, ?int $schoolid = null): self
    {
        // Implementation for removing a schedule by ID
        $command = 'DELETE FROM horaires WHERE id = :id ';
        if (!is_null($schoolid)) {
            $command .= 'AND ecoleid = :ecoleid';
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->bindParam(':ecoleid', $schoolid, \PDO::PARAM_INT);
        } else {
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT);
        }
        $this->execute();

        return $this;
    }

    public function retrieveByDay(?string $day = null): ?Schedule
    {
        // Implementation for finding a schedule by day
        if (is_null($day)) {
            $command = 'SELECT * FROM horaires WHERE jour = :jour LIMIT 1';
            $rows = $this->prepare($command)
                ->bindParam(':jour', $day, \PDO::PARAM_STR)
                ->executeQuery()
                ->getResults();

            if (is_array($rows) && count($rows) > 0) {
                $row = current($rows);
                $schedule = Schedule::fromState($row);
                return $schedule;
            }
        }
        return null; // Placeholder return
    }
}