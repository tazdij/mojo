<?php

namespace Ext\Centro\Models;

use Mojo\System\Model;
use Mojo\Data\Mysql\DBError;
use Mojo\Data\Mysql\Orm;

class Contexts extends Model {
    

    // Required for own instances in multiton
    protected static $_instances = [];

    public static function Inst($id='default') : Contexts {
		return parent::Inst($id);
	}

    public function Create(
        $domain,
        $name,
        $title,
        $priority=NULL,
        $theme=NULL,
        $description=NULL
    ) {
        $context_id = Model::GenerateBinGUID();

        // Get the next priority
        if ($priority == NULL) {
            $priority = $this->getNextPriority();
        }

        try {
            $res = $this->db->insert('context')->set([
                'Description' => $description ?? '',
                'Name' => $name,
                'Domain' => $domain,
                'Theme' => $theme,
                'Title' => $title,
                'Priority' => $priority,
                'ContextID' => Orm::Bin($context_id)
            ])->exec();
        } catch (\Exception $e) {
            $err = new DBError($e->getMessage(), $e->getCode() ?? 500);
            return $err;
        }
        

         var_dump($res);
        return bin2hex($context_id);
    }

    public function getAll() {
        $rs = $this->db->select(DB_ALL)->from('context')->exec();
        return $rs;
    }

    public function getNextPriority() {
        $rs = $this->db->select(['Priority'])->from('context')->orderBy('Priority', DB_DESC)->limit(1)->exec();
        if (count($rs) > 0) {
            return (int)$rs[0]['Priority'] + 1;
        } else {
            return 1;
        }
    }

}