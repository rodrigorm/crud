<?php
App::uses('ModelBehavior', 'Model');

class CrudBehavior extends ModelBehavior {
	public function view($Model, $id = null) {
		return $this->_read($Model, $id);
	}

	public function validateAndDelete($Model, $id = null, $data = array()) {
		$item = $this->_read($Model, $id);

		if (empty($data)) {
			return $item;
		}

		$Model->set($item);

		$Model->validate = array(
			'id' => array('rule' => 'notEmpty'),
			'confirm' => array('rule' => '[1]', 'allowEmpty' => false, 'required' => true)
		);

		$Model->set($data);
		$Model->set($Model->primaryKey, $id);

		if ($Model->validates() && $Model->delete($id)) {
			return true;
		}
		throw new RuntimeException(sprintf(__('You need to confirm to delete this %s'), $this->__humanizedAlias($Model)));
	}

	protected function _read($Model, $id) {
		$item = $Model->find('first', array(
			'conditions' => array(
				"{$Model->alias}.{$Model->primaryKey}" => $id,
			)
		));

		if (empty($item)) {
			throw new OutOfBoundsException(sprintf(__('Invalid %s'), $this->__humanizedAlias($Model)));
		}

		return $item;
	}

	public function add($Model, $data = null, $validate = true, $fieldList = array()) {
		if (empty($data)) {
			return;
		}

		$Model->create($data, true);
		$result = $Model->save($Model->data, $validate, $fieldList);

		if ($result !== false) {
			$Model->data = array_merge($data, $result);
			return true;
		} else {
			throw new OutOfBoundsException(sprintf(__('Could not save the %s, please check your inputs.'), $this->__humanizedAlias($Model)));
		}
	}

	private function __humanizedAlias($Model) {
		return Inflector::humanize(Inflector::underscore($Model->alias));
	}

	public function edit($Model, $id = null, $data = null) {
		$item = $this->_read($Model, $id);

		if (empty($data)) {
			return $item;
		}

		$Model->set($item);
		$Model->set($data);

		$result = $Model->save(null, true);

		if ($result) {
			$Model->data = $result;
			return true;
		} else {
			return $data;
		}
	}
}