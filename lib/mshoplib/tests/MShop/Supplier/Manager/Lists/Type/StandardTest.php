<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2018
 */

namespace Aimeos\MShop\Supplier\Manager\Lists\Type;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $editor = '';


	protected function setUp()
	{
		$this->editor = \TestHelperMShop::getContext()->getEditor();
		$manager = \Aimeos\MShop\Supplier\Manager\Factory::create( \TestHelperMShop::getContext() );

		$listManager = $manager->getSubManager( 'lists' );
		$this->object = $listManager->getSubManager( 'type' );
	}


	protected function tearDown()
	{
		unset( $this->object );
	}


	public function testCleanup()
	{
		$this->assertInstanceOf( \Aimeos\MShop\Common\Manager\Iface::class, $this->object->cleanup( [-1] ) );
	}


	public function testGetResourceType()
	{
		$result = $this->object->getResourceType();

		$this->assertContains( 'supplier/lists/type', $result );
	}


	public function testCreateItem()
	{
		$item = $this->object->createItem();
		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Type\Iface::class, $item );
	}

	public function testGetItem()
	{
		$search = $this->object->createSearch()->setSlice( 0, 1 );
		$search->setConditions( $search->compare( '==', 'supplier.lists.type.editor', $this->editor) );
		$results = $this->object->searchItems($search);

		if( ( $expected = reset($results) ) === false ) {
			throw new \RuntimeException( 'No attribute list type item found' );
		}

		$this->assertEquals( $expected, $this->object->getItem( $expected->getId() ) );
	}


	public function testSaveInvalid()
	{
		$this->setExpectedException( \Aimeos\MW\Common\Exception::class );
		$this->object->saveItem( new \Aimeos\MShop\Locale\Item\Standard() );
	}


	public function testSaveUpdateDeleteItem()
	{
		$search = $this->object->createSearch();
		$search->setConditions( $search->compare( '==', 'supplier.lists.type.editor', $this->editor) );
		$results = $this->object->searchItems($search);

		if( ( $item = reset($results) ) === false ) {
			throw new \RuntimeException( 'No type item found' );
		}

		$item->setId(null);
		$item->setCode( 'unitTestSave' );
		$resultSaved = $this->object->saveItem( $item );
		$itemSaved = $this->object->getItem( $item->getId() );

		$itemExp = clone $itemSaved;
		$itemExp->setCode( 'unitTestSave2' );
		$resultUpd = $this->object->saveItem( $itemExp );
		$itemUpd = $this->object->getItem( $itemExp->getId() );

		$this->object->deleteItem( $itemSaved->getId() );


		$this->assertTrue( $item->getId() !== null );
		$this->assertEquals( $item->getId(), $itemSaved->getId() );
		$this->assertEquals( $item->getSiteId(), $itemSaved->getSiteId() );
		$this->assertEquals( $item->getCode(), $itemSaved->getCode() );
		$this->assertEquals( $item->getDomain(), $itemSaved->getDomain() );
		$this->assertEquals( $item->getLabel(), $itemSaved->getLabel() );
		$this->assertEquals( $item->getStatus(), $itemSaved->getStatus() );

		$this->assertEquals( $this->editor, $itemSaved->getEditor() );
		$this->assertRegExp( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $itemSaved->getTimeCreated() );
		$this->assertRegExp( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $itemSaved->getTimeModified() );

		$this->assertEquals( $itemExp->getId(), $itemUpd->getId() );
		$this->assertEquals( $itemExp->getSiteId(), $itemUpd->getSiteId() );
		$this->assertEquals( $itemExp->getCode(), $itemUpd->getCode() );
		$this->assertEquals( $itemExp->getDomain(), $itemUpd->getDomain() );
		$this->assertEquals( $itemExp->getLabel(), $itemUpd->getLabel() );
		$this->assertEquals( $itemExp->getStatus(), $itemUpd->getStatus() );

		$this->assertEquals( $this->editor, $itemUpd->getEditor() );
		$this->assertEquals( $itemExp->getTimeCreated(), $itemUpd->getTimeCreated() );
		$this->assertRegExp( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $itemUpd->getTimeModified() );

		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Iface::class, $resultSaved );
		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Iface::class, $resultUpd );

		$this->setExpectedException( \Aimeos\MShop\Exception::class );
		$this->object->getItem( $itemSaved->getId() );
	}

	public function testSearchItems()
	{
		$total = 0;
		$search = $this->object->createSearch();

		$expr = [];
		$expr[] = $search->compare( '!=', 'supplier.lists.type.id', null );
		$expr[] = $search->compare( '!=', 'supplier.lists.type.siteid', null );
		$expr[] = $search->compare( '==', 'supplier.lists.type.domain', 'text' );
		$expr[] = $search->compare( '==', 'supplier.lists.type.code', 'default' );
		$expr[] = $search->compare( '==', 'supplier.lists.type.label', 'Standard' );
		$expr[] = $search->compare( '>=', 'supplier.lists.type.position', 0 );
		$expr[] = $search->compare( '==', 'supplier.lists.type.status', 1 );
		$expr[] = $search->compare( '>=', 'supplier.lists.type.mtime', '1970-01-01 00:00:00' );
		$expr[] = $search->compare( '>=', 'supplier.lists.type.ctime', '1970-01-01 00:00:00' );
		$expr[] = $search->compare( '==', 'supplier.lists.type.editor', $this->editor );

		$search->setConditions( $search->combine('&&', $expr) );
		$search->setSortations( [$search->sort( '-', 'supplier.lists.type.position' )] );
		$search->setSlice(0, 1);
		$results = $this->object->searchItems( $search, [], $total );
		$this->assertEquals( 1, count( $results ) );
		$this->assertEquals( 1, $total );

		foreach( $results as $itemId => $item ) {
			$this->assertEquals( $itemId, $item->getId() );
		}
	}


	public function testGetSubManager()
	{
		$this->setExpectedException( \Aimeos\MShop\Exception::class );
		$this->object->getSubManager('unknown');
	}
}
