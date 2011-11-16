<?php
App::import('Behavior', 'Crud.Crud');
App::import('Core', 'Security');

Mock::generatePartial('Model', 'NotificationBehaviorTestMockModel', array('find', 'delete', 'save', 'create'));

class CrudBehaviorTestCase extends CakeTestCase {
	public function startTest($method) {
		parent::startTest($method);
		$this->Model = new NotificationBehaviorTestMockModel();
		$this->Model->name = 'Model';
		$this->Model->alias = 'Model';
		$this->Model->Behaviors = new BehaviorCollection();
		$this->Crud = new CrudBehavior();
	}

	public function endTest($method) {
		parent::endTest($method);
		unset($this->Model);
		unset($this->Crud);
		ClassRegistry::flush();
	}

	public function testValidateAndDeleteWrongId() {
		$this->Model->setReturnValue('find', false);
		$this->expectException('OutOfBoundsException');
		$this->Crud->validateAndDelete($this->Model, 'invalidId', array());
	}

	public function testValidateAndDeleteWithoutConfirmation() {
		$this->expectException('Exception');
		$this->Crud->validateAndDelete($this->Model, 1, array('confirm' => 0));
	}

	public function testValidateAndDelete() {
		$this->Model->setReturnValue('find', true);
		$this->Model->setReturnValue('delete', true);
		$result = $this->Crud->validateAndDelete($this->Model, 1, array('confirm' => 1));
		$this->assertTrue($result);
	}

	public function testValidateAndDeleteCorrectId() {
		$this->Model->setReturnValue('find', true);
		$this->Model->setReturnValue('delete', true);
		$this->Model->expectOnce('delete', array(1));
		$this->Crud->validateAndDelete($this->Model, 1, array('confirm' => 1));
	}

	public function testView() {
		$expected = array('Model' => array('id' => 1));
		$this->Model->setReturnValue('find', $expected);
		$result = $this->Crud->view($this->Model, 1);
		$this->assertEqual($result, $expected);
	}

	public function testViewWrongId() {
		$this->expectException('OutOfBoundsException');
		$this->Model->setReturnValue('find', false);
		$this->Crud->view($this->Model, 'wrong_id');
	}

	public function testAdd() {
		$data = array('Model' => array('field' => 'value'));
		$this->Model->setReturnValue('save', array());
		$result = $this->Crud->add($this->Model, $data);
		$this->assertTrue($result);
	}

	public function testAddInvalid() {
		$this->expectException('OutOfBoundsException');
		$this->Model->setReturnValue('save', false);
		$this->Crud->add($this->Model, array('field' => 'value'));
	}

	public function testEditGet() {
		$expected = array('Model' => array('field' => 'value'));
		$this->Model->setReturnValue('find', $expected);
		$result = $this->Crud->edit($this->Model, 1, null);
		$this->assertEqual($result, $expected);
	}

	public function testEditInvalid() {
		$expected = array('Model' => array('field' => 'value'));
		$this->Model->setReturnValue('find', $expected);
		$this->Model->setReturnValue('save', false);
		$this->Model->expectOnce('save');
		$result = $this->Crud->edit($this->Model, 1, $expected);
		$this->assertEqual($result, $expected);
	}

	public function testEdit() {
		$data = array('Model' => array('field' => 'value'));
		$expected = array('Model' => array('field' => 'New value'));
		$this->Model->setReturnValue('find', $data);
		$this->Model->setReturnValue('save', $expected);
		$result = $this->Crud->edit($this->Model, 1, $data);
		$this->assertEqual($this->Model->data, $expected);
	}

	public function testEditWrongId() {
		$this->expectException('OutOfBoundsException');
		$this->Model->setReturnValue('find', false);
		$this->Crud->edit($this->Model, 'wrong_id', array('field' => 'value'));
	}
}