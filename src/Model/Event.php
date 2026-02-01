<?php 

/**
 * File Event
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
    use App\Exception\EventException;

    final class Event
    {
        protected ?int $id;
        protected ?string $titre;
        protected ?string $description;
        protected ?string $date;
        protected ?string $lieu;
        protected ?int $ecoleid;
        protected ?int $maximage;
        protected array $images;

        public const MAXIMA_IMAGE = 1;


        /**
         * Constructor
         * 
         * @param ?int    $id
         * @param ?string $titre
         * @param ?string $description
         * @param ?string $date
         * @param ?string $lieu
         * @param ?int $ecoleid
         * @param ?int $maximage
         * @param array $images
         */
        public function __construct(
            ?int $id, 
            ?string $titre, 
            ?string $description = null, 
            ?string $date = null, 
            ?string $lieu = null, 
            ?int $ecoleid = null,
            ?int $maximage = null,
            array $images = []
        ) {
            $this
                ->setId($id)
                ->setTitre($titre)
                ->setDescription($description)
                ->setDate($date)
                ->setLieu($lieu)
                ->setEcoleid($ecoleid)
                ->setMaximage($maximage)
                ->setImages($images);
        }

        /**
         * Get the value of id
         * 
         * @return ?int
         */
        public function getId(): ?int {
            return $this->id;
        }

        /**
         * Get the value of titre
         * 
         * @return ?string
         */
        public function getTitre(): ?string {
            return $this->titre;
        }

        /**
         * Get the value of description
         * 
         * @return ?string
         */
        public function getDescription(): ?string {
            return $this->description;
        }

        /**
         * Get the value of date
         * 
         * @return ?string
         */
        public function getDate(): ?string {
            return $this->date;
        }
        /**
         * Get the value of lieu
         * 
         * @return ?string
         */
        public function getLieu(): ?string {
            return $this->lieu;
        }
        /**
         * Get the value of ecoleid
         * 
         * @return ?int
         */
        public function getEcoleid(): ?int {
            return $this->ecoleid;
        }
        /**
         * Get the value of maximage
         *
         * @return ?int
         */
        public function getMaximage(): ?int {
            return $this->maximage;
        }

        /**
         * Get the value of images
         *
         * @return ?array
         */
        public function getImages():?array {
            return $this->images;
        }


        /**
         * Set the value of id
         *
         * @param  ?int  $id
         *
         * @return self
         */
        private function setId(?int $id): self
        {
            if ($id !== null && !is_int($id)) {
                throw new EventException("L'identifiant de l'event doit être un entier ou null.");
            }
            $this->id = $id;
            return $this;
        }
        /**
         * Set the value of titre
         *
         * @param  ?string  $titre
         *
         * @return self
         */
        public function setTitre(?string $titre): self
        {
            if ($titre !== null && (mb_strlen($titre) < 1 || mb_strlen($titre) > 100)) {
                throw new EventException("Le titre de l'event doit contenir entre 1 et 100 caractères.");
            }
            $this->titre = $titre;
            return $this;
        }
        /**
         * Set the value of description
         *
         * @param  ?string  $description
         *
         * @return self
         */
        public function setDescription(?string $description): self
        {
            if ($description !== null && mb_strlen($description) > 1000) {
                throw new EventException("La description de l'event ne doit pas dépasser 1000 caractères.");
            }
            $this->description = $description;
            return $this;
        }
        /**
         * Set the value of date
         * 
         * @param  ?string  $date
         *
         * @return self
         */
        public function setDate(?string $date): self
        {
            if ($date !== null && !\DateTime::createFromFormat('Y-m-d H:i:s', $date)) {
                throw new EventException(
                    "La date de l'event doit être au format YYYY-MM-DD H:i:s."
                );
            }
            $this->date = $date;
            return $this;
        }
        /**
         * Set the value of lieu
         *
         * @param  ?string  $lieu
         *
         * @return self
         */
        public function setLieu(?string $lieu): self
        {
            if ($lieu !== null && (mb_strlen($lieu) < 1 || mb_strlen($lieu) > 255)) {
                throw new EventException(
                    "Le lieu de l'event doit contenir entre 1 et 255 caractères."
                );
            }
            $this->lieu = $lieu;
            return $this;
        }
       
        /**
         * Set the value of ecoleid
         *
         * @param  ?int  $ecoleid
         *
         * @return self
         */
        public function setEcoleid(?int $ecoleid): self
        {
            if ($ecoleid !== null && !is_int($ecoleid)) {
                throw new EventException(
                    "L'identifiant de l'école doit être un entier ou null."
                );
            }
            $this->ecoleid = $ecoleid;
            return $this;
        }
        /**
         * Set the value of maximage
         *
         * @param  ?int  $maximage
         *
         * @return self
         */
        public function setMaximage(?int $maximage): self
        {
            if ((!is_null($maximage)) 
                && (
                    !is_numeric($maximage) 
                    || $maximage <= 0 || $maximage > static::MAXIMA_IMAGE
                )
            ) {
                throw new EventException("Maxime image error");
            }
            $this->maximage = $maximage;
            return $this;
        }

        public function setImages(?array $images): self
        {
            if ($images !== null && !is_array($images)) {
                throw new EventException(
                    "Les images de l'event doivent être un tableau ou null."
                );
            }
            $this->images = $images;
            return $this;
        }

        public function isMaximunImage(): bool 
        {
            $isBool = (
                !is_null($this->getMaximage()
            ) && self::MAXIMA_IMAGE <= $this->getMaximage())? true : false;
            return $isBool;
        }

        public function toArray(): array
        {
            return [
                'id'          => $this->getId(),
                'titre'      => $this->getTitre(),
                'description' => $this->getDescription(),
                'date'      => $this->getDate(),
                'lieu'        => $this->getLieu(),
                'ecoleid'     => $this->getEcoleid(),
                'maximage'   => $this->getMaximage(),
                'images'     => $this->getImages() ?? []
            ];
        }

        public static function fromState(array $data = []): Event
        {
            return new static(
                id: $data['id'] ?? null,
                titre: $data['titre'] ?? null,
                description: $data['description'] ?? null,
                date: $data['date'] ?? null,
                lieu: $data['lieu'] ?? null,
                ecoleid: $data['ecoleid'] ?? null,
                maximage: $data['maximage'] ?? null,
                images: $data['images'] ?? []
            );
        }

        public static function fromObject(object $data): Event
        {
            return new static(
                id: $data->id ?? null,
                titre: $data->titre ?? null,
                description: $data->description ?? null,
                date: $data->date ?? null,
                lieu: $data->lieu ?? null,
                ecoleid: $data->ecoleid ?? null,
                maximage: $data->maximage ?? null,
                images: $data->images ?? []
            );
        }

        public static function fromJson(string $json): Event
        {
            $data = json_decode($json, true);
            return self::fromState($data);
        }
    }
}