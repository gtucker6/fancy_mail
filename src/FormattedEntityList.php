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

        $this->user_values = $this->user->toArray();
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
    function processParamsEntity(EntityInterface $entity) {

        $entity_values = $entity->toArray();

        $params[$entity->getEntityTypeId()] = [];

        foreach($entity_values as $key => $value) {
            $entity_key = $entity->get($key)->get(0);
            if ((strpos($key, 'field_') !== false || $key === "title" || $key === "mail" || $key === "body") && $entity_key !== null) {
                $new_key = str_replace('field_', "", $key);

                if ($entity_key instanceof AddressItem) {
                    $address_obj = $entity->get($key)->get(0);
                    $new_address = "";
                    if(trim($address_obj->getAddressLine1()) !== "") {
                        $new_address .= $address_obj->getAddressLine1() . $address_obj->getAddressLine2() .", ";
                    }
                    $new_address .= $address_obj->getLocality() . ", ". $address_obj->getAdministrativeArea() . " ";
                    $new_address .= $address_obj->getPostalCode();

                    $params[$entity->getEntityTypeId()]['address'] = $new_address;

                    $aa= str_replace("US-", "",$address_obj->getAdministrativeArea());
                    $states = (new SubdivisionRepository())->getList(['US']);
                    if(array_key_exists($aa, $states)) {
                        $params[$entity->getEntityTypeId()]['state_name'] = $states[$aa];
                    }

                } else if(is_numeric($entity_key->getString()) && $entity_key instanceof EntityReferenceItem) {
                    $term = Term::load((int)$entity_key->getString())->getName();
                    $params[$entity->getEntityTypeId()][$new_key] = $term;

                } else {
                    $params[$entity->getEntityTypeId()][$new_key] = $entity_key->getString();

                }
            }
        }

        if($entity instanceof UserInterface) {
            if($entity->hasRole('administrator')){
                $params['user']['role'] = "Admin";
            }
        }

        return $params[$entity->getEntityTypeId()];

    }

    public function getOriginalDifferenceKeys() {
        $differences = [];
        if(isset($this->entity->original)) {
            if(count($this->entity_values) >= count($this->entity->original->toArray())) {
                $key_array = $this->entity_values;
            } else {

                $key_array = $this->entity->original->toArray();
            }

            foreach($key_array as $key => $value) {
                $o_value = $this->entity->original->toArray()[$key][0];
                $n_value = $this->entity->toArray()[$key][0];

               if(!isset($o_value['attributes']) && isset($n_value['attributes'])) {
                    $o_value['attributes'] = [];
                }

                if((strpos($key, "field_") !== false || $key === "title" || $key === "body") && $o_value !== $n_value) {
                    $value_type = gettype($value[0]);
                    $new_key = str_replace("field_", "", $key);
                    if($value_type === "array") {
                        foreach($value[0] as $sub_key => $sub_value) {
                            if($o_value[$sub_key] !== $n_value[$sub_key]) {
                                $differences[$new_key] = $this->entity->get($key)->getString();
                            }
                        }
                    } else {
                        $differences[$new_key] = $n_value;
                    }
                }
            }
        }
        return $differences;
    }
}
