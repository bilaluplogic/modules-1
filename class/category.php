<?php
    sys::import('modules.dynamicdata.class.objects.base');

    class Category extends DataObject
    {
        public $parentindices = array();

        function createItem(Array $args = array())
        {
            extract($args);

            // there may not be an entry point passed
            $entry = isset($entry) ? $entry : array();

            if (isset($args['parent_id'])) {
            	// If this is an import replace parentid imported with the local ones
				$parentindex = $args['parent_id'];
				if (in_array($parentindex,array_keys($this->parentindices))) {
					$args['parent_id'] = $this->parentindices[$parentindex];
				} else {
					// there could be more than 1 entry point, therefore the array
					if (count($entry > 0)) {
						$this->parentindices[$parentindex] = array_shift($entry);
						$args['parent_id'] = $this->parentindices[$parentindex];
					} else {
						$args['parent_id'] = 0;
					}
				}
				$args['left_id'] = null;
				$args['right_id'] = null;
			}

            // we have all the values, do it
            $id = parent::createItem($args);

            // add this category to the list of known parents
            if (isset($args['parent_id'])) $this->parentindices[$args['id']] = $id;

            // do the Celko dance and update all the left/right values
            return xarModAPIFunc('categories','admin','updatecelkolinks',array('cid' => $id, 'type' => 'create'));
        }

        function updateItem(Array $args = array())
        {
            $id = isset($args['itemid']) ? $args['itemid'] : $this->itemid;
            $this->getItem(array('itemid' => $id));
            $old_parentid = $this->properties['cat_parent']->value;
            $id = parent::updateItem($args);

            list($isvalid,$new_parentid) = $this->properties['cat_parent']->fetchValue();
            // CHECKME: do we need to bail if isvalid not true?

            // only update Celko if the parent has changed
            if (($old_parentid - $new_parentid) != 0) {
				return xarModAPIFunc('categories','admin','updatecelkolinks',array('cid' => $id));
            } else {
				return true;
            }
        }
    }
?>