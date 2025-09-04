<?php

namespace App\Controller\Account;


use App\Database\Connexion;
use App\Repository\SchoolRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Model\School;
use App\Model\Address;
use App\Model\Image;
use App\Exception\SchoolException;
use App\Exception\AddressException;
use App\Exception\ImageException;


class ImageController extends \App\Controller\Controller
{
    public function addAction(Request $request, Response $response, $args): Response
    {
        $id = (int) $args['id']?? 0;

        if (!isset($id) || $id <= 0) {
            $this->addFlashMessage('danger', "L'identifiant de l'école est invalide.");
            return $this->redirectTo($request, $response, "account.liste_ecole");
        }

        $v = $this->validator;
        $connexionRead = Connexion::read();
        $repository = new SchoolRepository($connexionRead);
        $schoolRows =  $repository->retrieve(id: $id);
        $schoolRow = current($schoolRows);
        $school = School::fromState($schoolRow);

        if ($v->method()) {
            $v->validate([
                "title" => function() use ($v) {$v->isRequired()->get(); },
                "fileimage" => function() use ($v) { $v->file()->isRequired()->get(); },
            ]);
            if ($v->failed()) {
                $this->addErrors($v->errors());
                return $this->redirectTo($request, $response, "account.add_image");
            } else {
                try {
                    $uploadedFile= current($v->results()['fileimage']);
                    $title =  $v->results()['title']?? null;
                    $extension = $this->extensionFile(
                        $uploadedFile->getClientFilename()
                    );
                    $fileName = $this->getFileName($uploadedFile->getClientFilename());
                    $uniquedFile = $this->imageFileUnique($id, $fileName);
                    $newFilename = $uniquedFile.'.'. $extension;

                    $image = new Image(
                        id: null,
                        title: html_entity_decode($title),
                        filename: $newFilename,
                        mimetype: $uploadedFile->getMimeTypeFromFile(),
                        ecoleid: $id
                    );
                    if (!is_null($school->getMaximage()) && $school->isMaximunImage()) {
                        $msg = "Vous ne pouvez pas ajouter cette image, ";
                        $msg.= "le nombre maximum d'images a été atteint.";
                        $this->addFlashMessage('danger', $msg);
                        return $this->redirectTo(
                            $request, $response, "account.add_image",
                            ['id' => $id]
                        );
                    }
                    // Ajout une image
                    $repository->addImage($image);
                    $maxima= 1 + intval($school->getMaximage());
                    $school->setMaximage(maximage: $maxima);
                    $repository->update(school: $school);
                    $tmpName = $uploadedFile->getStream()->getMetadata('uri');
                    $image->saveImageFile($tmpName); 
                   
                    $this->addFlashMessage('success', "Ajout de l'image avec succès");
                     return $this->redirectTo($request, $response, 
                        "account.show_ecole", ['id' => $id]
                    );
                } catch (ImageException $e) {
                    $this->addFlashMessage('danger', $e->getMessage());
                    return $this->redirectTo($request, $response, "account.add_image");
                }
            }
        }

        return $this->render(
            $response, 'account/image/add.html.twig', [
            'hasContentHeader' => false, 
            'hasFooter' => false,
            'school' => $school
        ]);
    }

    public function deleteAction()
    {
        // Logic for deleting an image
        die('Image deletion logic goes here');
    }
}