<?php
/**An Entity List with user properties*/

namespace Drupal\fancy_mail;

use Drupal\address\Plugin\Field\FieldType\AddressItem;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Field\FieldItemListInterface;

class FormattedEntityList {

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var array|mixed[]
     */

    protected $entity_values;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var array|mixed[]
     */

    protected $user_values;

    /**
     * @var $params
     */

    protected $params = [];

    public function __construct(EntityInterface $entity){
        if($entity instanceof EntityOwnerInterface && !$entity->getEntityType()->isRevisionable()){
            $this->user = $entity->getOwner();
        } else if($entity->getEntityType()->isRevisionable()) {
            $this->user = $entity->getRevisionUser();
        } else {
            $this->user = null;
        }

        if(!empty($this->user)) {
          $this->user_values = $this->user->toArray();
        }

        $this->entity = $entity;
        $this->entity_values = $entity->toArray();
    }

    public function getAllParams()
    {
        $params[$this->entity->getEntityTypeId()] = $this->processParamsEntity($this->entity);
        if($this->user !== null) {
            $params['user'] = $this->processParamsEntity($this->user);
        }

        if($this->entity instanceof NodeInterface) {
            $params['node_type'] = ucwords(str_replace("_", " ", $this->entity->getType()));
        }

        $this->params = $params;

        return $this->params;
    }

    public function processParamsEntity(EntityInterface $entity) {

      if($entity instanceof UserInterface) {
        $params['user']['role'] = implode(',', $entity->getRoles());
        $params['user']['email'] = $entity->getEmail();
      } else {
        //loop through entity keys
        $entity_values = array_keys($entity->toArray());

        $params[$entity->getEntityTypeId()] = [];

        foreach ($entity_values as $key) {
          $field_item_list = $entity->get($key);
          if (strpos($key, 'field_') !== false || $key === "title" || $key === "mail" || $key === "body") {
            if ($field_item_list instanceof FieldItemListInterface) {
              $new_key = str_replace('field_', "", $key);
              // attach render array view of field_item_list (will view default if mail_list is not found)
              $params[$entity->getEntityTypeId()][$new_key] = $field_item_list->view('mail_list');
            }
          }
        }
      }
        return $params[$entity->getEntityTypeId()];

    }
}
