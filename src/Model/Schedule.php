<?php 

/**
 * File Schedule
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category App\Model
 * @package  App\Model
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
namespace App\Model
{
    use App\Exception\ScheduleException;

    class Schedule
    {
        private ?int $id;
        private ?string $jour;
        private ?string $debut;
        private ?string $fin;
        private ?int $ecoleid;

        public function __construct(
            ?int $id, ?string $jour, 
            ?string $debut, ?string $fin, 
            ?int $ecoleid
        )
        {
            $this->setId($id)
                ->setJour($jour)
                ->setDebut($debut)
                ->setFin($fin)
                ->setEcoleId($ecoleid);
        }

        public function getId(): int
        {
            return $this->id;
        }

        public function getJour(): ?string
        {
            return $this->jour;
        }

        public function getDebut(): ?string
        {
            return $this->debut;
        }

        public function getFin(): ?string
        {
            return $this->fin;
        }
        /**
         * Get the value of ecoleid
         *
         * @return ?int
         */
        public function getEcoleId(): ?int
        {
            return $this->ecoleid;
        }

        public static function fromState(array $state): Schedule
        {
            return new Schedule(
                $state['id'] ?? null,
                $state['jour'] ?? null,
                $state['debut'] ?? null,
                $state['fin'] ?? null,
                $state['ecoleid'] ?? null
            );
        }
        
        public function toArray(): array
        {
            return [
                'id' => $this->getId(),
                'jour' => $this->getJour(),
                'debut' => $this->getDebut(),
                'fin' => $this->getFin(),
                'ecoleid' => $this->getEcoleId()
            ];
        }

        /**
         * Set the value of id
         *
         * @param int $id
         *
         * @return self
         */
        public function setId(?int $id): self {
             if ((!is_null($id)) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807)) {
                throw new ScheduleException("Schedule ID error");
            }
            $this->id = $id;
            return $this;
        }

        /**
         * Set the value of jour
         *
         * @param ?string $jour
         *
         * @return self
         */
        public function setJour(?string $jour): self 
        {
             if (is_null($jour) || mb_strlen($jour) < 0 || mb_strlen($jour)>50) {
                throw new ScheduleException("Schedule day error.");
            }
            $this->jour = $jour;
            return $this;
        }

        /**
         * Set the value of debut
         *
         * @param ?string $debut
         *
         * @return self
         */
        public function setDebut(?string $debut): self 
        {
            if (is_null($debut) || mb_strlen($debut) < 0 || mb_strlen($debut)>50) {
                throw new ScheduleException("Schedule start time error.");
            }
            $this->debut = $debut;
            return $this;
        }

        /**
         * Set the value of fin
         *
         * @param ?string $fin
         *
         * @return self
         */
        public function setFin(?string $fin): self 
        {
            if (is_null($fin) || mb_strlen($fin) < 0 || mb_strlen($fin)>50) {
                throw new ScheduleException("Schedule end time error.");
            }
            $this->fin = $fin;
            return $this;
        }

        /**
         * Set the value of ecoleid
         *
         * @param ?int $ecoleid
         *
         * @return self
         */
        public function setEcoleId(?int $ecoleid): self
        {
            if ((!is_null($ecoleid)) && (!is_numeric($ecoleid) || $ecoleid <= 0 || $ecoleid > 9223372036854775807)) {
                throw new ScheduleException("Schedule School ID error");
            }
            $this->ecoleid = $ecoleid;
            return $this;
        }

        public function __toString(): string
        {
            return 
            "ID: " . $this->getId() . "\n" .
            "Jour: " . $this->getJour() . "\n" .
            "Debut: " . $this->getDebut() . "\n" .
            "Fin: " . $this->getFin() . "\n" .
            "Ecole ID: " . $this->getEcoleId() . "\n";
        }
    }
}