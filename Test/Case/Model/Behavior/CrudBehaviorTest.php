<?php
App::uses('CrudBehavior', 'Crud.Model/Behavior');
App::uses('Security', 'Utility');
App::uses('Model', 'Model');
App::uses('BehaviorCollection', 'Model');

class CrudBehaviorTest extends CakeTestCase {
	public function setUp() {
		$this->Model = $this->getMock('Model', array('find', 'delete', 'save', 'create', 'validates'));
		$this->Model->name = 'Model';
		$this->Model->alias = 'Model';
		$this->Model->Behaviors = new BehaviorCollection();
		$this->Crud = new CrudBehavior();
	}

	public function tearDown() {
		unset($this->Model);
		unset($this->Crud);
		ClassRegistry::flush();
	}

	public function testValidateAndDeleteWrongId() {
		$this->Model->expects($this->once())->method('find')->will($this->ReturnValue(false));
		$this->expectException('OutOfBoundsException');
		$this->Crud->validateAndDelete($this->Model, 'invalidId', array());
	}

	public function testValidateAndDeleteWithoutConfirmation() {
		$this->expectException('RuntimeException');
		$this->Crud->validateAndDelete($this->Model, 1, array('confirm' => 0));
	}

	public function testValidateAndDelete() {
		$this->Model->expects($this->once())->method('find')->will($this->returnValue(true));
		$this->Model->expects($this->once())->method('validates')->will($this->returnValue(true));
		$this->Model->expects($this->once())->method('delete')->will($this->returnValue(true));
		$result = $this->Crud->validateAndDelete($this->Model, 1, array('confirm' => 1));
		$this->assertTrue($result);
	}

	public function testValidateAndDeleteCorrectId() {
		$this->Model->expects($this->once())->method('find')->will($this->returnValue(true));
		$this->Model->expects($this->once())->method('validates')->will($this->returnValue(true));
		$this->Model->expects($this->once())->method('delete')->with($this->equalTo(1))->will($this->returnValue(true));
		$this->Crud->validateAndDelete($this->Model, 1, array('confirm' => 1));
	}

	public function testView() {
		$expected = array('Model' => array('id' => 1));
		$this->Model->expects($this->once())->method('find')->will($this->returnValue($expected));
		$result = $this->Crud->view($this->Model, 1);
		$this->assertEqual($result, $expected);
	}

	public function testViewWrongId() {
		$this->expectException('OutOfBoundsException');
		$this->Model->expects($this->once())->method('find')->will($this->returnValue(false));
		$this->Crud->view($this->Model, 'wrong_id');
	}

	public function testAdd() {
		$data = array('Model' => array('field' => 'value'));
		$this->Model->expects($this->once())->method('save')->will($this->returnValue(array()));
		$result = $this->Crud->add($this->Model, $data);
		$this->assertTrue($result);
	}

	public function testAddInvalid() {
		$this->expectException('OutOfBoundsException');
		$this->Model->expects($this->once())->method('save')->will($this->returnValue(false));
		$this->Crud->add($this->Model, array('field' => 'value'));
	}

	public function testEditGet() {
		$expected = array('Model' => array('field' => 'value'));
		$this->Model->expects($this->once())->method('find')->will($this->returnValue($expected));
		$result = $this->Crud->edit($this->Model, 1, null);
		$this->assertEqual($result, $expected);
	}

	public function testEditInvalid() {
		$expected = array('Model' => array('field' => 'value'));
		$this->Model->expects($this->once())->method('find')->will($this->returnValue($expected));
		$this->Model->expects($this->once())->method('save')->will($this->returnValue(false));
		$result = $this->Crud->edit($this->Model, 1, $expected);
		$this->assertEqual($result, $expected);
	}

	public function testEdit() {
		$data = array('Model' => array('field' => 'value'));
		$expected = array('Model' => array('field' => 'New value'));
		$this->Model->expects($this->once())->method('find')->will($this->returnValue($data));
		$this->Model->expects($this->once())->method('save')->will($this->returnValue($expected));
		$result = $this->Crud->edit($this->Model, 1, $data);
		$this->assertEqual($this->Model->data, $expected);
	}

	public function testEditWrongId() {
		$this->expectException('OutOfBoundsException');
		$this->Model->expects($this->once())->method('find')->will($this->returnValue(false));
		$this->Crud->edit($this->Model, 'wrong_id', array('field' => 'value'));
	}
}