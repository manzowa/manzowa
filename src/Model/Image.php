<?php 

/**
 * File Image
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
    use Gumlet\ImageResize;
    use Gumlet\ImageResizeException;
    use App\Exception\ImageException;

    final class Image
    {
        protected readonly ?int $id;
        protected ?string $title;
        protected ?string $filename;
        protected ?string $mimetype;
        protected ?string $type; // e.g., 'S' = ecole, 'E' = event, etc.
        protected ?int $ecoleid;
        protected ?int $evenementid;
        protected ?string $url;
        protected ?string $location;

        protected ?string $uploadFolderLocation;
       
     

        public function __construct
        (
            ?int $id, 
            ?string $title, 
            ?string $filename,
            ?string $mimetype,
            ?string $type = "S",
            ?int $ecoleid = null,
            ?int $evenementid = null,
            ?string $location = "ecoles"
        ) {
            $this
                ->setId($id)
                ->setTitle($title)
                ->setFilename($filename)
                ->setMimetype($mimetype)
                ->setType($type)
                ->setEcoleid($ecoleid)
                ->setEvenementid($evenementid)
                ->setUploadFolderLocation(
                    \App\path('public', 'uploads', $location)
                );
        }
                
        /**
         * Get the value of id
         */
        public function getId() {
            return $this->id;
        }

        /**
         * Set the value of id
         */
        public function setId($id): self 
        {
            if (($id !== null) && 
                (!is_numeric($id) || $id<= 0 
                    || $id > 9223372036854775807 
                )
            ) {
                throw new ImageException('Image ID error');
            }
            $this->id = $id;
            return $this;
        }

        /**
         * Get the value of title
         *
         * @return ?string
         */
        public function getTitle(): ?string {
            return $this->title;
        }

        /**
         * Set the value of title
         *
         * @param ?string $title
         *
         * @return self
         */
        public function setTitle(?string $title): self {
            if (strlen($title) < 1 || strlen($title) > 255) {
                throw new ImageException('Image title error');
            }
            $this->title = $title;
            return $this;
        }

        /**
         * Get the value of filename
         *
         * @return ?string
         */
        public function getFilename(): ?string {
            return $this->filename;
        }

        /**
         * Set the value of filename
         *
         * @param ?string $filename
         *
         * @return self
         */
        public function setFilename(?string $filename): self 
        {
            $regex = "/^img_([0-9]+)_(.*)_(.+)+(.jpge|.jpg|.gif|.png)$/";
            if (!is_null($filename) 
                && (strlen($filename) < 1 
                    || strlen($filename) > 255 
                    || preg_match($regex, $filename) != 1
                )
            ) {
                $msg = 'Filename error - must be between 1 and 255 characters ';
                $msg .='and only .jpg|.png|.gif';
                throw new ImageException($msg);
            }
            $this->filename = $filename;
            return $this;
        }

        /**
         * Get the value of mimetype
         *
         * @return ?string
         */
        public function getMimetype(): ?string {
            return $this->mimetype;
        }

        /**
         * Set the value of mimetype
         *
         * @param ?string $mimetype
         *
         * @return self
         */
        public function setMimetype(?string $mimetype): self {
            if (!is_null($mimetype) && (strlen($mimetype) < 1 || strlen($mimetype) > 255)) {
                throw new ImageException('Image MimeType error');
            }
            $this->mimetype = $mimetype;
            return $this;
        }

        /**
         * Get the value of type
         *
         * @return ?string
         */
        public function getType(): ?string {
            return $this->type;
        }
        /**
         * Set the value of type
         *
         * @param ?string $type
         *
         * @return self
         */
        public function setType(?string $type = "S"): self {
            $type = strtoupper($type);
            $allowedTypes = ['S', 'E']; // S = ecole, E = event
            if (!is_null($type) && !in_array($type, $allowedTypes)) {
                throw new ImageException('Image Type error');
            }
            $this->type = $type;
            return $this;   
        }
        /**
         * Get the value of evenementid
         *
         * @return ?int
         */
        public function getEvenementid(): ?int {
            return $this->evenementid;
        }
        /**
         * Set the value of eventid
         *
         * @param ?int $evenementid
         *
         * @return self
         */ 
        public function setEvenementid(?int $evenementid): self {
            if (($evenementid!== null) 
            && (
                !is_numeric($evenementid) || $evenementid<= 0 
                || $evenementid > 9223372036854775807 
            )) {
                throw new ImageException('Image Event ID error');
            }
            $this->evenementid = $evenementid;
            return $this;
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
         * Set the value of ecoleid
         *
         * @param ?int $ecoleid
         *
         * @return self
         */
        public function setEcoleid(?int $ecoleid): self {
            if (($ecoleid!== null) 
            && (
                !is_numeric($ecoleid) || $ecoleid<= 0 
                || $ecoleid > 9223372036854775807 
            )) {
                throw new ImageException('Image School ID error');
            }
            $this->ecoleid = $ecoleid;
            return $this;
        }

        public function toArray() :array 
        {
            return [
                'id'        => $this->getId(),
                'title'     => $this->getTitle(),
                'filename'  => $this->getFilename(),
                'mimetype'  => $this->getMimetype(),
                'type'      => $this->getType(),
                'ecoleid'   => $this->getEcoleid(),
                'evenementid'   => $this->getEvenementid(),
                'url' => $this->url()
            ];
        }

        /**
         * Get the value of uploadFolderLocation
         *
         * @return ?string
         */
        public function getUploadFolderLocation(): ?string {
            return $this->uploadFolderLocation;
        }

        /**
         * Set the value of uploadFolderLocation
         *
         * @param ?string $uploadFolderLocation
         *
         * @return self
         */
        public function setUploadFolderLocation(?string $uploadFolderLocation): self {
            $this->uploadFolderLocation = $uploadFolderLocation;
            return $this;
        }

        public function saveImageFile($tempFileName): self
        {
            $uploadedFilePath = $this->getUploadFolderLocation()
            .DS.$this->getEcoleid().DS.$this->getFilename();

            $uploadedDir = $this->getUploadFolderLocation()
            .DS.$this->getEcoleid();

            if (!is_dir($uploadedDir)) {
                if (!mkdir($uploadedDir, 0755, true)) {
                    throw new ImageException(
                        "Failed to create image upload folder for task "
                    );
                }
            }

            if (!is_writable($uploadedDir)) {
                throw new ImageException(
                    "Upload directory is not writable: " . $uploadedDir
                );
            }

            if (!file_exists($tempFileName)) {
                throw new ImageException("Failed to upload image");
            }
    
            if (!move_uploaded_file($tempFileName, $uploadedFilePath)) {
                throw new ImageException("Failed to upload image ");
            }

            // ✅ Redimensionnement de l'image avec Gumlet après le déplacement
            try {
                $image = new ImageResize($uploadedFilePath);
                $image->crop(800, 600, true, ImageResize::CROPCENTER);
                $image->save($uploadedFilePath); // Réécrit le même fichier avec l'image redimensionnée
            } catch (ImageResizeException $e) {
                throw new ImageException(
                    "Failed to resize image: " . $e->getMessage()
                );
            }

            return $this;
        }

        public function deleteImageFile() {
            $filePath = $this->getUploadFolderLocation()
            .DS.$this->getEcoleid().DS.$this->getFilename();
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    throw new ImageException("Failed to delete image file");
                }
            }
        }

        public function renameImageFile($oldFilename, $newFilename) 
        {
            $originalFilePath = $this->abspath($this->getEcoleid(), $oldFilename);
            $renameFilePath =$this->abspath($this->getEcoleid(), $newFilename);
    
            if (!file_exists($originalFilePath)) {
                throw new ImageException("Cannot find image file to remane");
            }
    
            if(!rename($originalFilePath, $renameFilePath)) {
                throw new ImageException("Failed to update th filename");
            }
        }

        public function returnImageFile()
        {
            $filePath = $this->abspath(
                $this->getEcoleid(), 
                $this->getFilename()
            );

            if (!file_exists($filePath)) {
                throw new ImageException("Image File not found");
            }
            header('Content-Type: '.$this->getMimetype());
            header('Content-Disposition: inline; filename="'.$this->getFilename().'"');
            if (!readfile($filePath)) {
                http_response_code(404);
                exit;
            }
            exit; 
        }

        public static function fromState(array $data = []) 
        {
            return new static (
                id: $data['id']?? null,
                title:  $data['title']?? null,
                filename:  $data['filename']?? null,
                mimetype:  $data['mimetype']?? null,
                type:  $data['type']?? "S",
                ecoleid: $data['ecoleid']?? null,
                evenementid: $data['evenementid']?? null,
                location: $data['location']?? "ecoles"
            );
        }

        public function url():string
        {
            if (!$id = $this->getId()) {
                return '';
            }
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost'; // Sécurité minimale si HTTP_HOST est absent
            $ecoleId = $this->getEcoleid();
            $eventId= $this->getEvenementId();

            $uri = $this->getType() === 'E'
            ? sprintf('/api/v1/ecoles/%s/evenements/%s/images/%s', $ecoleId, $eventId, $id)
            : sprintf('/api/v1/ecoles/%s/images/%s', $ecoleId, $id);

            return sprintf('%s://%s%s', $protocol, $host, $uri);
        }

        public function abspath():string {
            $arguments = func_get_args();
            return join(
                DIRECTORY_SEPARATOR, 
                [$this->getUploadFolderLocation(), ...$arguments]
            );
        }
    }
}