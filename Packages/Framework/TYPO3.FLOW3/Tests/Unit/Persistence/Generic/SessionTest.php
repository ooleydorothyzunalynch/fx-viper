<?php
namespace TYPO3\FLOW3\Tests\Unit\Persistence\Generic;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for the Persistence Session
 *
 */
class SessionTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function objectRegisteredWithRegisterReconstitutedEntityCanBeRetrievedWithGetReconstitutedEntities() {
		$someObject = new \ArrayObject(array());
		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->registerReconstitutedEntity($someObject, array('identifier' => 'fakeUuid'));

		$ReconstitutedEntities = $session->getReconstitutedEntities();
		$this->assertTrue($ReconstitutedEntities->contains($someObject));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function unregisterReconstitutedEntityRemovesObjectFromSession() {
		$someObject = new \ArrayObject(array());
		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->registerObject($someObject, 'fakeUuid');
		$session->registerReconstitutedEntity($someObject, array('identifier' => 'fakeUuid'));
		$session->unregisterReconstitutedEntity($someObject);

		$ReconstitutedEntities = $session->getReconstitutedEntities();
		$this->assertFalse($ReconstitutedEntities->contains($someObject));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function hasObjectReturnsTrueForRegisteredObject() {
		$object1 = new \stdClass();
		$object2 = new \stdClass();
		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->registerObject($object1, 12345);

		$this->assertTrue($session->hasObject($object1), 'Session claims it does not have registered object.');
		$this->assertFalse($session->hasObject($object2), 'Session claims it does have unregistered object.');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function hasIdentifierReturnsTrueForRegisteredObject() {
		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->registerObject(new \stdClass(), 12345);

		$this->assertTrue($session->hasIdentifier('12345'), 'Session claims it does not have registered object.');
		$this->assertFalse($session->hasIdentifier('67890'), 'Session claims it does have unregistered object.');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getIdentifierByObjectReturnsRegisteredUUIDForObject() {
		$object = new \stdClass();
		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->registerObject($object, 12345);

		$this->assertEquals($session->getIdentifierByObject($object), 12345, 'Did not get UUID registered for object.');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getObjectByIdentifierReturnsRegisteredObjectForUUID() {
		$object = new \stdClass();
		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->registerObject($object, 12345);

		$this->assertSame($session->getObjectByIdentifier('12345'), $object, 'Did not get object registered for UUID.');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function unregisterObjectRemovesRegisteredObject() {
		$object1 = new \stdClass();
		$object2 = new \stdClass();
		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->registerObject($object1, 12345);
		$session->registerObject($object2, 67890);

		$this->assertTrue($session->hasObject($object1), 'Session claims it does not have registered object.');
		$this->assertTrue($session->hasIdentifier('12345'), 'Session claims it does not have registered object.');
		$this->assertTrue($session->hasObject($object1), 'Session claims it does not have registered object.');
		$this->assertTrue($session->hasIdentifier('67890'), 'Session claims it does not have registered object.');

		$session->unregisterObject($object1);

		$this->assertFalse($session->hasObject($object1), 'Session claims it does have unregistered object.');
		$this->assertFalse($session->hasIdentifier('12345'), 'Session claims it does not have registered object.');
		$this->assertTrue($session->hasObject($object2), 'Session claims it does not have registered object.');
		$this->assertTrue($session->hasIdentifier('67890'), 'Session claims it does not have registered object.');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function newObjectsAreConsideredDirty() {
		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$this->assertTrue($session->isDirty(new \stdClass(), 'foo'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function isDirtyReturnsTrueForUnregisteredReconstitutedEntities() {
		$session = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('isReconstitutedEntity'));
		$session->expects($this->once())->method('isReconstitutedEntity')->will($this->returnValue(FALSE));
		$this->assertTrue($session->isDirty(new \stdClass(), 'foo'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function isDirtyReturnsFalseForNullInBothCurrentAndCleanValue() {
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $foo; }');
		$object = new $className();

		$session = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('getIdentifierByObject'));
		$session->registerReconstitutedEntity($object, array('identifier' => 'fakeUuid'));
		$session->expects($this->once())->method('getIdentifierByObject')->will($this->returnValue('fakeUuid'));

		$this->assertFalse($session->isDirty($object, 'foo'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function isDirtyAsksIsPropertyDirtyForChangedLiterals() {
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $foo; }');
		$object = new $className();
		$object->foo = 'different';

		$cleanData = array(
			'identifier' => 'fakeUuid',
			'properties' => array(
				'foo' => array(
					'type' => 'string',
					'multivalue' => FALSE,
					'value' => 'bar'
				)
			)
		);
		$session = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('getIdentifierByObject', 'isSingleValuedPropertyDirty'));
		$session->registerReconstitutedEntity($object, $cleanData);
		$session->expects($this->once())->method('getIdentifierByObject')->will($this->returnValue('fakeUuid'));
		$session->expects($this->once())->method('isSingleValuedPropertyDirty')->with('string', 'bar', 'different')->will($this->returnValue(TRUE));

		$this->assertTrue($session->isDirty($object, 'foo'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function isDirtyReturnsFalseForUnactivatedLazyObjects() {
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $foo; }');
		$object = new $className();
		$object->FLOW3_Persistence_LazyLoadingObject_thawProperties = 'dummy';

		$session = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('dummy'));
		$session->registerReconstitutedEntity($object, array('identifier' => 'fakeUuid'));
		$this->assertFalse($session->isDirty($object, 'foo'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function isDirtyReturnsTrueForTraversablesWhoseCountDiffers() {
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $foo; }');
		$object = new $className();
		$object->foo = array('foo', 'bar', 'baz');

		$cleanData = array(
			'identifier' => 'fakeUuid',
			'properties' => array(
				'foo' => array(
					'type' => 'string',
					'multivalue' => TRUE,
					'value' => array(array(), array())
				)
			)
		);
		$session = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('getIdentifierByObject'));
		$session->registerReconstitutedEntity($object, $cleanData);
		$session->expects($this->once())->method('getIdentifierByObject')->will($this->returnValue('fakeUuid'));

		$this->assertTrue($session->isDirty($object, 'foo'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function isDirtyReturnsTrueForNestedArrayWhoseCountDiffers() {
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $foo; }');
		$object = new $className();
		$object->foo = array('foo', array('bar', 'baz'));

		$cleanData = array(
			'identifier' => 'fakeUuid',
			'properties' => array(
				'foo' => array(
					'type' => 'string',
					'multivalue' => TRUE,
					'value' => array(
						array('type' => 'string', 'index' => 0, 'value' => 'foo'),
						array(
							'type' => 'array',
							'index' => 1,
							'value' => array('type' => 'string', 'index' => 0, 'value' => 'bar'),
						)
					)
				)
			)
		);
		$session = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('getIdentifierByObject'));
		$session->registerReconstitutedEntity($object, $cleanData);
		$session->expects($this->once())->method('getIdentifierByObject')->will($this->returnValue('fakeUuid'));

		$this->assertTrue($session->isDirty($object, 'foo'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function isDirtyReturnsTrueForSplObjectStorageWhoseContainedObjectsDiffer() {
		$object = new \stdClass();
		$object->FLOW3_Persistence_Identifier = 'dirtyUuid';
		$splObjectStorage = new \SplObjectStorage();
		$splObjectStorage->attach($object);
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $splObjectStorage; }');
		$parent = new $className();
		$parent->splObjectStorage = $splObjectStorage;

		$cleanData = array(
			'identifier' => 'fakeUuid',
			'properties' => array(
				'splObjectStorage' => array(
					'type' => 'SplObjectStorage',
					'multivalue' => TRUE,
					'value' => array(
						array(
							'value' => array('identifier' => 'cleanUuid')
						)
					)
				)
			)
		);
		$session = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('getIdentifierByObject'));
		$session->registerReconstitutedEntity($parent, $cleanData);
		$session->expects($this->atLeastOnce())->method('getIdentifierByObject')->will($this->returnValue('fakeUuid'));

		$this->assertTrue($session->isDirty($parent, 'splObjectStorage'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function isDirtyReturnsTrueForArraysWhoseContainedObjectsDiffer() {
		$object = new \stdClass();
		$object->FLOW3_Persistence_Identifier = 'dirtyUuid';
		$array = array();
		$array[] = $object;
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $array; }');
		$parent = new $className();
		$parent->array = $array;

		$cleanData = array(
			'identifier' => 'fakeUuid',
			'properties' => array(
				'array' => array(
					'type' => 'array',
					'multivalue' => TRUE,
					'value' => array(
						array(
							'type' => 'Some\Object',
							'index' => 0,
							'value' => array('identifier' => 'cleanUuid')
						)
					)
				)
			)
		);
		$session = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('getIdentifierByObject', 'isSingleValuedPropertyDirty'));
		$session->registerReconstitutedEntity($parent, $cleanData);
		$session->expects($this->once())->method('getIdentifierByObject')->will($this->returnValue('fakeUuid'));
		$session->expects($this->once())->method('isSingleValuedPropertyDirty')->will($this->returnValue(TRUE));

		$this->assertTrue($session->isDirty($parent, 'array'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function isDirtyReturnsFalseForCleanArrays() {
		$object = new \stdClass();
		$object->FLOW3_Persistence_Identifier = 'cleanHash';
		$array = array();
		$array[] = $object;
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $array; }');
		$parent = new $className();
		$parent->array = $array;

		$cleanData = array(
			'identifier' => 'fakeUuid',
			'properties' => array(
				'array' => array(
					'type' => 'array',
					'multivalue' => TRUE,
					'value' => array(
						array(
							'type' => 'Some\Object',
							'index' => 0,
							'value' => array('identifier' => 'cleanHash')
						)
					)
				)
			)
		);
		$session = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('getIdentifierByObject', 'isSingleValuedPropertyDirty'));
		$session->registerReconstitutedEntity($parent, $cleanData);
		$session->expects($this->once())->method('getIdentifierByObject')->will($this->returnValue('fakeUuid'));
		$session->expects($this->once())->method('isSingleValuedPropertyDirty')->with('Some\Object', array('identifier' => 'cleanHash'), $object)->will($this->returnValue(FALSE));

		$this->assertFalse($session->isDirty($parent, 'array'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function isDirtyReturnsFalseForCleanNestedArrays() {
		$object = new \stdClass();
		$object->FLOW3_Persistence_Identifier = 'cleanHash';
		$array = array(array($object));
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $array; }');
		$parent = new $className();
		$parent->array = $array;

		$cleanData = array(
			'identifier' => 'fakeUuid',
			'properties' => array(
				'array' => array(
					'type' => 'array',
					'multivalue' => TRUE,
					'value' => array(
						array(
							'type' => 'array',
							'index' => 0,
							'value' => array(
								array(
									'type' => 'Some\Object',
									'index' => 0,
									'value' => array('identifier' => 'cleanHash')
								),
							)
						)
					)
				)
			)
		);
		$session = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('getIdentifierByObject', 'isSingleValuedPropertyDirty'));
		$session->registerReconstitutedEntity($parent, $cleanData);
		$session->expects($this->once())->method('getIdentifierByObject')->will($this->returnValue('fakeUuid'));
		$session->expects($this->once())->method('isSingleValuedPropertyDirty')->will($this->returnValue(FALSE));

		$this->assertFalse($session->isDirty($parent, 'array'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function isDirtyReturnsTrueForArraysWithNewMembers() {
		$object = new \stdClass();
		$object->FLOW3_Persistence_Identifier = 'dirtyUuid';
		$array = array();
		$array[] = $object;
		$className = 'Class' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' { public $array; }');
		$parent = new $className();
		$parent->array = $array;

		$cleanData = array(
			'identifier' => 'fakeUuid',
			'properties' => array(
				'array' => array(
					'type' => 'array',
					'multivalue' => TRUE,
					'value' => array(
						array(
							'type' => 'Some\Object',
							'index' => 'new',
							'value' => array('identifier' => 'cleanUuid')
						)
					)
				)
			)
		);
		$session = $this->getMock('TYPO3\FLOW3\Persistence\Generic\Session', array('getIdentifierByObject'));
		$session->registerReconstitutedEntity($parent, $cleanData);
		$session->expects($this->once())->method('getIdentifierByObject')->will($this->returnValue('fakeUuid'));

		$this->assertTrue($session->isDirty($parent, 'array'));
	}

	/**
	 * Returns tuples of the form <type, current, clean, expected> for
	 * isSingleValuedPropertyDirty()
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function propertyData() {
		$dateTime = new \DateTime();
		$entity = new \stdClass();
		$valueObject = new \stdClass();

		return array(
			array('string', 'foo', 'foo', FALSE),
			array('string', 'foo', 'bar', TRUE),
			array('boolean', TRUE, TRUE, FALSE),
			array('boolean', TRUE, FALSE, TRUE),
			array('float', 1.2, 1.2, FALSE),
			array('float', 1.2, 1.3, TRUE),
			array('integer', 10, 10, FALSE),
			array('integer', 10, 12, TRUE),
			array('Some\Entity', $entity, array('identifier' => NULL), FALSE),
			array('Some\Entity', $entity, array('identifier' => 'dirtyUuid'), TRUE),
			array('Some\ValueObject', $valueObject, array('identifier' => NULL), FALSE),
			array('Some\ValueObject', $valueObject, array('identifier' => 'dirtyHash'), TRUE),
			array('DateTime', $dateTime, $dateTime->getTimestamp(), FALSE),
			array('DateTime', $dateTime, $dateTime->getTimestamp()+1, TRUE),
		);
	}

	/**
	 * @test
	 * @dataProvider propertyData
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function isSingleValuedPropertyDirtyWorksAsExpected($type, $current, $clean, $expected) {
		$session = $this->getMock($this->buildAccessibleProxy('TYPO3\FLOW3\Persistence\Generic\Session'), array('getIdentifierByObject'));
		$this->assertEquals($session->_call('isSingleValuedPropertyDirty', $type, $clean, $current), $expected);
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getCleanStateOfPropertyReturnsNullIfPropertyWasNotInObjectData() {
		$entity = new \stdClass();

		$reconstitutedEntitiesData = array(
			'abc' => array(
				'properties' => array(
					'foo' => array('type' => 'string')
				)
			)
		);

		$session = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\Session', array('isReconstitutedEntity', 'getIdentifierByObject'));
		$session->_set('reconstitutedEntitiesData', $reconstitutedEntitiesData);

		$session->expects($this->any())->method('isReconstitutedEntity')->with($entity)->will($this->returnValue(TRUE));
		$session->expects($this->any())->method('getIdentifierByObject')->with($entity)->will($this->returnValue('abc'));

		$state = $session->getCleanStateOfProperty($entity, 'bar');
		$this->assertNull($state);
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getCleanStateOfPropertyReturnsNullIfObjectWasNotReconstituted() {
		$entity = new \stdClass();

		$session = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\Session', array('isReconstitutedEntity'));

		$session->expects($this->any())->method('isReconstitutedEntity')->with($entity)->will($this->returnValue(FALSE));

		$state = $session->getCleanStateOfProperty($entity, 'bar');
		$this->assertNull($state);
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getCleanStateOfPropertyReturnsPropertyData() {
		$entity = new \stdClass();

		$reconstitutedEntitiesData = array(
			'abc' => array(
				'properties' => array(
					'foo' => array('type' => 'string')
				)
			)
		);

		$session = $this->getAccessibleMock('TYPO3\FLOW3\Persistence\Generic\Session', array('isReconstitutedEntity', 'getIdentifierByObject'));
		$session->_set('reconstitutedEntitiesData', $reconstitutedEntitiesData);

		$session->expects($this->any())->method('isReconstitutedEntity')->with($entity)->will($this->returnValue(TRUE));
		$session->expects($this->any())->method('getIdentifierByObject')->with($entity)->will($this->returnValue('abc'));

		$state = $session->getCleanStateOfProperty($entity, 'foo');
		$this->assertEquals(array('type' => 'string'), $state);
	}

	/**
	 * Does it return the UUID for an object know to the identity map?
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getIdentifierByObjectReturnsUUIDForKnownObject() {
		$knownObject = $this->getMock('TYPO3\FLOW3\AOP\ProxyInterface');
		$fakeUUID = '123-456';

		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->registerObject($knownObject, $fakeUUID);

		$this->assertEquals($fakeUUID, $session->getIdentifierByObject($knownObject));
	}

	/**
	 * Does it return the UUID for an AOP proxy not being in the identity map
	 * but having FLOW3_Persistence_Identifier?
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getIdentifierByObjectReturnsUuidForObjectBeingAOPProxy() {
		$knownObject = $this->getMock('TYPO3\FLOW3\AOP\ProxyInterface');
		$knownObject->FLOW3_Persistence_Identifier = 'fakeUuid';

		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->injectReflectionService($this->getMock('TYPO3\FLOW3\Reflection\ReflectionService'));

		$this->assertEquals('fakeUuid', $session->getIdentifierByObject($knownObject));
	}

	/**
	 * Does it return the value object hash for an AOP proxy not being in the
	 * identity map but having FLOW3_Persistence_Identifier?
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getIdentifierByObjectReturnsHashForObjectBeingAOPProxy() {
		$knownObject = $this->getMock('TYPO3\FLOW3\AOP\ProxyInterface');
		$knownObject->FLOW3_Persistence_Identifier = 'fakeHash';

		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->injectReflectionService($this->getMock('TYPO3\FLOW3\Reflection\ReflectionService'));

		$this->assertEquals('fakeHash', $session->getIdentifierByObject($knownObject));
	}

	/**
	 * Does it return NULL for an AOP proxy not being in the identity map and
	 * not having FLOW3_Persistence_Identifier?
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getIdentifierByObjectReturnsNullForUnknownObjectBeingAOPProxy() {
		$unknownObject = $this->getMock('TYPO3\FLOW3\AOP\ProxyInterface');

		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->injectReflectionService($this->getMock('TYPO3\FLOW3\Reflection\ReflectionService'));

		$this->assertNull($session->getIdentifierByObject($unknownObject));
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getIdentifierByObjectReturnsValueOfPropertyTaggedWithId() {
		$object = $this->getMock('TYPO3\FLOW3\AOP\ProxyInterface');
		$object->FLOW3_Persistence_Identifier = 'randomlyGeneratedUuid';
		$object->customId = 'customId';

		$mockReflectionService = $this->getMock('TYPO3\FLOW3\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getPropertyNamesByTag')->will($this->returnValue(array('customId')));

		$session = new \TYPO3\FLOW3\Persistence\Generic\Session();
		$session->injectReflectionService($mockReflectionService);

		$this->assertEquals('customId', $session->getIdentifierByObject($object));
	}

}
?>