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
    use App\Exception\ImageException;

    final class Image
    {
        protected readonly ?int $id;
        protected ?string $title;
        protected ?string $filename;
        protected ?string $mimetype;
        protected ?int $ecoleid;
        protected ?string $url;

        protected ?string $uploadFolderLocation;
     

        public function __construct
        (
            ?int $id, ?string $title, ?string $filename,
            ?string $mimetype,?int $ecoleid = null, ?string $url = null
        ) {
            $this
                ->setId($id)
                ->setTitle($title)
                ->setFilename($filename)
                ->setMimetype($mimetype)
                ->setEcoleid($ecoleid)
                ->setUploadFolderLocation(
                    \App\path('public', 'uploads', 'ecoles')
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
                'ecoleid'   => $this->getEcoleid(),
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

        public function saveImageFile($tempFileName) 
        {
            $uploadedFilePath = $this->getUploadFolderLocation()
            .DS.$this->getEcoleid().DS.$this->getFilename();

            $uploadedDir = $this->getUploadFolderLocation()
            .DS.$this->getEcoleid();

            if (!is_dir($uploadedDir)) {
                if (!mkdir($uploadedDir)) {
                    throw new ImageException("Failed to create image upload folder for task ");
                }
            }

            if (!file_exists($tempFileName)) {
                throw new ImageException("Failed to upload image");
            }
    
            if (!move_uploaded_file($tempFileName, $uploadedFilePath)) {
                throw new ImageException("Failed to upload image ");
            }
    
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
                ecoleid: $data['ecoleid']?? null
            );
        }

        public function url():string
        {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            if (!$this->getId()) {
                return "";
            }
           $uri = sprintf("/api/v1/ecoles/%s/images/%s", $this->getEcoleid(), $this->getId());
           $url = $protocol . "://" . $host . $uri;
            return $url;
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