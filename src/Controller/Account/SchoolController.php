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

class SchoolController extends \App\Controller\Controller
{
    public function indexAction(Request $request, Response $response, $args): Response
    {
        // Sample data (e.g., from DB)
        $items = range(1, 100); // Example: 100 items
        $perPage = 6;
        $total = count($items);
        // Get current page from URL query (?page=2)
        $page = isset($args['page']) ? (int)$args['page'] : 1;
        $page = max(1, $page); // At least 1
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        // Get items for current page
        $paginatedItems = array_slice($items, $offset, $perPage);
        // Calculate total pages
        $pageCount = ceil($total / $perPage);

        return $this->render(
            $response, 'account/school/index.html.twig', [
            'hasContentHeader' => false, 
            'hasFooter' => false, 
            'items' => $paginatedItems,
            'pagination' => [
                'currentPage' => $page,
                'pageCount' => $pageCount,
                'previousPage' => $page > 1 ? $page - 1 : null,
                'nextPage' => $page < $pageCount ? $page + 1 : null
            ]
        ]);
    }

    public function addAction(Request $request, Response $response, $args): Response
    {
        $v = $this->validator;
        if ($v->method()) {
            $v->validate([
                "nom" => function() use ($v) { $v->isRequired()->get();},
                "email" => function() use ($v) {$v->get();},
                "telephone" => function() use ($v) { $v->get(); },
                "type" => function() use ($v) { $v->get(); },
                "site" => function() use ($v) {$v->get(); },
                "voie" => function() use ($v) {$v->isRequired()->get(); },
                "quartier" => function() use ($v) {$v->isRequired()->get(); },
                "commune" => function() use ($v) {$v->isRequired()->get(); },
                "district" => function() use ($v) {$v->isRequired()->get(); },
                "ville" => function() use ($v) {$v->isRequired()->get(); },
                "reference" => function() use ($v) {$v->get(); },
                "title" => function() use ($v) {$v->isRequired()->get(); },
                "filename" => function() use ($v) {$v->isRequired()->get(); },
                "logo" => function() use ($v) {$v->file()->isRequired()->get(); },
            ]);
            if ($v->failed()) {
                $this->addErrors($v->errors());
                return $this->redirectTo($request, $response, "account.add_ecole");
            } else {
                try {
                    // Establish the connection Database
                    $connexionWrite = Connexion::write();
                    $repository = new SchoolRepository($connexionWrite);
                    $results = $v->results();
                    $school  = School::fromState($results);
                    $address = Address::fromState($results);
                    $school->setAdresses(adresses: [$address->toArray()]);

                    $uploadedFile= current($results['logo']);
                    $title =  $results['title']?? null;
                    $repository->retrieveByName(nom: $school->getNom());
                    $rowCounted = $repository->getTempRowCounted();

                    if ($rowCounted !== 0) {
                        $this->addFlashMessage('danger', "L'école existe déjà");
                        return $this->redirectTo($request, $response, "account.add_ecole");
                    }
                    // Start Transaction
                    $repository->beginTransaction();
                    // Add new Ecole
                    $repository->add(school: $school);
                    $rowCounted = $repository->getTempRowCounted();
                    if ($rowCounted === 0) {
                        $this->addFlashMessage('danger', "Échec de la création de l'école");
                        return $this->redirectTo($request, $response, "account.add_ecole");
                    }
                    $school_id = (int) $repository->getStockId();
                    $rowCounted = $repository->getTempRowCounted();
                    if ($rowCounted === 0) {
                        $this->addFlashMessage(
                            'danger', 
                            "Échec de la récupération de l'école après la création"
                        );
                        return $this->redirectTo($request, $response, "account.add_ecole");
                    }
                    $extension = $this->extensionFile(
                        $uploadedFile->getClientFilename()
                    );
                    $uniquedFile = $this->imageFileUnique($school_id, "logo");
                    $newFilename = $uniquedFile.'.'. $extension;
                    $image = new Image(
                        id: null,
                        title: $title,
                        filename: $newFilename,
                        mimetype: $uploadedFile->getMimeTypeFromFile(),
                        ecoleid: $school_id,
                        evenementid: null
                    );
                    if (!is_null($school->getMaximage()) && $school->isMaximunImage()) {
                        if ($repository->inTransaction()) {
                            $repository->rollBack();
                        }
                        $msg = "Vous ne pouvez pas ajouter cette image, ";
                        $msg.= "le nombre maximum d'images a été atteint.";
                        $this->addFlashMessage('danger', $msg);
                        return $this->redirectTo($request, $response, "account.add_ecole");
                    }

                    // Création image
                    $repository->addImage(image: $image);
                    $rowCounted = $repository->rowCount();
                    if ($rowCounted === 0) {
                        if ($repository->inTransaction()) {
                            $repository->rollBack();
                        }
                        $this->addFlashMessage(
                            'danger', 
                            "Échec de la création d'une image"
                        );
                        return $this->redirectTo($request, $response, "account.add_ecole");
                    }
                    $school = new School(
                        id: $school_id,
                        nom: $school->getNom(),
                        email: $school->getEmail(),
                        telephone: $school->getTelephone(),
                        type: $school->getType(),
                        adresses: $school->getAdresses(),
                    );
                    $maxima = 1 + intval($school->getMaximage());
                    $school->setMaximage(maximage: $maxima);
                    $repository->update(school: $school);

                    $tmpName = $uploadedFile->getStream()->getMetadata('uri');
                    $image->saveImageFile($tmpName); 
                    $repository->commit();
                    $this->addFlashMessage('success', "Ajout de l'école avec succès");
                    return $this->redirectTo($request, $response, 
                        "account.show_ecole", ['id' => $school_id]
                    );
                    
                } catch (SchoolException | AddressException | ImageException $e) {
                    $this->addFlashMessage('danger', $e->getMessage());
                    return $this->redirectTo(
                        $request, $response, 
                        "account.add_ecole",
                    );
                }
            }
        }
        return $this->render(
            $response, 'account/school/add.html.twig', [
            'hasContentHeader' => false, 
            'hasFooter' => false
        ]);

    }

    public function showAction(Request $request, Response $response, $args): Response
    {
        $id = (int) $args['id']?? 0;
        if ($id <= 0) {
            $this->addFlashMessage('danger', "L'école n'existe pas");
            return $this->redirectTo($request, $response, "account.liste_ecole");
        }
        return $this->render(
            $response, 'account/school/show.html.twig', [
            'school_id' => $id,
            'hasContentHeader' => false, 
            'hasFooter' => false
        ]);
    }

    public function editAction(Request $request, Response $response, $args): Response
    {
        $id = (int) $args['id']?? 0;
        if (!isset($id) || $id <= 0) {
            $this->addFlashMessage('danger', "L'identifiant de l'école est invalide.");
            return $this->redirectTo($request, $response, "account.liste_ecole");
        }
        $connexionRead = Connexion::read();
        $repository = new SchoolRepository($connexionRead);
        $schoolRow =  $repository->retrieve(id: $id);
        $school = current($schoolRow);

        $v = $this->validator;
        if ($v->method()) {
            $v->validate([
                "id" => function() use ($v) { $v->isRequired()->get(); },
                "nom" => function() use ($v) { $v->isRequired()->get();},
                "email" => function() use ($v) {$v->get();},
                "telephone" => function() use ($v) { $v->get(); },
                "type" => function() use ($v) { $v->get(); },
                "site" => function() use ($v) {$v->get(); },
                "adresse_id" => function() use ($v) { $v->isRequired()->get(); },
                "voie" => function() use ($v) {$v->isRequired()->get(); },
                "quartier" => function() use ($v) {$v->isRequired()->get(); },
                "commune" => function() use ($v) {$v->isRequired()->get(); },
                "district" => function() use ($v) {$v->isRequired()->get(); },
                "ville" => function() use ($v) {$v->isRequired()->get(); },
                "reference" => function() use ($v) {$v->get(); }
            ]);
            if ($v->failed()) {
                $this->addErrors($v->errors());
                return $this->redirectTo($request, $response, "account.edit_ecole", ['id' => $id]);
            } else {
                try {

                    $results = $v->results();
                    $school  = School::fromState($results);
                
                    $address = new Address(
                        id: (int) $results['adresse_id'],
                        voie: $results['voie'],
                        quartier: $results['quartier'],
                        commune: $results['commune'],
                        district: $results['district'],
                        ville: $results['ville'],
                        reference: $results['reference'],
                        ecoleid: $school->getId()
                    );
                    $school->setAdresses(adresses: [$address->toArray()]);

                    // Start Transaction
                    $repository->beginTransaction();
                    // Update Ecole
                    $repository->update(school: $school);
                    // Update Address
                    $repository->updateAddress(address: $address);
                    $repository->commit();
                    $this->addFlashMessage('success', "Ajout de l'école avec succès");
                    return $this->redirectTo($request, $response, "account.show_ecole", ['id' => $id]);
                    
                } catch (SchoolException | AddressException | ImageException $e) {
                    $this->addFlashMessage('danger', $e->getMessage());
                    return $this->redirectTo($request, $response, "account.edit_ecole", ['id' => $id]);
                }
            }
        }

        return $this->render(
            $response, 'account/school/edit.html.twig', [
            'school' => $school,
            'hasContentHeader' => false, 
            'hasFooter' => false
        ]);
    }

    public function deleteAction(Request $request, Response $response, $args): Response
    {
        $id = (int) $args['id']?? 0;
        return $this->render(
            $response, 'account/school/delete.html.twig', [
            'hasContentHeader' => false, 
            'hasFooter' => false
        ]);
    }
}