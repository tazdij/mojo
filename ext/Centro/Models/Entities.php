<?php

namespace Ext\Centro\Models;

use Mojo\System\Model;

class Entities extends Model {

    protected static $_instances = [];

    public static function Inst($id='default') : Entities {
		return parent::Inst($id);
	}

    public function createEntity($context_id, $entity_type, $user_id, $status='DRAFT') {

    }

    public function getEntity($entity_id) {
        $this->db->select();
    }

}